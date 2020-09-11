<?php


namespace Magenest\QuickBooksDesktop\WebConnector\Driver;

use Magenest\QuickBooksDesktop\WebConnector\Driver;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;

/**
 * Class Tax
 * @package Magenest\QuickBooksDesktop\WebConnector\Driver
 */
class Tax extends Driver
{

    /**
     * @var \Magenest\QuickBooksDesktop\Model\ResourceModel\CustomQueue\Collection
     */
    protected $taxCollection;

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

        return false;
    }

    /**
     * Get CustomQueue Collection
     *
     * @return \Magenest\QuickBooksDesktop\Model\ResourceModel\CustomQueue\Collection
     */
    public function getCollection()
    {
        $companyId = $this->_queueHelper->getCompanyId();
        if (!$this->taxCollection) {
            $this->taxCollection = $this->_objectManager->create('\Magenest\QuickBooksDesktop\Model\ResourceModel\CustomQueue\Collection')
                ->addFieldToFilter('company_id', $companyId)
                ->addFieldToFilter('type', TypeQuery::QUERY_TAX)
                ->addFieldToFilter('status', Status::STATUS_QUEUE);
        }

        return $this->taxCollection;
    }

    /**
     * @param null $ticket
     * @return \Magenest\QuickBooksDesktop\Model\Queue
     */
    public function getCurrentQueue($ticket = null)
    {
        $collection = $this->getCollection();

        return $collection->getFirstItem();
    }

    /**
     * @param $customQueue
     * @return string
     */
    public function prepareSendRequestXML($dataFromQWC)
    {
        $customQueue = $this->getCurrentQueue($dataFromQWC->ticket);
        /** @var \Magenest\QuickBooksDesktop\Model\CustomQueue $customQueue */
        $version = $this->_queueHelper->getQuickBooksVersion();

        if ($version == Version::VERSION_US) {
            $action = 'ItemSalesTaxQuery';
        } else {
            $action = 'SalesTaxCodeQuery';
        }

        $model = $this->_objectManager->create('\Magenest\QuickBooksDesktop\Model\CustomQueue')
            ->load($customQueue->getId());

        $operation = $model->getOperation();

        $xml = '<?xml version="1.0" encoding="utf-8"?>' .
            '<?qbxml version="13.0"?>' .
            '<QBXML>' .
            '<QBXMLMsgsRq onError="continueOnError">';


        if ($version == Version::VERSION_US) {
            if ($operation == 1) {
                $xml .= '<' . $action . 'Rq requestID="' . $customQueue->getId() . '" iterator="Start">';
            } else {
                $model = $this->_objectManager->create('\Magenest\QuickBooksDesktop\Model\CustomQueue')
                    ->load($customQueue->getId() - 1);
                $iteratorId = $model->getData('iterator_id');

                $xml .= '<' . $action . 'Rq requestID="' . $customQueue->getId() . '" iterator="Continue" iteratorID="' . $iteratorId . '">';
            }

            $xml .= '<MaxReturned>' . '30' . '</MaxReturned>';
            $xml .= '<ActiveStatus>' . 'All' . '</ActiveStatus>';
            $xml .= '<IncludeRetElement>' . 'ListID' . '</IncludeRetElement>';
            $xml .= '<IncludeRetElement>' . 'EditSequence' . '</IncludeRetElement>';
            $xml .= '<IncludeRetElement>' . 'Name' . '</IncludeRetElement>';
            $xml .= '</' . $action . 'Rq>';
            $xml .= '</QBXMLMsgsRq></QBXML>';
        } else {
            $number = $this->_scopeConfig->getValue(
                'qbdesktop/qbd_setting/number_tax',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $xml .= '<' . $action . 'Rq>';
            $xml .= '<MaxReturned>' . $number . '</MaxReturned>';
            $xml .= '<ActiveStatus>' . 'All' . '</ActiveStatus>';
            $xml .= '<IncludeRetElement>' . 'ListID' . '</IncludeRetElement>';
            $xml .= '<IncludeRetElement>' . 'EditSequence' . '</IncludeRetElement>';
            $xml .= '<IncludeRetElement>' . 'Name' . '</IncludeRetElement>';
            $xml .= '</' . $action . 'Rq>';
            $xml .= '</QBXMLMsgsRq></QBXML>';
        }


        return $xml;
    }
}
