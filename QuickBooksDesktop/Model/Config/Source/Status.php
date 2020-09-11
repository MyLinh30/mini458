<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 * @package Magenest\QuickBooksDesktop\Model\Config\Source
 */
class Status extends AbstractSource
{
    /**#@+
     * Status values
     */
    const STATUS_QUEUE = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 3;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::STATUS_QUEUE => __('Queue'),
            self::STATUS_SUCCESS => __('Success'),
            self::STATUS_FAIL => __('Fail'),
            ];
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $result = [];
        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        
        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionGrid($optionId)
    {
        $options = self::getOptionArray();
        if ($optionId == self::STATUS_QUEUE) {
            $html = '<span class="grid-severity-minor"><span>' . $options[$optionId] . '</span>'.'</span>';
        } elseif ($optionId == self::STATUS_SUCCESS) {
            $html = '<span class="grid-severity-notice"><span>' . $options[$optionId] . '</span></span>';
        } else {
            $html = '<span class="grid-severity-critical"><span>' . $options[$optionId] . '</span></span>';
        }

        return $html;
    }
}
