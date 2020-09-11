<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model\QBXML;

use Magenest\QuickBooksDesktop\Helper\Configuration;
use Magento\Catalog\Model\Product as ProductModel;
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class Item extends QBXML
{
    /**
     * @var ProductModel
     */
    protected $_product;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;


    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * Item constructor.
     * @param ProductModel $product
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectManagerInterface $objectManager
     * @param QueueHelper $queueHelper
     */
    public function __construct(
        ProductModel $product,
        ScopeConfigInterface $scopeConfig,
        Configuration $configuration,
        ObjectManagerInterface $objectManager,
        QueueHelper $queueHelper
    ) {
        parent::__construct($configuration, $objectManager);
        $this->_product = $product;
        $this->_scopeConfig = $scopeConfig;
        $this->_queueHelper = $queueHelper;
        $this->_version = $this->_queueHelper->getQuickBooksVersion();
    }

    /**
     * Get XML using sync to QBD
     * @param $id
     * @return string
     */
    public function getXml($id)
    {
        $model = $this->_product->load($id);
        if (!$model->getId()) {
            return;
        }
        $qty = $model->getExtensionAttributes()->getStockItem()->getQty();
        $xml = $this->simpleXml($model->getSku(),'Name', 30);
        $price = $model->getData('price');

        $cost = $model->getData('cost');
        $finalPrice = $model->getData('final_price');

        if(!$cost){
            $cost = $price;
        }

        if(!$finalPrice){
            $finalPrice = $price;
        }

        $type = $model->getTypeId();

        $qty = $qty ? $qty : 0;

        if ($type == 'simple' || $type == 'virtual' || $type == 'giftcard' || $type == 'downloadable') {
            $xml .= $this->simpleXml($model->getName(), 'SalesDesc');
             $xml .= $this->simpleXml(round($finalPrice, 2), 'SalesPrice');
            $xml .= $this->multipleXml($this->getAccountName(), ['IncomeAccountRef','FullName']);
            $xml .= $this->simpleXml($model->getName(), 'PurchaseDesc');
            $xml .= $this->simpleXml(round($cost, 2), 'PurchaseCost');
            $xml .= $this->multipleXml($this->getAccountName('cogs'), ['COGSAccountRef','FullName']);
            $xml .= $this->multipleXml($this->getAccountName('asset'), ['AssetAccountRef','FullName']);
            $xml .= $this->simpleXml($qty, 'QuantityOnHand');
        } else {
            $xml .= '<SalesOrPurchase>';
            $xml .= $this->simpleXml($model->getName(), 'Desc');
            $xml .= $this->simpleXml(round($price, 2), 'Price');
            $xml .= $this->multipleXml($this->getAccountName('expense'), ['AccountRef', 'FullName']);
            $xml .= '</SalesOrPurchase>';
        }
        return $xml;
    }


    /**
     * @param string $type
     * @return mixed
     */
    protected function getAccountName($type = 'income')
    {
        $path = 'qbdesktop/account_setting/'.$type;

        return $this->_scopeConfig->getValue($path);
    }
}
