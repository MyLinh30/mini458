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
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Limited;

/**
 * Class SyncCustomer
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue
 */
class SyncCustomer extends Action
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
    protected $customerCollection;

    /**
     * SyncCustomer constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     * @param Mapping $map
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        QueueFactory $queueFactory,
        CreateQueue $_queueHelper,
        Mapping $map
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_queueFactory = $queueFactory;
        $this->_queueHelper = $_queueHelper;
        $this->_map = $map;
    }

    /**
     * sync customer to queue table
     */
    public function execute()
    {
        try {
            $companyId = $this->_queueHelper->getCompanyId();

            if ($companyId) {
                $mappingCollection = $this->_queueHelper->checkMapping([Type::QUEUE_CUSTOMER]);
                $customerCollection = $this->getCollection()->setOrder('entity_id', 'ASC');
                if (count($mappingCollection)) {
                    $customerCollection->addFieldToFilter('entity_id', ['nin' => $mappingCollection]);
                }
                $queueCollection = $this->_queueFactory->create()->getCollection()
                    ->addFieldToFilter('company_id', $companyId)
                    ->addFieldToFilter('type', 'Customer')->getColumnValues('entity_id');
                if (count($queueCollection)) {
                    $customerCollection->addFieldToFilter('entity_id', ['nin' => $queueCollection]);
                }
                $customerCollection->setPageSize(Limited::LIMITED_CUSTOMER);
                $lastPage = $customerCollection->getLastPageNumber();

                $totals = 0;
                for ($currentPage = 1; $currentPage <= $lastPage; $currentPage++) {
                    $customerCollection->clear();
                    $customerCollection->setCurPage($currentPage);
                    foreach ($customerCollection as $customer) {
                        $id = $customer->getId();
                        $check = $this->_queueHelper->checkQueue($id, 'Customer');
                        if ($check->count() == 0) {
                            $this->_queueHelper->createCustomerQueue($customer, "Add", Operation::OPERATION_ADD);
                            $totals++;
                        }
                    }
                }

                $this->messageManager->addSuccessMessage(
                    __(
                        sprintf('Totals %s Customer Queue have been created/updated', $totals)
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
     * Customer Collection
     *
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollection()
    {
        $date = $this->_scopeConfig->getValue('qbdesktop/qbd_setting/date_customer');
        if (!$this->customerCollection) {
            $this->customerCollection = $this->_objectManager
                ->create('\Magento\Customer\Model\ResourceModel\Customer\Collection')
                ->addFieldToFilter('updated_at', ['gteq' => $date]);
        }

        return $this->customerCollection;
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
