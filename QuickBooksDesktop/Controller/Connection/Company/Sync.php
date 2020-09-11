<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Connection\Company;

use Magenest\QuickBooksDesktop\Controller\Connection\Start;

/**
 * Class Sync
 * @package Magenest\QuickBooksDesktop\Controller\Connection\Company
 */
class Sync extends Start
{
    /**
     * @return mixed
     */
    protected function getHandler()
    {
        $handler = $this->_objectManager->create('\Magenest\QuickBooksDesktop\WebConnector\Handlers\Company');

        return $handler;
    }
}
