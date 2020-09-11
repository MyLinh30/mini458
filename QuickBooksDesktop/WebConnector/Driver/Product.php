<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * peruvianlink.com extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package peruvianlink.com
 * @time: 19/08/2020 08:12
 */

namespace Magenest\QuickBooksDesktop\WebConnector\Driver;

use Magenest\QuickBooksDesktop\Helper\Configuration;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use Magenest\QuickBooksDesktop\Model\Config\Source\ProductQueryAction;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;
use Magenest\QuickBooksDesktop\Model\CustomQueue;
use Magenest\QuickBooksDesktop\Model\CustomQueueFactory;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Model\ResourceModel\Queue\CollectionFactory as QueueCollection;
use Magenest\QuickBooksDesktop\Model\Ticket;
use Magenest\QuickBooksDesktop\Model\User as UserModel;
use Magenest\QuickBooksDesktop\WebConnector\Driver;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class Product extends Driver
{
    const XML_PATH_PRODUCT_FILTER_TYPE = 'qbdesktop/product_list/mapping_update';
    const XML_PATH_PRODUCT_CRON_TIME = 'qbdesktop/product_list/cronjob_time';
    const XML_PATH_NUMBER_OF_PRODUCT = 'qbdesktop/product_list/product_number_qbd';
    const QUEUE_DEFAULT_MAX_RETURN = 200;

    /**
     * @var \Magenest\QuickBooksDesktop\Model\ResourceModel\CustomQueue\Collection
     */
    protected $productCollection;

    protected $_customQueue;

    protected $_ticket;

    public function __construct(
        Ticket $ticket,
        CustomQueueFactory $customQueueFactory,
        QueueCollection $collectionFactory, ObjectManagerInterface $objectManager, LoggerInterface $loggerInterface,
        UserModel $user, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, Mapping $map,
        QueueHelper $queueHelper
    ) {
        parent::__construct($collectionFactory, $objectManager, $loggerInterface, $user, $scopeConfig, $map, $queueHelper);
        $this->_customQueue = $customQueueFactory;
        $this->_ticket = $ticket;
    }

    /**
     * @return bool|int
     */
    public function getTotalsQueue()
    {
        $collection = $this->getCollection();

        $totals = $collection->getSize();
        if ($totals) {
            return $totals;
        }

        return 1;
    }

    /**
     * Get CustomQueue Collection
     *
     * @param null $ticketId
     * @return \Magenest\QuickBooksDesktop\Model\ResourceModel\CustomQueue\Collection
     */
    public function getCollection($ticketId = null)
    {
        if (!$this->productCollection) {
            $productCollection = $this->getCollectionByStatus(CustomQueue::CUSTOM_QUEUE_STATUS_QUEUE);
            if ($ticketId == null && $productCollection->getFirstItem()->getData('operation') != CustomQueue::OPERATION_START) { // if start -> reset collection
                $this->_customQueue->create()->updateAllStatus(TypeQuery::QUERY_PRODUCT);
                $productCollection = $this->getCollectionByStatus(CustomQueue::CUSTOM_QUEUE_STATUS_QUEUE);
            }
            $this->productCollection = $productCollection;
        }

        return $this->productCollection;
    }

    protected function getCollectionByStatus($status = CustomQueue::CUSTOM_QUEUE_STATUS_QUEUE)
    {
        $companyId = $this->_queueHelper->getCompanyId();

        return $this->_customQueue->create()->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', TypeQuery::QUERY_PRODUCT)
            ->addFieldToFilter('status', $status);
    }

    /**
     * @return \Magenest\QuickBooksDesktop\Model\CustomQueue|\Magento\Framework\DataObject
     */
    public function getCurrentQueue($ticketId = null)
    {
        $collection = $this->getCollection($ticketId);

        return $collection->getFirstItem();
    }

    /**
     * @param $customQueue
     *
     * @return string
     */
    public function prepareSendRequestXML($dataFromQWC)
    {
        $customQueue = $this->getCurrentQueue($dataFromQWC->ticket);
        if ($customQueue->getOperation() != CustomQueue::OPERATION_START && $customQueue->getId() % 4 == 0) {
            return ''; //pause in each 4 request times
        }
        if ($this->_scopeConfig->getValue(Configuration::QUERY_PRODUCT_ACTION) == ProductQueryAction::MAPPING_PRODUCT) {
            $action = 'ItemQuery';
        } else {
            $action = 'ItemInventoryQuery';
        }
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <?qbxml version="13.0"?>
        <QBXML>
            <QBXMLMsgsRq onError="continueOnError">';

        if ($customQueue->getOperation() == CustomQueue::OPERATION_START) {
            $xml .= '<' . $action . 'Rq requestID="' . $customQueue->getId() . '" iterator="Start">';
        } else {
            $model = $this->_customQueue->create()->getCollection()
                ->addFieldToFilter('ticket_id', $this->_ticket->loadByCode($dataFromQWC->ticket)->getId())->getLastItem();
            $xml .= '<' . $action . 'Rq requestID="' . $customQueue->getId() . '" iterator="Continue" iteratorID="' . $model->getData('iterator_id') . '">';
        }

        $xml .= $this->simpleXml(self::QUEUE_DEFAULT_MAX_RETURN, 'MaxReturned');
        $xml .= $this->simpleXml('ActiveOnly', 'ActiveStatus');
//		$xml .= '<NameFilter><MatchCriterion>Contains</MatchCriterion><Name>05-00799</Name></NameFilter>';

        $xml .= '</' . $action . 'Rq>';
        $xml .= '</QBXMLMsgsRq></QBXML>';

        return $xml;
    }
}
