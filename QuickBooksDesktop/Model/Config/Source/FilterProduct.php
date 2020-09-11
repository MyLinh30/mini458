<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model\Config\Source;

/**
 * Class Filter
 * @package Magenest\QuickBooksDesktop\Model\Config\Source
 */
class FilterProduct implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('All')],
            ['value' => '2', 'label' => __('Cronjob')],
        ];
    }
}
