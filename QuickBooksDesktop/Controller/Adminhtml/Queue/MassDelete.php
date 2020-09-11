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
 * Class MassDelete
 *
 * @package Magenest\ProductLabel\Controller\Adminhtml\Label
 */
class MassDelete extends AbstractQueue
{

    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collections = $this->_filter->getCollection($this->_collectionFactory->create());
            $totals = $collections->count();
            $collections->walk('delete');
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deteled.', $totals));
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while delete the post(s).'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
