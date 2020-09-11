<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model\Config\Source\Queue;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 * @package Magenest\QuickBooksDesktop\Model\Config\Source
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{

    const STATUS_QUEUE = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 3;

    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => 'Queue', 'value' => self::STATUS_QUEUE];
        $options[] = ['label' => 'Success', 'value' => self::STATUS_SUCCESS];
        $options[] = ['label' => 'Fail', 'value' => self::STATUS_FAIL];
        return $options;
    }
}
