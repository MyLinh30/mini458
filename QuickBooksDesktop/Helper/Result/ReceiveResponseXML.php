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
class ReceiveResponseXML extends Result
{
    /**
     * Integer indicating update progress
     *
     * @var integer
     */
    public $receiveResponseXMLResult;

    /**
     * ResponseRequestXML constructor.
     * @param $complete
     */
    public function __construct($complete)
    {
        $this->receiveResponseXMLResult = $complete;
    }
}
