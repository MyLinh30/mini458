<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace   Magenest\QuickBooksDesktop\Model\ResourceModel;

/**
 * Class Queue
 * @package Magenest\QuickBooksDesktop\Model\ResourceModel
 */
class ItemSalesOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    /**
     * Init
     */
    protected function _construct()
    {
        $this->_init('magenest_qbd_item_sales_order', 'id');
    }
}
