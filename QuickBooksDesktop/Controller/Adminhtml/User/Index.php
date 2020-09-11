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
 * Class Index
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\User
 */
class Index extends AbstractUser
{
    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Magenest_QuickBooksDesktop::user');
        $resultPage->addBreadcrumb(__('Manage User'), __('Manage User'));
        $resultPage->addBreadcrumb(__('Manage User'), __('Manage User'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage User'));

        return  $resultPage;
    }
}
