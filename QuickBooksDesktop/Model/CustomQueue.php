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
 * Class CustomQueue
 * @package Magenest\QuickBooksDesktop\Model
 * @method int getId()
 * @method int getTicketId()
 * @method int getCompanyId()
 * @method int getStatus()
 * @method int getIteratorId()
 * @method int getOperation()
 * @method CustomQueue setStatus(int $status)

 */
class CustomQueue extends AbstractModel
{
    const OPERATION_START = 1;
    const OPERATION_CONTINUE = 2;

    const CUSTOM_QUEUE_STATUS_ERROR = 3;
    const CUSTOM_QUEUE_STATUS_SUCCESS = 2;
    const CUSTOM_QUEUE_STATUS_QUEUE = 1;

    /**
     * Initize
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksDesktop\Model\ResourceModel\CustomQueue');
    }

    public function insertMultiple($data)
    {
        return $this->getResource()->insertMultiple($data);
    }

    public function updateAllStatus($type)
    {
        if ($type) {
            $this->getResource()->updateAllStatus($type);
        }
        return $this;
    }

    public function saveCustomQueueStatus($ticketId, $statusCode, $iteratorId)
    {
        $this->setTicketId($ticketId);
        $this->setStatus($statusCode);
        $this->setIteratorId($iteratorId);

        return $this;
    }
}
