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
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use Magenest\QuickBooksDesktop\Helper\MappingHelper;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order as OrderModel;

/**
 * Class SalesOrder
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class SalesOrder extends QBXML
{
    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * @var OrderModel
     */
    protected $_order;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var MappingHelper
     */
    private $mappingHelper;

    /**
     * SalesOrder constructor.
     * @param OrderModel $order
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Mapping $map
     * @param ProductFactory $productFactory
     * @param \Magento\Framework\App\ObjectManager $objectManager
     * @param QueueHelper $queueHelper
     */
    public function __construct(
        OrderModel $order,
        Configuration $configuration,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Mapping $map,
        ProductFactory $productFactory,
        ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        QueueHelper $queueHelper,
        MappingHelper $mappingHelper
    ) {
        parent::__construct($configuration, $objectManager);
        $this->_order = $order;
        $this->scopeConfig = $scopeConfig;
        $this->_map = $map;
        $this->_productFactory = $productFactory;
        $this->_queueHelper = $queueHelper;
        $this->_version = $this->_queueHelper->getQuickBooksVersion();
        $this->customerFactory = $customerFactory;
        $this->mappingHelper = $mappingHelper;
    }

    /**
     * Get XML using sync to QBD
     *
     * @param $id
     * @return string
     */
    public function getXml($id)
    {
        /** @var \Magento\Sales\Model\Order $model */
        $model = $this->_order->load($id);
        if (!$model->getId()) {
            return '';
        }
        $billAddress = $model->getBillingAddress();
        $shipAddress = $model->getShippingAddress();

        $xml = $this->getCustomerXml($model);
        $date = date("Y-m-d", strtotime($model->getCreatedAt()));
        $xml .= $this->simpleXml($date, 'TxnDate');
        $xml .= $this->simpleXml($model->getIncrementId(), 'RefNumber', 11);
        $xml .= $billAddress ? $this->getAddress($billAddress) : '';
        $xml .= $shipAddress ? $this->getAddress($shipAddress, 'ship') : '';
        $shipMethod = strtok($model->getShippingMethod(), '_');

        if (!empty($model->getShippingMethod())) {
            $xml .= $this->multipleXml($shipMethod, ['ShipMethodRef', 'FullName'], 14);
        }

        $taxCode = $this->getTaxCodeTransaction($model);
        if ($taxCode != null && $this->_version == Version::VERSION_US) {
            $xml .= $this->multipleXml($taxCode, ['ItemSalesTaxRef', 'FullName'], 30);
        }

        // set default tax
        if ($taxCode == null) {
            $taxDefault = $this->scopeConfig->getValue(QueueHelper::TAX_DEFAULT);
            if (!empty($taxDefault)) {
                $xml .= $this->multipleXml($taxDefault, ['ItemSalesTaxRef', 'FullName'], 30);
            }
        }

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($model->getAllItems() as $item) {
            $xml .= $this->getOrderItem($item);
        }

        if ($model->getShippingAmount() != 0) {
            $dataShipping =
                [
                    'type' => 'Shipping',
                    'desc' => $model->getShippingDescription(),
                    'rate' => round($model->getShippingAmount(), 2),
                    'tax_amount' => round($model->getShippingTaxAmount(), 2),
                    'txn_id' => null,
                    'taxcode' => $taxCode
                ];

            $xml .= $this->getOtherItem($dataShipping, 'SalesOrderLineAdd');
        }

        if ($model->getDiscountAmount() != 0) {
            $discount = $model->getDiscountAmount();
            if ($discount < 0) {
                $discount = 0 - $discount;
            }
            $dataDiscount =
                [
                    'type' => 'Discount',
                    'desc' => $model->getDiscountDescription(),
                    'rate' => round($discount, 2),
                    'tax_amount' => round($model->getDiscountTaxCompensationAmount(), 2),
                    'txn_id' => null,
                    'taxcode' => $taxCode
                ];
            $xml .= $this->getOtherItem($dataDiscount, 'SalesOrderLineAdd');
        }

        return $xml;
    }

    /**
     * Get Order Item XML
     *
     * @param \Magento\Sales\Model\Order\Item $item *
     * @return string
     */
    protected function getOrderItem($item)
    {
        if ($item->getProductType() == 'bundle' || $item->getPrice() == 0) {
            return '';
        }

        if ($item->getParentItem() && $item->getParentItem()->getProductType() == 'configurable') {
            return '';
        }

        if ($item->getProductType() == 'configurable') {
            if (isset($item->getChildrenItems()[0])) {
                $item->setProductId($item->getChildrenItems()[0]->getProductId());
            }
        }

        $price = $item->getPrice();
        $taxAmount = $item->getTaxAmount();
        $qty = $item->getQtyOrdered();

        $product = $this->_productFactory->create()->load($item->getProductId());
        if ($id = $product->getId()) {
            $listId = $this->mappingHelper->searchListIdFromObject($id, Type::QUEUE_PRODUCT);
        }

        // if ($sku) {
        $xml = '<SalesOrderLineAdd>';
        if ($listId){
            $xml .= $this->multipleXml($listId, ['ItemRef', 'ListID'], 30);
        }else{
            $xml .= $this->multipleXml($item->getSku(), ['ItemRef', 'FullName'], 30);
        }
        $hasTax = false;
        $taxCode = $this->getTaxCodeItem($item->getItemId());
        $xml .= $this->simpleXml($qty, 'Quantity');
        $xml .= $this->simpleXml(round($price, 2), 'Rate');

        if ($taxAmount != 0) {
            $hasTax = true;
        }
        $xml .= $this->getXmlTax($taxCode, $hasTax);
        $xml .= '</SalesOrderLineAdd>';

        // } else {
        //     $xml = '<SalesOrderLineAdd>';
        //     $xml .= $this->multipleXml('Not Found Product From M2: ' . $item->getProductId(), ['ItemRef', 'ListID']);
        //     $xml .= '</SalesOrderLineAdd>';
        //     return $xml;
        // }

        return $xml;
    }
}
