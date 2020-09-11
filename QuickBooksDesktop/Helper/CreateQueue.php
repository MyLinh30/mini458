<?php
/**
 * Created by PhpStorm.
 * User: Nguyen Thanh Nam
 * Date: 7/24/2018
 * Time: 2:46 PM
 */

namespace Magenest\QuickBooksDesktop\Helper;

use Magenest\QuickBooksDesktop\Model\CompanyFactory;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Priority;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magento\Sales\Model\Order;

class CreateQueue
{

    const CUSTOMER_DATE = 'qbdesktop/qbd_setting/date_customer';
    const PRODUCT_DATE = 'qbdesktop/qbd_setting/date_product';
    const ORDER_DATE = 'qbdesktop/qbd_setting/date_sales_order';
    const INVOCIE_DATE = 'qbdesktop/qbd_setting/date_invoice';
    const CREDITMEMO_DATE = 'qbdesktop/qbd_setting/date_credit_memo';
    const TAX_DEFAULT = 'qbdesktop/tax_setting/tax_default';

    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var CompanyFactory
     */
    protected $_companyFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Mapping
     */
    public $_map;

    protected $_configHelper;

    /**
     * CreateQueue constructor.
     * @param CompanyFactory $companyFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Configuration $configuration,
        CompanyFactory $companyFactory,
        ScopeConfigInterface $scopeConfig,
        QueueFactory $queueFactory,
        Mapping $mapping
    ) {
        $this->_companyFactory = $companyFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_queueFactory = $queueFactory;
        $this->_map = $mapping;
        $this->_configHelper = $configuration;
    }

    public function getCompanyId()
    {
        $company = $this->_companyFactory->create();
        $company->load(1, 'status');
        return $company->getCompanyId();
    }

    public function disconnectCompany()
    {
        $this->_companyFactory->create()->getCollection()->setDataToAll('status', 0)->save();
    }

    public function writeDebug($ex)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/qbdesktop.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("QBD error: " . $ex);
    }

    public function getQuickBooksVersion()
    {
        $version = $this->scopeConfig->getValue(
            'qbdesktop/qbd_setting/quickbook_version',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $version;
    }

    public function createItemInventoryAddQueue($productId)
    {
        $this->createQueue(
            $productId,
            'ItemInventoryAdd',
            'Product',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_PRODUCT
        );
    }

    public function createCustomerQueue(Customer $customer, $action, $operation)
    {
        if (!$this->_configHelper->isSyncIntoACustomer($customer->getStore()->getData('code'))) {
            $this->createQueue(
                $customer->getId(),
                'Customer' . $action,
                'Customer',
                $operation,
                Priority::PRIORITY_CUSTOMER
            );
        }
    }

    public function createGuestQueue(Order $order, $action, $operation)
    {
        if (!$this->_configHelper->isSyncIntoACustomer($order->getStore()->getCode())) {
            $this->createQueue(
                $order->getIncrementId(),
                'Customer' . $action,
                'Guest',
                $operation,
                Priority::PRIORITY_GUEST
            );
        }
    }

    public function createItemInventoryModQueue($productId)
    {
        $this->createQueue(
            $productId,
            'ItemInventoryMod',
            'Product',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_PRODUCT
        );
    }

    public function createItemNonInventoryAddQueue($productId)
    {
        $this->createQueue(
            $productId,
            'ItemNonInventoryAdd',
            'Product',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_PRODUCT
        );
    }

    public function createItemNonInventoryModQueue($productId)
    {
        $this->createQueue(
            $productId,
            'ItemNonInventoryMod',
            'Product',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_PRODUCT
        );
    }

    public function createSalesOrderQueue($orderId)
    {
        $this->createQueue(
            $orderId,
            'SalesOrderAdd',
            'SalesOrder',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_SALESORDER
        );
    }

    public function createCreditMemoQueue($creditMemoId)
    {
        $this->createQueue(
            $creditMemoId,
            'CreditMemoAdd',
            'CreditMemo',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_CREDITMEMO
        );
    }

    public function createOpenInvoiceQueue($invoiceId)
    {
        $this->createQueue(
            $invoiceId,
            'InvoiceAdd',
            'Invoice',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_INVOICE
        );
    }

    public function createPaidInvoiceQueue($invoiceId)
    {
        $this->createQueue(
            $invoiceId,
            'ReceivePaymentAdd',
            'ReceivePayment',
            Operation::OPERATION_ADD,
            Priority::PRIORITY_RECEIVEPAYMENT
        );
    }

    public function createQueue($entityId, $actionName, $type, $operation, $priority)
    {
        try {
            if ($this->getCompanyId()) {
                $info = [
                    'action_name' => $actionName,
                    'enqueue_datetime' => time(),
                    'type' => $type,
                    'status' => Status::STATUS_QUEUE,
                    'entity_id' => $entityId,
                    'operation' => $operation,
                    'company_id' => $this->getCompanyId(),
                    'priority' => $priority
                ];
                $model = $this->_queueFactory->create();
                $modelCheck = $model->getCollection()
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('entity_id', $entityId)
                    ->addFieldToFilter('company_id', $this->getCompanyId())
                    ->addFieldToFilter('status', Status::STATUS_QUEUE)->getLastItem();
                $modelCheck->addData($info);
                $modelCheck->save();
            }
        } catch (\Exception $exception) {
            $this->writeDebug($exception->getMessage());
        }
    }

    public function createTransactionQueue($entityId, $type, $priority)
    {
        try {
            if ($this->getCompanyId()) {
                $info = [
                    'action_name' => $type . 'Add',
                    'enqueue_datetime' => time(),
                    'dequeue_datetime' => '',
                    'type' => $type,
                    'status' => Status::STATUS_QUEUE,
                    'company_id' => $this->getCompanyId(),
                    'entity_id' => $entityId,
                    'operation' => Operation::OPERATION_ADD,
                    'priority' => $priority
                ];
                $model = $this->_queueFactory->create();
                $modelCheck = $model->getCollection()
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('entity_id', $entityId)
                    ->addFieldToFilter('company_id', $this->getCompanyId())
                    ->addFieldToFilter('status', Status::STATUS_QUEUE)->getLastItem();
                $modelCheck->addData($info);
                $modelCheck->save();
            }
        } catch (\Exception $exception) {
            $this->writeDebug($exception->getMessage());
        }
    }

    public function checkMapping(array $type)
    {
        $companyId = $this->getCompanyId();
        return $this->_map->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', ["in" => $type])
            ->getColumnValues('entity_id');
    }

    public function checkQueue($id, $type)
    {
        $companyId = $this->getCompanyId();
        return $this->_queueFactory->create()->getCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('entity_id', $id)
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('status', Status::STATUS_QUEUE);
    }

    public function getCustomerDate()
    {
        return $this->scopeConfig->getValue(self::CUSTOMER_DATE);
    }

    public function getProductDate()
    {
        return $this->scopeConfig->getValue(self::PRODUCT_DATE);
    }

    public function getSalesOrderDate()
    {
        return $this->scopeConfig->getValue(self::ORDER_DATE);
    }

    public function getInvoiceDate()
    {
        return $this->scopeConfig->getValue(self::INVOCIE_DATE);
    }

    public function getCreditmemoDate()
    {
        return $this->scopeConfig->getValue(self::CREDITMEMO_DATE);
    }
}
