<?php
/**
 * Created by PhpStorm.
 * User: Toan FrontEnd
 * Date: 7/30/2018
 * Time: 3:05 PM
 */

namespace Magenest\QuickBooksDesktop\Model\Config\Source\Queue;

/**
 * Class Operation
 * @package Magenest\QuickBooksDesktop\Model\Config\Source\Queue
 */
class Operation implements \Magento\Framework\Option\ArrayInterface
{

    const OPERATION_MOD = 1;
    const OPERATION_ADD = 2;

    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => 'Edit', 'value' => self::OPERATION_MOD];
        $options[] = ['label' => 'Add', 'value' => self::OPERATION_ADD];
        return $options;
    }
}
