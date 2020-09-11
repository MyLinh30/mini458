<?php

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Connection\Ajax;

use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Limited;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Priority;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Check extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CreateQueue
     */
    protected $_queueHelper;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    private $customerCollection;

    private $productCollection;

    private $orderCollection;

    private $invoiceCollection;

    private $creditmemoColleciton;

    /**
     * Sync constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CreateQueue $createQueue
     * @param QueueFactory $queueFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Mapping $map
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CreateQueue $createQueue,
        QueueFactory $queueFactory,
        ScopeConfigInterface $scopeConfig,
        Mapping $map
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_map = $map;
        $this->_queueHelper = $createQueue;
        $this->_queueFactory = $queueFactory;
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $type = $this->getRequest()->getParam('type');
            $result = 0;
            if ($type === "customer") {
                $result = $this->getCountCustomer();
            } elseif ($type === "product") {
                $result = $this->getCountProduct();
            } elseif ($type === "order") {
                $result = $this->getCountOrder();
            } elseif ($type === "invoice") {
                $result = $this->getCountInvoice();
            } elseif ($type === "creditmemo") {
                $result = $this->getCountCreditMemo();
            }
           if($result == 0){
               $this->messageManager->addSuccessMessage('Totals 0 Queue have been created/updated');
           }
            return $this->resultJsonFactory->create()->setData([
                'count' => $result
            ]);
        }
        return null;
    }

    public function getCollection(array $type)
    {
        $companyId = $this->_queueHelper->getCompanyId();

        $mappingCollection = $this->_map->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter(
                'type',
                ["in" => $type]
            )
            ->getColumnValues('entity_id');

        $typeMapping = [
            Type::QUEUE_CUSTOMER => 'Customer',
            Type::QUEUE_GUEST => 'Customer',
            Type::QUEUE_INVOICE => 'Invoice',
            Type::QUEUE_SALESORDER => 'SalesOrder',
            Type::QUEUE_PRODUCT => 'Product',
            Type::QUEUE_CREDITMEMO => 'CreditMemo'
        ];
        $queueType = [];
        foreach ($type as $value) {
            if (isset($typeMapping[$value])) {
                $queueType[] = $typeMapping[$value];
            }
        }

        $queueCollection = $this->_queueFactory->create()->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', ['in' => $queueType])->getColumnValues('entity_id');

        $allIds = $this->getTypeCollection($type[0])->getAllIds();
        $idToQueue = array_diff(array_diff($allIds, $mappingCollection), $queueCollection);
        $collection = $this->getTypeCollection($type[0])
            ->addFieldToFilter('entity_id', ['in' => $idToQueue]);
        return $collection;
    }

    public function getTypeCollection($type)
    {
        if ($type == Type::QUEUE_CUSTOMER) {
            return $this->getCustomerCollection();
        } elseif ($type == Type::QUEUE_PRODUCT) {
            return $this->getProductCollection();
        } elseif ($type == Type::QUEUE_SALESORDER) {
            return $this->getOrderCollection();
        } elseif ($type == Type::QUEUE_INVOICE) {
            return $this->getInvoiceCollection();
        } elseif ($type == Type::QUEUE_CREDITMEMO) {
            return $this->getCreditMemoCollection();
        }
    }

    /**
     * @return float
     */
    public function getCountCustomer()
    {
        $customerCollection = $this->getCollection([Type::QUEUE_CUSTOMER]);
        return (ceil(($customerCollection->count()) / Limited::LIMITED_CUSTOMER));
    }

    /**
     * @return mixed
     */
    public function getCustomerCollection()
    {
        if ($this->customerCollection == null) {
            $date = $this->_scopeConfig->getValue('qbdesktop/qbd_setting/date_customer');
            $customerCollection = $this->_objectManager->create('\Magento\Customer\Model\ResourceModel\Customer\Collection');
            $this->customerCollection = empty($date) ? $customerCollection : $customerCollection->addFieldToFilter('updated_at', ['gteq' => $date]);
        }
        return $this->customerCollection;
    }

    /**
     * @return float
     */
    public function getCountProduct()
    {

        $productCollection = $this->getCollection([Type::QUEUE_PRODUCT]);
        return (ceil(($productCollection->count()) / Limited::LIMITED_PRODUCT));
    }

    /**
     * @return mixed
     */
    public function getProductCollection()
    {
        if ($this->productCollection == null) {
            $date = $this->_scopeConfig->getValue('qbdesktop/qbd_setting/date_product');
            $productCollection = $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');
            $this->productCollection = empty($date) ? $productCollection : $productCollection->addFieldToFilter('updated_at', ['gteq' => $date]);
        }
        return $this->productCollection;
    }

    /**
     * @return float
     */
    public function getCountOrder()
    {
        $orderCollection = $this->getCollection([Type::QUEUE_SALESORDER]);
        return (ceil(($orderCollection->count()) / Limited::LIMITED_ORDER));

    }

    /**
     * @return Collection|mixed
     */
    public function getOrderCollection()
    {
        if ($this->orderCollection == null) {
            $date = $this->_scopeConfig->getValue('qbdesktop/qbd_setting/date_sales_order');
            $orderCollection = $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Collection');
            $this->orderCollection = empty($date) ? $orderCollection : $orderCollection->addFieldToFilter('updated_at', ['gteq' => $date]);
        }
        return $this->orderCollection;
    }

    /**
     * @return float
     */
    public function getCountInvoice()
    {
        $invoiceCollection = $this->getCollection([Type::QUEUE_INVOICE, Type::QUEUE_RECEIVEPAYMENT]);
        return (ceil(($invoiceCollection->count()) / Limited::LIMITED_INVOICE));
    }

    /**
     * @return Collection|mixed
     */
    public function getInvoiceCollection()
    {
        if ($this->invoiceCollection == null) {
            $date = $this->_scopeConfig->getValue('qbdesktop/qbd_setting/date_invoice');
            $invoiceCollection = $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection');
            $this->invoiceCollection = empty($date) ? $invoiceCollection : $invoiceCollection->addFieldToFilter('updated_at', ['gteq' => $date]);
        }
        return $this->invoiceCollection;
    }

    /**
     * @return float
     */
    public function getCountCreditMemo()
    {
        $memoCollection = $this->getCollection([Type::QUEUE_CREDITMEMO]);
        return (ceil(($memoCollection->count()) / Limited::LIMITED_CREDITMEMO));

    }

    /**
     * @return Collection|mixed
     */
    public function getCreditMemoCollection()
    {
        if ($this->creditmemoColleciton == null) {
            $date = $this->_scopeConfig->getValue('qbdesktop/qbd_setting/date_credit_memo');
            $creditmemoCollection = $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection');
            $this->creditmemoColleciton = empty($date) ? $creditmemoCollection : $creditmemoCollection->addFieldToFilter('updated_at', ['gteq' => $date]);
        }
        return $this->creditmemoColleciton;
    }

}
