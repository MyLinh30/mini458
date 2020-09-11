<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 * @package Magenest\QuickBooksDesktop\Model
 */
class Status extends AbstractSource
{
    /**#@+
     * Status values
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [self::STATUS_ENABLED => __('Enable'), self::STATUS_DISABLED => __('Disable')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
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
        if ($optionId == self::STATUS_ENABLED) {
            $html = '<span class="grid-severity-notice"><span>' . $options[$optionId] . '</span>'.'</span>';
        } else {
            $html = '<span class="grid-severity-critical"><span>' . $options[$optionId] . '</span></span>';
        }

        return $html;
    }
}
