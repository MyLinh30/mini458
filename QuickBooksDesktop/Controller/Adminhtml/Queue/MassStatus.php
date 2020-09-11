<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue as QueueController;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;

/**
 * Class MassStatus
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue
 */
class MassStatus extends QueueController
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $status = (int)$this->getRequest()->getParam('status');
            $totals = 0;
            foreach ($collection as $item) {
                /** @var \Magenest\QuickBooksDesktop\Model\Queue $item */
                if ($item->getStatus() == Status::STATUS_SUCCESS) {
                    $item->setMsg('The queue has been synchronized');
                    $item->save();
                    $totals++;
                } else {
                    $item->setStatus($status);
                    $item->setMsg(null);
                    $item->setTicketId(null);
                    $item->save();
                    $totals++;
                }
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $totals));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while updating the queue(s) status.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
