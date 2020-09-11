<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\WebConnector\Driver;

use Magenest\QuickBooksDesktop\WebConnector\Driver;

/**
 * Class Company
 * @package Magenest\QuickBooksDesktop\WebConnector\Driver
 */
class Company extends Driver
{
    /**
     * @return bool|int
     */
    public function getTotalsQueue()
    {
        return true;
    }

    /**
     * @return \Magenest\QuickBooksDesktop\Model\Queue
     */
    public function getCurrentQueue($ticket = null)
    {
        return true;
    }

    /**
     * Company Collection
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareSendRequestXML($dataFromQWC)
    {
        $action = 'CompanyQuery';
        /** @var \Magenest\QuickBooksDesktop\Model\QBXML\Company $model */
        $xml = '<?xml version="1.0" encoding="utf-8"?>' .
            '<?qbxml version="13.0"?>' .
            '<QBXML>' .
            '<QBXMLMsgsRq onError="stopOnError">';
        $xml .= '<' . $action . 'Rq>';
        $xml .= '</' . $action . 'Rq>';
        $xml .= '</QBXMLMsgsRq></QBXML>';

        return $xml;
    }
}
