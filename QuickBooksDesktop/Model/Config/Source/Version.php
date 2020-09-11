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
 * Class Version
 * @package Magenest\QuickBooksDesktop\Model\Config\Source
 */
class Version implements \Magento\Framework\Option\ArrayInterface
{

    const VERSION_CANADA = 1;
    const VERSION_US = 2;
    const VERSION_UK = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Canada') ],
            ['value' => '2', 'label' => __('United States')],
            ['value' => '3', 'label' => __('United Kingdom')],
        ];
    }
}
