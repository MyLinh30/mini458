<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\WebConnector;

use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use Magenest\QuickBooksDesktop\Helper\GenerateTicket;
use Magenest\QuickBooksDesktop\Helper\Result;
use Magenest\QuickBooksDesktop\Model\CustomQueue;
use Magenest\QuickBooksDesktop\Model\MappingFactory;
use Magenest\QuickBooksDesktop\Model\Ticket;
use Magenest\QuickBooksDesktop\WebConnector\Receive\Response as ReceiveResponse;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Handlers
 *
 * @package Magenest\QuickBooksDesktop\WebConnector
 */
abstract class Handlers
{
    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * @var Driver
     */
    protected $_driver;

    /**
     * @var GenerateTicket
     */
    protected $_generateTicket;

    /**
     * @var ReceiveResponse
     */
    protected $receiveResponse;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var MappingFactory
     */
    protected $_mapping;

    /**
     * Handlers constructor.
     *
     * @param GenerateTicket $generateTicket
     * @param Ticket $ticket
     * @param ReceiveResponse $receiveResponse
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        GenerateTicket $generateTicket,
        Ticket $ticket,
        ReceiveResponse $receiveResponse,
        ObjectManagerInterface $objectManager,
        MappingFactory $mappingFactory,
        QueueHelper $queueHelper
    ) {
        $this->_generateTicket = $generateTicket;
        $this->receiveResponse = $receiveResponse;
        $this->_ticket = $ticket;
        $this->_objectManager = $objectManager;
        $this->_mapping = $mappingFactory;
        $this->_queueHelper = $queueHelper;
    }

    /**
     * Check username and password send from QB Web Connector
     *
     * @param \stdClass $obj
     * @return Result\Authenticate
     */
    public function authenticate($obj)
    {
        /** Default Value */
        $status = 'nvu';
        $ticketCode = '88888-88888-88888-88888';

        if ($this->_driver->authenticate($obj)) {
            $status = 'none';
            $processed = $this->getProcessQueue();
            if ($processed) {
                $status = '';
            } else {
                $processed = 0;
            }
            $ticketCode = $this->_generateTicket->generateTicket($obj->strUserName, $processed);
        }

        return new Result\Authenticate($ticketCode, $status);
    }

    /**
     * Get Process Queue
     *
     * @return bool|int
     */
    protected function getProcessQueue()
    {
        $processed = $this->_driver->getTotalsQueue();

        return $processed;
    }

    /**
     * Magento Version
     *
     * @return Result\ServerVersion
     */
    public function serverVersion()
    {
        /** @var \Magenest\QuickBooksDesktop\Helper\Result\ServerVersion $serverVersion */
        $serverVersion = $this->_objectManager->create('Magenest\QuickBooksDesktop\Helper\Result\ServerVersion');

        return $serverVersion;
    }

    /**
     * Get and check QB Web Connector version
     *
     * @return Result\ClientVersion
     */
    public function clientVersion()
    {
        return new Result\ClientVersion();
    }

    /**
     * Close Connection
     *
     * @return Result\CloseConnection
     */
    public function closeConnection()
    {
        return new Result\CloseConnection();
    }

    /**
     * Send Request to QB Web Connector
     *
     * @param \stdClass $dataFromQWC
     * @return Result\SendRequestXML
     */
    public function sendRequestXML($dataFromQWC)
    {
        $this->updateProcessed($dataFromQWC);
        $xml = $this->_driver->prepareSendRequestXML($dataFromQWC);

        return new Result\SendRequestXML($xml);
    }

    /**
     * Update number ticket which have been processed
     *
     * @param $dataFromQWC
     * @throws \Exception
     */
    protected function updateProcessed($dataFromQWC)
    {
        $ticket = $this->_ticket->loadByCode($dataFromQWC->ticket);
        $current = ((int)$ticket->getCurrent()) + 1;
        $ticket->setCurrent($current);
        $ticket->save();
    }

    /**
     * @return ReceiveResponse
     */
    protected function getReceiveResponse()
    {
        return $this->receiveResponse;
    }

    /**
     * @param $dataFromQWC
     */
    protected function processResponse($dataFromQWC)
    {
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magenest\QuickBooksDesktop\Logger\Logger')->info("Start Process Response1");
        $response = $this->getReceiveResponse();
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magenest\QuickBooksDesktop\Logger\Logger')->info("Start Process Response2");
        $response->setResponse($dataFromQWC);
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magenest\QuickBooksDesktop\Logger\Logger')->info("Start Process Response3");
        $response->getAttribute();
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magenest\QuickBooksDesktop\Logger\Logger')->info("Start Process Response4");
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    protected function logger()
    {
        return $this->_objectManager->create('Psr\Log\LoggerInterface');
    }

    /**
     * Receive Response XML
     *
     * @param \stdClass $dataFromQWC
     * @return Result\ReceiveResponseXML
     */
    public function receiveResponseXML($dataFromQWC)
    {
        $this->processResponse($dataFromQWC);
        $model = $this->_ticket->loadByCode($dataFromQWC->ticket);
        $totals = (int)$model->getProcessed();
        $current = (int)$model->getCurrent();
        $percent = (int)($current * 100 / $totals);

        return new Result\ReceiveResponseXML($percent);
    }

    /**
     * Get Last Error
     *
     * @param $obj
     * @return Result\GetLastError
     */
    public function getLastError($obj)
    {
        $ticketId = $this->_ticket->loadByCode($obj->ticket)->getId();
        $iteratorId = $this->_customQueue->create()->getCollection()->addFieldToFilter('ticket_id', $ticketId)->getLastItem()->getData('iterator_id');
        $this->_driver->getCurrentQueue($obj->ticket)->saveCustomQueueStatus($ticketId, CustomQueue::CUSTOM_QUEUE_STATUS_SUCCESS, $iteratorId)->save();

        return new Result\GetLastError($obj->response);
    }
}
