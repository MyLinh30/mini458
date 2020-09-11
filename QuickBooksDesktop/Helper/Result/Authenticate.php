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
class Authenticate extends Result
{
    /**
     * @var array
     */
    protected $authenticateResult;

    /**
     * Authenticate constructor.
     * @param string $ticket
     * @param string $status
     */
    public function __construct($ticket, $status)
    {
        $this->authenticateResult = [$ticket, $status];
    }
}
