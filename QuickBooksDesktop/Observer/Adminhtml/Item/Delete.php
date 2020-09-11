<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Observer\Adminhtml\Item;

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
class Delete implements ObserverInterface
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

    protected $_queueHelper;

    /**
     * Update constructor.
     *
     * @param QueueFactory $queueFactory
     * @param CustomerFactory $customerFactory
     * @param Mapping $map
     * @param CreateQueue $_queueHelper
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
        $event = $observer->getEvent();
        /** @var \Magento\Catalog\Model\Product $item */
        $item = $event->getProduct();
        $productId = $item->getId();
        $companyId = $this->_queueHelper->getCompanyId();

        $this->_queueFactory->create()->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('entity_id', $productId)
            ->addFieldToFilter('type', 'Product')
            ->walk('delete');
    }
}
