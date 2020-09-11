<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Observer\Adminhtml\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magento\Customer\Model\CustomerFactory;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;

/**
 * Class Update
 *
 * @package Magenest\QuickBooksDesktop\Observer\Customer
 */
class Update implements ObserverInterface
{
    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var Mapping
     */
    protected $_map;

    /**
     * @var CreateQueue
     */
    protected $_queueHelper;

    /**
     * Update constructor.
     * @param QueueFactory $queueFactory
     * @param CustomerFactory $customerFactory
     * @param Mapping $map
     */
    public function __construct(
        QueueFactory $queueFactory,
        CustomerFactory $customerFactory,
        Mapping $map,
        CreateQueue $_queueHelper
    ) {
        $this->_queueHelper = $_queueHelper;
        $this->_customerFactory = $customerFactory;
        $this->_queueFactory = $queueFactory;
        $this->_map = $map;
    }

    /**
     * Admin edit information
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $redirectUrl = $_SERVER['REQUEST_URI'];
        if (strpos($redirectUrl, 'qbdesktop/customer/') === false) {
            $event = $observer->getEvent();
            /** @var \Magento\Framework\Event\Observer $customer */
            $customer = $event->getCustomer();

            $customerDate = $this->_queueHelper->getCustomerDate();
            if(strtotime($customer->getUpdatedAt()) < strtotime($customerDate)){
                return;
            }

            $customerId = $customer->getId();
            $companyId = $this->_queueHelper->getCompanyId();

            $qbId = $this->_map->getCollection()
                ->addFieldToFilter('company_id', $companyId)
                ->addFieldToFilter('type', Type::QUEUE_CUSTOMER)
                ->addFieldToFilter('entity_id', $customerId)
                ->getFirstItem()->getData();

            $action = $qbId ? 'Mod' : 'Add';
            $operation = $qbId ? Operation::OPERATION_MOD : Operation::OPERATION_ADD;
            $this->_queueHelper->createCustomerQueue($customer, $action, $operation);
        }
    }
}
