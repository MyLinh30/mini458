<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Observer\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magento\Customer\Model\CustomerFactory;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;

/**
 * Class Edit
 * @package Magenest\QuickBooksDesktop\Observer\Customer
 */
class Edit implements ObserverInterface
{
    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var CreateQueue
     */
    protected $_queueHelper;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var Mapping
     */
    protected $_map;

    /**
     * Edit constructor.
     * @param QueueFactory $queueFactory
     * @param CustomerFactory $customerFactory
     * @param Mapping $map
     */
    public function __construct(
        QueueFactory $queueFactory,
        CreateQueue $createQueue,
        CustomerFactory $customerFactory,
        Mapping $map
    ) {
        $this->_queueHelper = $createQueue;
        $this->_customerFactory = $customerFactory;
        $this->_queueFactory = $queueFactory;
        $this->_map = $map;
    }

    /**
     * Cutomer edit information address
     *
     * @param Observer $observer
     *
     * @return string
     */
    public function execute(Observer $observer)
    {
        $redirectUrl = $_SERVER['REQUEST_URI'];
        if (strpos($redirectUrl, 'qbdesktop/customer/') === false) {
            $event = $observer->getEvent();
            $customer = $event->getCustomerAddress()->getCustomer();

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
