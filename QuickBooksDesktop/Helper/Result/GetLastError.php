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
class GetLastError extends Result
{
    /**
     * An error message
     *
     * @param string $resp
     */
    public $getLastErrorResult;

    /**
     * GetLastError constructor.
     * @param $result
     */
    public function __construct($result)
    {
        $this->getLastErrorResult = $result;
    }
}
