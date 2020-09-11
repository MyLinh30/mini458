<?php

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Connection\Ajax;

use Magento\Framework\App\Action\Context;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Limited;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Priority;

class Sync extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
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
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param CreateQueue $createQueue
     * @param QueueFactory $queueFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Mapping $map
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        CreateQueue $createQueue,
        QueueFactory $queueFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Mapping $map
    )
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_map = $map;
        $this->_queueHelper = $createQueue;
        $this->_queueFactory = $queueFactory;
    }

    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $number = $this->getRequest()->getParam('number');
            $max = $this->getRequest()->getParam('max') - 1;
            $type = $this->getRequest()->getParam('type');
            if ($type === "customer") {
                $result = $this->syncCustomer($number);
                $limited = Limited::LIMITED_CUSTOMER;
            } elseif ($type === "product") {
                $result = $this->syncProduct($number);
                $limited = Limited::LIMITED_PRODUCT;
            } elseif ($type === "order") {
                $result = $this->syncOrder($number);
                $limited = Limited::LIMITED_ORDER;
            } elseif ($type === "invoice") {
                $result = $this->syncInvoice($number);
                $limited = Limited::LIMITED_INVOICE;
            } elseif ($type === "creditmemo") {
                $result = $this->syncCreditMemo($number);
                $limited = Limited::LIMITED_CREDITMEMO;
            }
            if (!is_numeric($result)) {
                $this->messageManager->addErrorMessage($result);
                return $this->resultJsonFactory->create()->setData([
                    'finish' => 1
                ]);
            } else if ($number == $max) {
                $count = $max * $limited + $result;
                $this->messageManager->addSuccessMessage("Totals $count Queue have been created/updated");
            }
            return $this->resultJsonFactory->create()->setData([
                'finish' => $number == $max ? 1 : 0
            ]);
        }
        return null;
    }

    public function getCollection(array $type, $limited, $number)
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
            ->addFieldToFilter('entity_id', ['in' => $idToQueue])
            ->setPageSize($limited)
            ->setCurPage($number + 1)
            ->setOrder('entity_id', 'ASC');
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

    public function checkQueue($type, $entityId)
    {
        $companyId = $this->_queueHelper->getCompanyId();
        $check = $this->_queueFactory->create()->getCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('entity_id', $entityId)
            ->addFieldToFilter('company_id', $companyId);
        return $check;
    }

    /**
     * @param $number
     * @return int|string
     */
    protected function syncCustomer($number)
    {
        try {
            $customerCollection = $this->getCollection([Type::QUEUE_CUSTOMER], Limited::LIMITED_CUSTOMER, $number);
            $totals = 0;

            foreach ($customerCollection as $customer) {
                $id = $customer->getId();
                $check = $this->checkQueue('Customer', $id);
                if ($check->count() == 0) {
                    $this->_queueHelper->createCustomerQueue($customer, "Add", Operation::OPERATION_ADD);
                    $totals++;
                }
            }
            return $totals;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
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
     * @param $number
     * @return int|string
     */
    protected function syncProduct($number)
    {
        try {
            $productCollection = $this->getCollection([Type::QUEUE_PRODUCT], Limited::LIMITED_PRODUCT, $number);
            $totals = 0;

            foreach ($productCollection as $product) {
                /** @var \Magento\Catalog\Model\Product $productModel */
                $productModel = $this->_objectManager->create('\Magento\Catalog\Model\Product');
                $productId = $product->getId();
                $productModel = $productModel->load($productId);
                $qty = $productModel->getExtensionAttributes()->getStockItem()->getQty();
                $type = $productModel->getTypeId();
                $modelCheck = $this->checkQueue('Product', $productId);
                if ($modelCheck->count() == 0) {
                    if ($qty > 0
                        || $type == 'virtual'
                        || $type == 'simple'
                        || $type == 'giftcard'
                        || $type == 'downloadable'
                    ) {
                        $this->_queueHelper->createItemInventoryAddQueue($productId);
                    } else {
                        $this->_queueHelper->createItemNonInventoryAddQueue($productId);
                    }
                	$totals++;
                }
            }
            return $totals;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
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
     * @param $number
     * @return int|string
     */
    protected function syncOrder($number)
    {
        try {
            $companyId = $this->_queueHelper->getCompanyId();
            $orderCollection = $this->getCollection([Type::QUEUE_SALESORDER], Limited::LIMITED_ORDER, $number);

            $totals = 0;
            foreach ($orderCollection as $order) {
                $id = $order->getId();
                $check = $this->checkQueue('SalesOrder', $id);
                if ($check->count() == 0) {
                    if (!$order->getCustomerId()) {
                        $qbId = $this->_map->getCollection()
                            ->addFieldToFilter('company_id', $companyId)
                            ->addFieldToFilter('type', Type::QUEUE_GUEST)
                            ->addFieldToFilter('entity_id', $id)
                            ->getFirstItem()->getData();

                        if (!$qbId) {
                            $this->_queueHelper->createGuestQueue($order, 'Add', Operation::OPERATION_ADD);
                        }
                    }
                    $this->_queueHelper->createTransactionQueue($id, 'SalesOrder', Priority::PRIORITY_SALESORDER);

            	    $totals++;
            	}
            }
            return $totals;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @return mixed
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
     * @param $number
     * @return int|string
     */
    protected function syncInvoice($number)
    {
        try {
            $invoiceCollection = $this->getCollection([Type::QUEUE_INVOICE, Type::QUEUE_RECEIVEPAYMENT], Limited::LIMITED_INVOICE, $number);
            $totals = 0;

            foreach ($invoiceCollection as $invoice) {
                $id = $invoice->getId();

                $check = $this->checkQueue('Invoice', $id);
                if ($check->count() == 0) {
                    $this->_queueHelper->createTransactionQueue($id, 'Invoice', Priority::PRIORITY_INVOICE);

                }
                $totals++;
                if ($invoice->getState() == 2) { // Paid Invoice
                    $check = $this->checkQueue('ReceivePayment', $id);
                    if ($check->count() == 0) {
                        $this->_queueHelper->createTransactionQueue($id, 'ReceivePayment', Priority::PRIORITY_RECEIVEPAYMENT);
                        $totals++;
                    }
                }
            }
            return $totals;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @return mixed
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
     * @param $number
     * @return int|string
     */
    protected function syncCreditMemo($number)
    {
        try {
            $memoCollection = $this->getCollection([Type::QUEUE_CREDITMEMO], Limited::LIMITED_CREDITMEMO, $number);
            $totals = 0;
            foreach ($memoCollection as $memo) {
                $id = $memo->getId();
                $check = $this->checkQueue('CreditMemo', $id);
                if ($check->count() == 0) {
                    $this->_queueHelper->createTransactionQueue($id, 'CreditMemo', Priority::PRIORITY_CREDITMEMO);
                    $totals++;
                }

            }
            return $totals;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @return mixed
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
