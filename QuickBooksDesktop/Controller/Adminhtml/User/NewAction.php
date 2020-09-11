<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\User;

use Magento\Backend\App\Action;
use Magenest\QuickBooksDesktop\Controller\Adminhtml\User as AbstractUser;

/**
 * Class NewAction
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\User
 */
class NewAction extends AbstractUser
{
    /**
     * forward to edit
     *
     * @return $this
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();

        return $resultForward->forward('edit');
    }
}
