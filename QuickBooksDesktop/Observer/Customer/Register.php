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
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;

/**
 * Class Register
 *
 * @package Magenest\QuickBooksDesktop\Observer\Customer
 */
class Register implements ObserverInterface
{
    /**
     * @var CreateQueue
     */
    protected $_queueHelper;

    /**
     * Register constructor.
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        CreateQueue $queueHelper
    ) {
        $this->_queueHelper = $queueHelper;
    }

    /**
     * Cutomer add account
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        $this->_queueHelper->createCustomerQueue($customer, 'Add', Operation::OPERATION_ADD);
    }
}
