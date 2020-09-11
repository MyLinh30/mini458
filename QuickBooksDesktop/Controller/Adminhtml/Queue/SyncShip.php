<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
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
 * Class SyncShip
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue
 */
class SyncShip extends Action
{
    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var CreateQueue
     */
    protected $_queueHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * SyncShip constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     * @param Mapping $map
     * @param \Magento\Shipping\Model\Config $shippingConfig
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        QueueFactory $queueFactory,
        Mapping $map,
        CreateQueue $createQueue,
        \Magento\Shipping\Model\Config $shippingConfig
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_queueFactory = $queueFactory;
        $this->_queueHelper = $createQueue;
        $this->_map = $map;
        $this->_shippingConfig = $shippingConfig;
    }

    public function execute()
    {
        try {
            $companyId = $this->_queueHelper->getCompanyId();

            if ($companyId) {
                $totals = 0;

                $status = $this->getAddOtherShipping('Shipping', 'ItemOtherCharge');
                $totals = $status === true ? $totals + 1 : $totals;
                $status = $this->getAddOtherShipping('Discount', 'ItemDiscount');
                $totals = $status === true ? $totals + 1 : $totals;

                $shippingMethodList = $this->_shippingConfig->getAllCarriers();
                foreach ($shippingMethodList as $code => $data) {
                    if ($data['id'] != 'ups' && $data['id'] != 'dhl') {
                        $status = $this->getAddOtherShipping($data['id'], 'ShipMethod');
                        $totals = $status === true ? $totals + 1 : $totals;
                    }
                }
                $this->messageManager->addSuccessMessage(
                    __(
                        sprintf('Totals %s Shipping Methods Queue have been created/updated', $totals)
                    )
                );
            } else {
                $this->messageManager->addErrorMessage('The company is not connected');
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    public function getAddOtherShipping($name, $type)
    {
        $companyId = $this->_queueHelper->getCompanyId();
        $check = $this->_map->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', Type::QUEUE_SHIPMETHOD)
            ->addFieldToFilter('payment', $name)
            ->getLastItem()
            ->getData('list_id');
        if (empty($check)) {
            $info = [
                'action_name' => $type . 'Add',
                'enqueue_datetime' => time(),
                'dequeue_datetime' => '',
                'type' => $type,
                'status' => Status::STATUS_QUEUE,
                'payment' => $name,
                'company_id' => $companyId,
                'operation' => Operation::OPERATION_ADD,
                'priority' => Priority::PRIORITY_SHIPMETHOD
            ];
            $model = $this->_queueFactory->create();
            $modelCheck = $model->getCollection()
                ->addFieldToFilter('type', $type)
                ->addFieldToFilter('payment', $name)
                ->addFieldToFilter('company_id', $companyId)
                ->addFieldToFilter('status', Status::STATUS_QUEUE)
                ->getLastItem();
            $modelCheck->addData($info);
            $modelCheck->save();
            return true;
        }
        return false;
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
