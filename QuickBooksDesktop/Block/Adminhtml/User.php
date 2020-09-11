<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Block\Adminhtml;

/**
 * Class User
 * @package Magenest\QuickBooksDesktop\Block\Adminhtml
 */
class User extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_QuickBooksDesktop';
        $this->_controller = 'adminhtml_user';
        
        parent::_construct();
    }
}
