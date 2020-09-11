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
 * Class CustomQueue
 * @package Magenest\QuickBooksDesktop\Model\ResourceModel
 */
class CustomQueue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Init
     */
    protected function _construct()
    {
        $this->_init('magenest_qbd_custom_queue', 'id');
    }

    public function insertMultiple($data)
    {
        $connection = $this->getConnection();
        $connection->insertArray($connection->getTableName('magenest_qbd_custom_queue'), array_keys($data[0]), $data);
        return $this;
    }

    public function updateAllStatus($type)
    {
        $connection = $this->getConnection();
        $connection->update(
            $connection->getTableName('magenest_qbd_custom_queue'),
            ['status' => \Magenest\QuickBooksDesktop\Model\CustomQueue::CUSTOM_QUEUE_STATUS_QUEUE, 'iterator_id' => null, 'ticket_id' => null],
            'type = ' . $type
        );
        return $this;
    }
}
