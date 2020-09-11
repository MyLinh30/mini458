<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Queue
 * @package Magenest\QuickBooksDesktop\Model
 * @method int getQueueId()
 * @method int getTicketId()
 * @method string getActionName()
 * @method int getType()
 * @method string getEnqueueDatetime()
 * @method string getDequeueDatetime()
 * @method int getStatus()
 * @method int getEntityId()
 * @method int getOperation()
 * @method int getQbdDeleteId()
 * @method string getMsg()
 * @method string getPayment()
 * @method string getVendorName()
 * @method Queue setStatus(int $status)
 * @method Queue setOrder(int)
 * @method Queue setMsg(string)
 */
class Queue extends AbstractModel
{
    /**
     * Initize
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksDesktop\Model\ResourceModel\Queue');
    }
}
