<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * peruvianlink.com extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package peruvianlink.com
 * @time: 19/08/2020 08:09
 */

namespace Magenest\QuickBooksDesktop\Controller\Connection\Product;

use Magenest\QuickBooksDesktop\Controller\Connection\Start;

class Sync extends Start
{
    /**
     * @return mixed
     */
    protected function getHandler()
    {
        $handler = $this->_objectManager->create('\Magenest\QuickBooksDesktop\WebConnector\Handlers\Product');

        return $handler;
    }
}
