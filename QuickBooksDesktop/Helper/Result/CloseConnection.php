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
class CloseConnection extends Result
{
    /**
     * @var string
     */
    public $closeConnectionResult;

    /**
     * CloseConnection constructor
     *
     * @param string $result
     */
    public function __construct($result = 'Complete !')
    {
        $this->closeConnectionResult = $result;
    }
}
