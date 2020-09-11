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
use Magento\Backend\App\Action;

/**
 * Class Save
 *
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\User
 */
class Save extends \Magento\Backend\App\Action
{
    protected $user;

    public function __construct(
        Action\Context $context,
        \Magenest\QuickBooksDesktop\Model\UserFactory $user
    ) {
        parent::__construct($context);
        $this->user = $user;
    }

    /**
     * Save user
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $model = $this->user->create();

            if (!empty($data['user_id'])) {
                $model->load($data['user_id']);
                if ($data['user_id'] != $model->getUserId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Wrong invoice rule.'));
                }
                $info = [
                    'username' => $data['username'],
                    'status' => $data['status'],
                ];

                if (!empty($data['password'])) {
                    $info['password'] = md5($data['password']);
                }
                $checkUserName = $this->user->create()->getCollection()
                    ->addFieldToFilter('username', $data['username'])
                    ->addFieldToFilter('user_id', ['neq' => $data['user_id']])
                    ->getLastItem()->getData();
                if (count($checkUserName)) {
                    $this->messageManager->addErrorMessage("Duplicate Username");
                    return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                }
            } else {
                $info = [
                    'username' => $data['username'],
                    'password' => md5($data['password']),
                    'status' => $data['status'],
                ];

                $checkUserName = $this->user->create()->getCollection()
                    ->addFieldToFilter('username', $data['username'])
                    ->getLastItem()->getData();
                if (count($checkUserName)) {
                    $this->messageManager->addErrorMessage("Duplicate Username");
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $model->addData($info);
            $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($model->getData());
            try {
                $model->save();

                $this->messageManager->addSuccessMessage(__('User has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e, __('Something went wrong while saving the user.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
