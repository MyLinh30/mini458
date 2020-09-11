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
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magento\Backend\App\Action;
use Magenest\QuickBooksDesktop\Model\QueueFactory;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeProduct;

/**
 * Class SyncProduct
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\Queue
 */
class SyncProduct extends Action
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
    protected $productCollection;

    /**
     * SyncProduct constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param QueueFactory $queueFactory
     * @param Mapping $map
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        QueueFactory $queueFactory,
        CreateQueue $createQueue,
        Mapping $map
    ) {
        parent::__construct($context);
        $this->_queueHelper = $createQueue;
        $this->_scopeConfig = $scopeConfig;
        $this->_queueFactory = $queueFactory;
        $this->_map = $map;
    }

    public function execute()
    {
        try {
            $companyId = $this->_queueHelper->getCompanyId();

            if ($companyId) {
                $mappingCollection = $this->_map->getCollection()
                    ->addFieldToFilter('company_id', $companyId)
                    ->addFieldToFilter('type', Type::QUEUE_PRODUCT)
                    ->getColumnValues('entity_id');
                $allProductIds = $this->getCollection()->getAllIds();

                $queueCollection = $this->_queueFactory->create()->getCollection()
                    ->addFieldToFilter('company_id', $companyId)
                    ->addFieldToFilter('type', 'Product')->getColumnValues('entity_id');

                $productIdToQueue = array_diff(array_diff($allProductIds, $mappingCollection), $queueCollection);

                $productCollection = $this->getCollection()
                    ->addFieldToFilter('entity_id', ['in' => $productIdToQueue]);

                $totals = 0;
                foreach ($productCollection as $product) {
                    /** @var \Magento\Catalog\Model\Product $productModel */
                    $productModel = $this->_objectManager
                        ->create('\Magento\Catalog\Model\Product');
                    $productId = $product->getId();
                    $productModel = $productModel->load($productId);
                    $qty = $productModel->getExtensionAttributes()->getStockItem()->getQty();
                    $type = $productModel->getTypeId();
                    $modelCheck = $this->_queueFactory->create()->getCollection()
                        ->addFieldToFilter('type', 'Product')
                        ->addFieldToFilter('entity_id', $productId)
                        ->addFieldToFilter('company_id', $companyId)
                        ->addFieldToFilter('status', Status::STATUS_QUEUE);
                    if ($modelCheck->count() == 0) {
                        if ($qty > 0
                            || $type == 'virtual'
                            || $type == 'simple'
                            || $type == 'giftcard'
                            || $type == 'downloadable'
                        ) {
                            $this->_queueHelper->createItemInventoryAddQueue($productId);
                        } else {
                            $this->_queueHelper->createItemNonInventoryAddQueue($productId);
                        }
                    }
                    $totals++;

                }

                $this->messageManager->addSuccessMessage(
                    __(
                        sprintf('Totals %s Product Queue have been created/updated', $totals)
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
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|mixed
     */
    public function getCollection()
    {
        $date = $this->_scopeConfig->getValue('qbdesktop/qbd_setting/date_product');
        if (!$this->productCollection) {
            $this->productCollection = $this->_objectManager
                ->create('\Magento\Catalog\Model\ResourceModel\Product\Collection')
                ->addFieldToFilter('updated_at', ['gteq' => $date]);
        }

        return $this->productCollection;
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
