<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\WebConnector\Receive;

use Magento\Framework\Xml\Parser as ParserXml;
use Magenest\QuickBooksDesktop\Model\Ticket as TicketModel;
use Psr\Log\LoggerInterface;

/**
 * Class Response
 * @package Magenest\QuickBooksDesktop\WebConnector\Receive
 */
class Response
{
    /**
     * @var ParserXml
     */
    public $parserXml;

    /**
     * @var TicketModel
     */
    protected $ticketModel;

    /**
     * @var array
     */
    protected $convertToArray;

    /**
     * @var mixed
     */
    protected $statusCode;

    /**
     * @var int
     */
    protected $requestId;

    /**
     * @var mixed
     */
    protected $iteratorCount;

    /**
     * @var int
     */
    protected $iteratorId;

    /**
     * @var string
     */
    protected $statusMessage;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Response constructor.
     *
     * @param TicketModel $ticketModel
     * @param ParserXml $parserXml
     */
    public function __construct(
        TicketModel $ticketModel,
        ParserXml $parserXml,
        LoggerInterface $loggerInterface
    ) {
        $this->logger = $loggerInterface;
        $this->parserXml = $parserXml;
        $this->ticketModel = $ticketModel;
    }

    /**
     * Set Response receive from QuickBooks Desktop
     *
     * @param \stdClass $response
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setResponse($response)
    {
        $this->parserXml->loadXML($response->response);
        $this->ticketModel->loadByCode($response->ticket);

        return $this;
    }

    /**
     * @return int
     */
    public function getTicketId()
    {
        return $this->ticketModel->getId();
    }

    /**
     * @return int
     */
    public function calculatePercent()
    {
        //TODO
    }

    /**
     * @return array
     */
    public function convertXmlToArray()
    {
        if (!$this->convertToArray) {
            $responses = $this->parserXml->xmlToArray();
            $this->convertToArray = [];
            if (isset($responses['QBXML']['QBXMLMsgsRs'])) {
                $response = $responses['QBXML']['QBXMLMsgsRs'];
                if (is_array($response)) {
                    foreach ($response as $value) {
                        $this->convertToArray = $value;
                    }
                }
            }
        }

        return $this->convertToArray;
    }

    /**
     * @return bool
     */
    public function getAttribute()
    {
        $result = $this->convertXmlToArray();
        if (!empty($result)) {
            $attribute = $result['_attribute'];
            $this->requestId = $attribute['requestID'];
            $this->statusCode = $attribute['statusCode'];
            $this->statusMessage = $attribute['statusMessage'];
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @return int
     */
    public function getIteratorCount()
    {
        $result = $this->convertXmlToArray();
        if (!empty($result)) {
            return $result['_attribute']['iteratorRemainingCount'];
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return mixed
     */
    public function getIteratorId()
    {
        $result = $this->convertXmlToArray();
        if (!empty($result)) {
            return $result['_attribute']['iteratorID'];
        }

        return false;
    }

    /**
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * @return bool|array
     */
    public function getValue()
    {
        $result = $this->convertXmlToArray();
        if (!empty($result)) {
            return $result['_value'];
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getTxnId()
    {
        $value = $this->getValue();
        if ($value) {
            foreach ($value as $item) {
                if (isset($item['TxnID'])) {
                    return $item['TxnID'];
                }
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getListId()
    {
        $value = $this->getValue();
        if ($value) {
            foreach ($value as $item) {
                if (isset($item['ListID'])) {
                    return $item['ListID'];
                }
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getEditSequence()
    {
        $value = $this->getValue();
        if ($value) {
            foreach ($value as $item) {
                if (isset($item['EditSequence'])) {
                    return $item['EditSequence'];
                }
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        $value = $this->getValue();
        if ($value) {
            foreach ($value as $item) {
                if (isset($item['Name'])) {
                    return $item['Name'];
                }
            }
        }

        return false;
    }
}
