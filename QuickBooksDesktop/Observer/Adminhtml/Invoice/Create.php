<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Observer\Adminhtml\Invoice;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface as ObserverInterface;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magenest\QuickBooksDesktop\Model\MappingFactory;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;

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
     * @var CreateQueue
     */
    protected $_queueHelper;

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

    /**
     * @var
     */
    protected $logger;

    /**
     * @var MappingFactory
     */
    protected $_map;

    /**
     * Create constructor.
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        MappingFactory $map,
        CreateQueue $queueHelper
    ) {
        $this->messageManager = $messageManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_map = $map;
        $this->_queueHelper = $queueHelper;
    }

    /**
     * Invoice created
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $redirectUrl = $_SERVER['REQUEST_URI'];
        if (strpos($redirectUrl, 'qbdesktop/create') === false) {
            $invoice = $observer->getEvent()->getInvoice();

            $invoiceDate = $this->_queueHelper->getInvoiceDate();
            if(strtotime($invoice->getCreatedAt()) < strtotime($invoiceDate)){
                return;
            }

            $invoiceId = $invoice->getId();
            if ($invoice->getState() == 1) {  // Open
                $this->_queueHelper->createOpenInvoiceQueue($invoiceId);
            } elseif ($invoice->getState() == 2) { //Paid
                $check = $this->_map->create()->getCollection()
                    ->addFieldToFilter('company_id', $this->_queueHelper->getCompanyId())
                    ->addFieldToFilter('type', Type::QUEUE_INVOICE)
                    ->addFieldToFilter('entity_id', $invoiceId)
                    ->getFirstItem();
                if (!$check->getId()) {
                    $this->_queueHelper->createOpenInvoiceQueue($invoiceId);
                }
                $this->_queueHelper->createPaidInvoiceQueue($invoiceId);
            }
        }
    }
}
