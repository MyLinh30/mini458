<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue;

use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Limited;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Priority;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magento\Backend\App\Action;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;

/**
 * Class SyncOrder
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue
 */
class SyncOrder extends Action
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $orderCollection;

    /**
     * SyncOrder constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     * @param Mapping $map
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        QueueFactory $queueFactory,
        CreateQueue $queueHelper,
        Mapping $map
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_queueFactory = $queueFactory;
        $this->_queueHelper = $queueHelper;
        $this->_map = $map;
    }

    public function execute()
    {
        try {
            $companyId = $this->_queueHelper->getCompanyId();

            if ($companyId) {
                $mappingCollection = $this->_queueHelper->checkMapping([Type::QUEUE_SALESORDER]);
                $orderCollection = $this->getCollection()->setOrder('entity_id', 'ASC');
                if (count($mappingCollection)) {
                    $orderCollection->addFieldToFilter('entity_id', ['nin' => $mappingCollection]);
                }
                $queueCollection = $this->_queueFactory->create()->getCollection()
                    ->addFieldToFilter('company_id', $companyId)
                    ->addFieldToFilter('type', 'SalesOrder')->getColumnValues('entity_id');
                if (count($queueCollection)) {
                    $orderCollection->addFieldToFilter('entity_id', ['nin' => $queueCollection]);
                }

                $orderCollection->setPageSize(Limited::LIMITED_ORDER);
                $lastPage = $orderCollection->getLastPageNumber();

                $totals = 0;
                for ($currentPage = 1; $currentPage <= $lastPage; $currentPage++) {
                    $orderCollection->clear();
                    $orderCollection->setCurPage($currentPage);
                    foreach ($orderCollection as $order) {
                        $id = $order->getId();
                        $check = $this->_queueHelper->checkQueue($id, 'SalesOrder');
                        if ($check->count() == 0) {
                            if (!$order->getCustomerId()) {
                                $qbId = in_array($id, $this->_queueHelper->checkMapping([Type::QUEUE_GUEST]));
                                if (!$qbId) {
                                    $this->_queueHelper->createGuestQueue($order, 'Add', Operation::OPERATION_ADD);
                                }
                            }
                            $this->_queueHelper->createTransactionQueue($id, 'SalesOrder', Priority::PRIORITY_SALESORDER);
                            $totals++;
                        }
                    }
                }

                $this->messageManager->addSuccessMessage(
                    __(
                        sprintf('Totals %s Order Queue have been created/updated', $totals)
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

    /**
     * Order Collection
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollection()
    {
        $date = $this->_scopeConfig->getValue('qbdesktop/qbd_setting/date_sales_order');
        if (!$this->orderCollection) {
            $this->orderCollection = $this->_objectManager
                ->create('\Magento\Sales\Model\ResourceModel\Order\Collection')
                ->addFieldToFilter('created_at', ['gteq' => $date]);
        }

        return $this->orderCollection;
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
