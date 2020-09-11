<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue;

use Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue as AbstractQueue;

/**
 * Class Index
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue
 */
class Index extends AbstractQueue
{
    /**
     * Execute
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Magenest_QuickBooksDesktop::queue');
        $resultPage->addBreadcrumb(__('Manage Queue'), __('Manage Queue'));
        $resultPage->addBreadcrumb(__('Manage Queue'), __('Manage Queue'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Queue'));

        return $resultPage;
    }
}
