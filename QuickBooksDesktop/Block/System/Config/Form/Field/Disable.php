<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Disable
 * @param AbstractElement $element
 * @package Magenest\QuickBooksDesktop\Block\System\Config\Form\Field
 */
class Disable extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function getElementHtml(AbstractElement $element)
    {
        $element->setDisabled('disabled');
        
        return $element->getElementHtml();
    }
}
