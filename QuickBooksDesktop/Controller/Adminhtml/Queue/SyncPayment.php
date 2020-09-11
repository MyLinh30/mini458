<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue;

use Magenest\QuickBooksDesktop\Model\Mapping;
use Magento\Backend\App\Action;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Priority;

/**
 * Class SyncPayment
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue
 */
class SyncPayment extends Action
{
    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var CreateQueue
     */
    protected $_queueHelper;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * SyncPayment constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     * @param Mapping $map
     */
    public function __construct(
        Action\Context $context,
        CreateQueue $queueHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        QueueFactory $queueFactory,
        Mapping $map
    ) {
        parent::__construct($context);
        $this->_queueHelper = $queueHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_queueHelper = $queueHelper;
        $this->_queueFactory = $queueFactory;
        $this->_map = $map;
    }

    /**
     *
     */
    public function execute()
    {
        try {
            $totals = 0;
            $paymentMethodsList = $this->_scopeConfig->getValue('payment');
            $companyId = $this->_queueHelper->getCompanyId();
            if ($companyId) {
                foreach ($paymentMethodsList as $code => $data) {
                    $check = $this->_map->getCollection()
                        ->addFieldToFilter('company_id', $companyId)
                        ->addFieldToFilter('type', Type::QUEUE_PAYMENTMETHOD)
                        ->addFieldToFilter('payment', $code)
                        ->getLastItem()->getData('list_id');

                    if (empty($check)) {
                        $info = [
                            'action_name' => 'PaymentMethodAdd',
                            'enqueue_datetime' => time(),
                            'dequeue_datetime' => '',
                            'type' => 'PaymentMethod',
                            'status' => Status::STATUS_QUEUE,
                            'payment' => $code,
                            'operation' => Operation::OPERATION_ADD,
                            'company_id' => $companyId,
                            'priority' => Priority::PRIORITY_PAYMENTMETHOD
                        ];
                        $model = $this->_queueFactory->create();
                        $modelCheck = $model->getCollection()
                            ->addFieldToFilter('type', 'PaymentMethod')
                            ->addFieldToFilter('payment', $code)
                            ->addFieldToFilter('company_id', $companyId)
                            ->addFieldToFilter('status', Status::STATUS_QUEUE)
                            ->getLastItem();
                        $modelCheck->addData($info);
                        $modelCheck->save();
                        $totals++;
                    }
                }

                $this->messageManager->addSuccessMessage(
                    __(
                        sprintf('Totals %s Payment Methods Queue have been created/updated', $totals)
                    )
                );
            } else {
                $this->messageManager->addErrorMessage('The company is not connected');
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        $this->_redirect('*/*/syncShip');
    }

    /**
     * Always true
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return true;
    }
}
