<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Observer\Adminhtml\CreditMemo;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface as ObserverInterface;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Create
 * @package Magenest\QuickBooksDesktop\Observer\Invoice
 */
class Create implements ObserverInterface
{
    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * Core Config Data
     *
     * @var $_scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    protected $_queueHelper;

    /**
     * @var
     */
    protected $logger;

    /**
     * Create constructor.
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        QueueFactory $queueFactory,
        CreateQueue $_queueHelper
    ) {
        $this->_queueHelper = $_queueHelper;
        $this->messageManager = $messageManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_queueFactory = $queueFactory;
    }

    /**
     * Invoice created
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();

        $creditmemoDate = $this->_queueHelper->getCreditmemoDate();
        if(strtotime($creditmemo->getCreatedAt()) < strtotime($creditmemoDate)){
            return;
        }

        $creditmemoId = $creditmemo->getId();
        $this->_queueHelper->createCreditMemoQueue($creditmemoId);
    }
}
