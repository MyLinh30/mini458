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
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Creditmemo as CreditmemoModel;

/**
 * Class CreditMemo
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class CreditMemo extends QBXML
{
    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * @var CreditmemoModel
     */
    protected $_creditMemo;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $_productFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var MappingHelper
     */
    private $mappingHelper;

    /**
     * CreditMemo constructor.
     * @param CreditmemoModel $creditmemo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Mapping $map
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param ObjectManagerInterface $objectManager
     * @param QueueHelper $queueHelper
     */
    public function __construct(
        CreditmemoModel $creditmemo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Mapping $map,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        Configuration $configuration,
        ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        QueueHelper $queueHelper,
        MappingHelper $mappingHelper
    ) {
        parent::__construct($configuration, $objectManager);
        $this->_creditMemo = $creditmemo;
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
        /** @var \Magento\Sales\Model\Order\Creditmemo $model */
        $model = $this->_creditMemo->load($id);
        if (!$model->getId()) {
            return '';
        }
        /** @var \Magento\Sales\Model\Order $order */
        $order = $model->getOrder();
        if (!$order->getId()) {
            return '';
        }
        $billAddress = $model->getBillingAddress();
        $shipAddress = $model->getShippingAddress();

        $xml = $this->getCustomerXml($order);
        $date = date("Y-m-d", strtotime($model->getCreatedAt()));
        $xml .= $this->simpleXml($date, 'TxnDate');
        $xml .= $this->simpleXml($model->getIncrementId(), 'RefNumber', 11);
        $xml .= $billAddress ? $this->getAddress($billAddress) : '';
        $xml .= $shipAddress ? $this->getAddress($shipAddress, 'ship') : '';
        $shipMethod = strtok($order->getShippingMethod(), '_');

        if (!empty($shipMethod)) {
            $xml .= $this->multipleXml($shipMethod, ['ShipMethodRef', 'FullName'], 14);
        }

        $taxCode = $this->getTaxCodeTransaction($order);

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

        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($model->getAllItems() as $item) {
            $item_id = $order->getItemsCollection()->addFieldToFilter('sku', $item->getSku())->getLastItem()->getItemId();
            $xml .= $this->getOrderItem($item, $item_id);
        }

        if ($model->getShippingAmount() != 0) {
            $dataShipping =
                [
                    'type' => 'Shipping',
                    'desc' => $order->getShippingDescription(),
                    'rate' => $model->getShippingAmount(),
                    'tax_amount' => $model->getShippingTaxAmount(),
                    'txn_id' => null,
                    'taxcode' => $taxCode
                ];

            $xml .= $this->getOtherItem($dataShipping, 'CreditMemoLineAdd');
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
                    'rate' => $discount,
                    'tax_amount' => $model->getDiscountTaxCompensationAmount(),
                    'txn_id' => null,
                    'taxcode' => $taxCode
                ];
            $xml .= $this->getOtherItem($dataDiscount, 'CreditMemoLineAdd');
        }

        return $xml;
    }

    /**
     * Get Order Item XML
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item *
     * @return string
     */
    protected function getOrderItem($item, $item_id)
    {
        $price = $item->getPrice();
        $taxAmount = $item->getTaxAmount();
        $qty = $item->getQty();

        if ($item->getOrderItem()->getProductType() == 'bundle' || $item->getPrice() == 0) {
            return '';
        }

        if ($item->getOrderItem()->getParentItem() && $item->getOrderItem()->getParentItem()->getProductType() == 'configurable') {
            return '';
        }

        if ($item->getOrderItem()->getProductType() == 'configurable') {
            if (isset($item->getOrderItem()->getChildrenItems()[0])) {
                $item->setProductId($item->getOrderItem()->getChildrenItems()[0]->getProductId());
            }
        }

        $hasTax = false;
        $taxCode = $this->getTaxCodeItem($item_id);
        $xml = '<CreditMemoLineAdd>';
        $listId = $this->mappingHelper->searchListIdFromObject($item->getProductId(), Type::QUEUE_PRODUCT);
        if ($listId) {
            $xml .= $this->multipleXml($listId, ['ItemRef', 'ListID'], 30);
        } else {
            $xml .= $this->multipleXml($item->getSku(), ['ItemRef', 'FullName'], 30);
        }
        $xml .= $this->simpleXml($qty, 'Quantity');
        $xml .= $this->simpleXml(round($price, 2), 'Rate');

        if ($taxAmount != 0) {
            $hasTax = true;
        }
        $xml .= $this->getXmlTax($taxCode, $hasTax);
        $xml .= '</CreditMemoLineAdd>';

        return $xml;
    }
}
