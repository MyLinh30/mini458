<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Helper\Result;

use Magenest\QuickBooksDesktop\Helper\Result;

/**
 * Class Authenticate
 * @package Magenest\QuickBooksDesktop\Model\Result
 */
class SendRequestXML extends Result
{
    /**
     * @var string
     */
    protected $sendRequestXMLResult;

    /**
     * SendRequestXML constructor.
     *
     * @param string $xml
     */
    public function __construct($xml)
    {
        $this->sendRequestXMLResult = $xml;
    }
}
