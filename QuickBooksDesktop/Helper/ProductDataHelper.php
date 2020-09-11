<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * peruvianlink.com extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package peruvianlink.com
 * @time: 21/08/2020 08:36
 */

namespace Magenest\QuickBooksDesktop\Helper;


class ProductDataHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_product;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockStateInterface;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * ProductDataHelper constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product $product,
        \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->_product = $product;
        $this->_stockStateInterface = $stockStateInterface;
        $this->_stockRegistry = $stockRegistry;
        parent::__construct($context);
    }

    /**
     * For Update stock of product
     * @param string $sku which stock you want to update
     * @param array $stockData your updated data
     * @return void
     */
    public function updateProductStock($sku, $stockData)
    {
        $stockItem = $this->_stockRegistry->getStockItemBySku($sku);
        $stockItem->setData('is_in_stock', $stockData['is_in_stock']); //set updated data as your requirement
        $stockItem->setData('qty', $stockData['qty']); //set updated quantity
        $stockItem->setData('use_config_notify_stock_qty', 1);
        $this->_stockRegistry->updateStockItemBySku($sku, $stockItem);
    }
}
