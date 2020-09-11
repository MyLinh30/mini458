<?php

namespace Magenest\QuickBooksDesktop\Block\Adminhtml;

/**
 * Class TaxCode
 * @package Magenest\QuickBooksDesktop\Block\Adminhtml
 */
class TaxCode extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_QuickBooksDesktop';
        $this->_controller = 'adminhtml_tax';
        parent::_construct();
        $this->buttonList->remove('add');
        $this->buttonList->add('apply', [
            'id' => 'tax_code_apply',
            'label' => __('Apply'),
            'class' => 'primary',
        ], '-1', 10);
    }
}
