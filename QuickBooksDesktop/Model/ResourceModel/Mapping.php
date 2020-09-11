<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Mapping
 * @package Magenest\QuickBooksDesktop\Model\ResourceModel
 */
class Mapping extends AbstractDb
{
    /**
     * Init
     */
    protected function _construct()
    {
        $this->_init('magenest_qbd_mapping', 'id');
    }

    public function insertMultipleData($data, $replace = true)
    {
        $connection = $this->getConnection();
        $tableName = $connection->getTableName('magenest_qbd_mapping');
        if ($replace) {
            $connection->insertOnDuplicate($tableName, $data, ['list_id', 'edit_sequence']);
        } else {
            $connection->insertArray($tableName, array_keys($data), $data);
        }

        return $this;
    }
}
