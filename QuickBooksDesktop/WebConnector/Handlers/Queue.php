<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\WebConnector\Handlers;

use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use Magenest\QuickBooksDesktop\Model\Ticket;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Model\MappingFactory;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Model\ItemSalesOrderFactory;
use Magento\Catalog\Model\Product as ProductCollection;
use Magento\Customer\Model\Customer as CustomerModel;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Helper\GenerateTicket;
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\WebConnector;
use Magenest\QuickBooksDesktop\WebConnector\Receive\Response as ReceiveResponse;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;

/**
 * Class Queue
 * @package Magenest\QuickBooksDesktop\WebConnector\Handlers
 */
class Queue extends WebConnector\Handlers
{

    /**
     * @var QueueFactory
     */
    protected $_queue;

    /**
     * @var MappingFactory
     */
    protected $_mapping;

    /**
     * @var ProductCollection
     */
    protected $_product;

    /**
     * @var CustomerModel
     */
    protected $customerModel;

    /**
     * @var Mapping
     */
    protected $_map;

    /**
     * @var ItemSalesOrderFactory
     */
    protected $itemOrder;

    /**
     * Queue constructor.
     * @param GenerateTicket $generateTicket
     * @param Ticket $ticket
     * @param ReceiveResponse $receiveResponse
     * @param ObjectManagerInterface $objectManager
     * @param WebConnector\Driver\Queue $driverQueue
     * @param ProductCollection $productFactory
     * @param CustomerModel $customerModel
     * @param Mapping $map
     * @param MappingFactory $mapping
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        GenerateTicket $generateTicket,
        Ticket $ticket,
        ReceiveResponse $receiveResponse,
        ObjectManagerInterface $objectManager,
        MappingFactory $mappingFactory,
        QueueHelper $queueHelper,
        WebConnector\Driver\Queue $driverQueue,
        ProductCollection $productFactory,
        CustomerModel $customerModel,
        Mapping $map,
        MappingFactory $mapping,
        QueueFactory $queueFactory,
        ItemSalesOrderFactory $itemSalesOrderFactory
    ) {
        parent::__construct(
            $generateTicket,
            $ticket,
            $receiveResponse,
            $objectManager,
            $mappingFactory,
            $queueHelper
        );
        $this->_driver = $driverQueue;
        $this->_queue = $queueFactory;
        $this->_product = $productFactory;
        $this->customerModel = $customerModel;
        $this->_map = $map;
        $this->_mapping = $mapping;
        $this->itemOrder = $itemSalesOrderFactory;
    }

    /**
     * @param $dataFromQWC
     */
    protected function processResponse($dataFromQWC)
    {
        $response = $this->getReceiveResponse();
        $response->setResponse($dataFromQWC);
        $response->getAttribute();

//        \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info("ComeONnnn   ".print_r($response->setResponse($dataFromQWC)->parserXml->loadXML($dataFromQWC->response),true)."\n");

        $statusCode = $response->getStatusCode();
        $data = [
            'ticket_id' => $response->getTicketId(),
            'dequeue_datetime' => time(),
        ];
        $queue = $this->_driver->getCurrentQueue();
        $type = $queue->getType();

        if ($statusCode == 0 && $statusCode || $response->getStatusMessage() == "Status OK") {
            $data['status'] = Status::STATUS_SUCCESS;
            $result = $response->convertXmlToArray();
            if (@$result['_value']['SalesOrderRet']) {
                $this->saveItemOrder($result['_value']['SalesOrderRet']);
            }
        } else {
            $messageError = $response->getStatusMessage();
            $isSalesOrder = strpos($messageError, 'Not Found Product From M2');
            if ($isSalesOrder) {
                $messageError = 'Not Found Product In Magento';
            }
            $isSalesOrder = strpos($messageError, 'Not Found Txn Id Invoice');
            if ($isSalesOrder) {
                $messageError = 'Not Found Txn Id Invoice';
            }
            $data['status'] = Status::STATUS_FAIL;
            $data['msg'] = $messageError;
        }
        $queue = $this->_driver->getCurrentQueue();
        $queue->addData($data);
        $queue->save();
        if ($data['status'] == Status::STATUS_SUCCESS) {
            $this->saveMapping($response, $type);
        }

        return;
    }

    /**
     * @param $response
     * @param $type
     */
    public function saveMapping($response, $type)
    {
        /** @var \Magenest\QuickBooksDesktop\WebConnector\Receive\Response $id */
        $id = $response->getRequestId();
        $name = $response->getName();
        $txnid = $response->getTxnId();
        $listId = $response->getListId();
        $attribute = [
            'list_id' => $listId ? $listId : $txnid,
            'edit_sequence' => $response->getEditSequence(),
            'entity_id' => $this->getId($id),
            'payment' => $name
        ];


        //save attributes of product
        switch ($type) {
            case "Product":
                $this->saveMappingQueue($attribute, Type::QUEUE_PRODUCT);
                break;
            case "Guest":
                $this->saveMappingQueue($attribute, Type::QUEUE_GUEST);
                break;
            case "Customer":
                $this->saveMappingQueue($attribute, Type::QUEUE_CUSTOMER);
                break;
            case "ItemSalesTax":
                $this->saveMappingQueue($attribute, Type::QUEUE_ITEMSALESTAX);
                break;
            case "SalesTaxCode":
                $this->saveMappingQueue($attribute, Type::QUEUE_SALESTAXCODE);
                break;
            case "Invoice":
                $this->saveMappingQueue($attribute, Type::QUEUE_INVOICE);
                break;
            case "PaymentMethod":
                $this->saveMappingQueue($attribute, Type::QUEUE_PAYMENTMETHOD);
                break;
            case "SalesOrder":
                $this->saveMappingQueue($attribute, Type::QUEUE_SALESORDER);
                break;
            case "CreditMemo":
                $this->saveMappingQueue($attribute, Type::QUEUE_CREDITMEMO);
                break;
            case "ShipMethod":
                $this->saveMappingQueue($attribute, Type::QUEUE_SHIPMETHOD);
                break;
            case "Vendor":
                $this->saveMappingQueue($attribute, Type::QUEUE_VENDOR);
                break;
            case "ReceivePayment":
                $this->saveMappingQueue($attribute, Type::QUEUE_RECEIVEPAYMENT);
                break;
            case "ItemOtherCharge":
                $this->saveMappingQueue($attribute, Type::QUEUE_SHIPMETHOD);
                break;
            case "ItemDiscount":
                $this->saveMappingQueue($attribute, Type::QUEUE_SHIPMETHOD);
                break;
            default:
                break;
        }
    }

    /**
     * @param $id
     * @return int
     */
    protected function getId($id)
    {
        $modelQueue = $this->_queue->create()->load($id);
        $setId = $modelQueue->getEntityId();

        return $setId;
    }

    public function saveMappingQueue($attribute, $type)
    {
        $companyId = $this->_queueHelper->getCompanyId();

        $attribute['type'] = $type;
        $attribute['company_id'] =$companyId ;

        $model = $this->_mapping->create()->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('list_id', $attribute['list_id'])
            ->getLastItem();

        $model->addData($attribute)->save();
    }

    public function saveItemOrder($data)
    {
        $listIdOrder = $data['TxnID'];
        $companyId = $this->_queueHelper->getCompanyId();
        $this->itemOrder->create()->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('list_id_order', $listIdOrder)
            ->walk('delete');

        $items = @$data['SalesOrderLineRet'];
        if (isset($items['TxnLineID'])) {
            $model = $this->itemOrder->create();
            $data =
                [
                    'list_id_order' => $listIdOrder,
                    'company_id' => $companyId,
                    'txn_line_id' => $items['TxnLineID'],
                    'list_id_item' => $items['ItemRef']['ListID'],
                    'sku' => $items['ItemRef']['FullName']
                ];
            $model->addData($data)->save();
        } else {
            foreach ($items as $item) {
                $model = $this->itemOrder->create();
                $data =
                    [
                        'list_id_order' => $listIdOrder,
                        'company_id' => $companyId,
                        'txn_line_id' => $item['TxnLineID'],
                        'list_id_item' => $item['ItemRef']['ListID'],
                        'sku' => $item['ItemRef']['FullName']
                    ];
                $model->addData($data)->save();
            }
        }
    }
}
