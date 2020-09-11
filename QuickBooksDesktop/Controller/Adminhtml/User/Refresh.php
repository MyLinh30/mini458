<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\User;

use Magenest\QuickBooksDesktop\Controller\Adminhtml\User as AbstractUser;

/**
 * Class Refresh
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\User
 */
class Refresh extends \Magento\Backend\App\Action
{
    /**
     * execute
     */
    public function execute()
    {
        $this->messageManager->addSuccessMessage('Refesh success !');
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
        $this->_redirect('adminhtml/*/');

        return;
    }
}
