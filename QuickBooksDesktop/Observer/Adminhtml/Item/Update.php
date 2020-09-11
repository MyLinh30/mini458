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
use Magenest\QuickBooksDesktop\Helper\CreateQueue;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Model\Company;
use Magento\Catalog\Model\ProductFactory;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Priority;

/**
 * Class Update
 *
 * @package Magenest\QuickBooksDesktop\Observer\Item
 */
class Update implements ObserverInterface
{
    /**
     * @var QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Mapping
     */
    protected $_map;

    /**
     * @var Company
     */
    protected $_company;

    /**
     * @var
     */
    protected $productMapping;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $_stockItemRepository;

    /**
     * @var CreateQueue
     */
    protected $_queueHelper;

    /**
     * Update constructor.
     * @param QueueFactory $queueFactory
     * @param ProductFactory $productFactory
     * @param Mapping $map
     * @param Company $company
     */
    public function __construct(
        QueueFactory $queueFactory,
        CreateQueue $_queueHelper,
        ProductFactory $productFactory,
        Mapping $map,
        Company $company,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
    ) {
        $this->_queueHelper = $_queueHelper;
        $this->_queueFactory = $queueFactory;
        $this->_productFactory = $productFactory;
        $this->_map = $map;
        $this->_company = $company;
        $this->_stockItemRepository = $stockItemRepository;
    }

    /**
     * Admin save a Product
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $redirectUrl = $_SERVER['REQUEST_URI'];
        if (strpos($redirectUrl, 'qbdesktop/price/update') === false
            && strpos($redirectUrl, 'qbdesktop/product/') === false) {
            $product = $observer->getEvent()->getProduct();

            $productDate = $this->_queueHelper->getProductDate();
            if(strtotime($product->getUpdatedAt()) < strtotime($productDate)){
                return;
            }

            $productId = $product->getId();
            if ($productId) {
                $productType = $product->getTypeId();
                $companyId = $this->_queueHelper->getCompanyId();

                $qbId = $this->_map->getCollection()
                    ->addFieldToFilter('company_id', $companyId)
                    ->addFieldToFilter('type', Type::QUEUE_PRODUCT)
                    ->addFieldToFilter('entity_id', $productId)
                    ->getFirstItem()->getData();

                $operation = $qbId ? Operation::OPERATION_MOD : Operation::OPERATION_ADD;
                $action = $this->getActionName($qbId, $productType);
                $this->_queueHelper->createQueue($productId, $action, 'Product', $operation, Priority::PRIORITY_PRODUCT);
            }
        }
    }

    /**
     * Get Action and Item Type
     *
     * @param $qty
     * @param $qbId
     * @return string
     */
    protected function getActionName($qbId, $productType)
    {
        if ($productType == 'virtual' || $productType == 'simple' || $productType == 'giftcard' || $productType == 'downloadable') {
            $itemType = 'ItemInventory';
        } else {
            $itemType = 'ItemNonInventory';
        }
        if ($qbId) {
            $action = 'Mod';
        } else {
            $action = 'Add';
        }

        return $itemType . $action;
    }
}
