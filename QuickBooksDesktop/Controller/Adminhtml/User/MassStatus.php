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
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassStatus
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\User
 */
class MassStatus extends AbstractUser
{
    /**
     * Mass Change Status
     *
     * @return $this
     */
    public function execute()
    {
        try {
            $collections = $this->_filter->getCollection($this->_collectionFactory->create());
            $status = (int)$this->getRequest()->getParam('status');
            $totals = 0;
            foreach ($collections as $item) {
                /** @var \Magenest\QuickBooksDesktop\Model\User $item */
                $item->setStatus($status)->save();
                $totals++;
            }

            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $totals));
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while updating the mapping(s) status.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*');
    }
}
