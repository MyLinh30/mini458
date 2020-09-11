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
 * Class Operation
 * @package Magenest\QuickBooksDesktop\Model\Config\Source
 */
class Operation extends AbstractSource
{
    /**#@+
     * Status values
     */
    const OPERATION_QUERY = 0;
    const OPERATION_MOD = 1;
    const OPERATION_ADD = 2;
    const OPERATION_DELETE = 3;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::OPERATION_QUERY => __('Query'),
            self::OPERATION_MOD => __('Edit'),
            self::OPERATION_ADD => __('Add'),
            self::OPERATION_DELETE => __('Delete'),
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
        if ($optionId == self::OPERATION_MOD) {
            $html = '<span class="grid-severity-minor"><span>' . $options[$optionId] . '</span>'.'</span>';
        } elseif ($optionId == self::OPERATION_ADD) {
            $html = '<span class="grid-severity-notice"><span>' . $options[$optionId] . '</span></span>';
        } else {
            $html = '<span class="grid-severity-critical"><span>' . $options[$optionId] . '</span></span>';
        }

        return $html;
    }
}
