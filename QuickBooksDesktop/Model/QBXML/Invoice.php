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
use Magenest\QuickBooksDesktop\Helper\MappingHelper;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;
use Magento\Sales\Model\Order\Invoice as InvoiceModel;
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magenest\QuickBooksDesktop\Model\Connector;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class Invoice extends QBXML
{
    /**
     * @var InvoiceModel
     */
    protected $_invoice;

    /**
     * @var Mapping
     */
    public $_map;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var MappingHelper
     */
    private $mappingHelper;


    /**
     * Invoice constructor.
     * @param InvoiceModel $invoice
     * @param Connector $connector
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Mapping $map
     * @param ObjectManagerInterface $objectManager
     * @param QueueHelper $queueHelper
     */
    public function __construct(
        InvoiceModel $invoice,
        Connector $connector,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Mapping $map,
        Configuration $configuration,
        ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        QueueHelper $queueHelper,
        MappingHelper $mappingHelper
    )
    {
        parent::__construct($configuration, $objectManager);
        $this->connector = $connector;
        $this->mappingHelper =$mappingHelper;
        $this->_invoice = $invoice;
        $this->scopeConfig = $scopeConfig;
        $this->_map = $map;
        $this->_queueHelper = $queueHelper;
        $this->_version = $this->_queueHelper->getQuickBooksVersion();
        $this->customerFactory = $customerFactory;
    }

    /**
     * Get XML using sync to QBD
     *
     * @param $id
     * @return string
     */
    public function getXml($id)
    {
        $invoice = $this->_invoice->load($id);
        if (!$invoice->getId()) {
            return '';
        }
        $model = $invoice->getOrder();
        if (!$model->getId()) {
            return '';
        }
        $billAddress = $model->getBillingAddress();
        $shipAddress = $model->getShippingAddress();
        $xml = $this->getCustomerXml($model);
        $date = date("Y-m-d", strtotime($invoice->getCreatedAt()));
        $xml .= $this->simpleXml($date,'TxnDate');
        $xml .= $this->simpleXml($invoice->getIncrementId(), 'RefNumber', 11);
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

        $companyId = $this->_queueHelper->getCompanyId();

        $txn_id = $this->_map->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', Type::QUEUE_SALESORDER)
            ->addFieldToFilter('entity_id', $model->getId())
            ->getLastItem()
            ->getData('list_id');

        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        foreach ($invoice->getAllItems() as $item) {
            $item_id = $model->getItemsCollection()->addFieldToFilter('sku',$item->getSku())->getLastItem()->getItemId();
            $xml .= $this->getOrderItem($item, $txn_id, $item_id);
        }

        if ($invoice->getShippingAmount() != 0) {
            $dataShipping =
                [
                    'type' => 'Shipping',
                    'desc' => $model->getShippingDescription(),
                    'rate' => $invoice->getShippingAmount(),
                    'tax_amount' => $invoice->getShippingTaxAmount(),
                    'txn_id' => $txn_id,
                    'taxcode' => $taxCode
                ];

            $xml .= $this->getOtherItem($dataShipping, 'InvoiceLineAdd');
        }

        if ($invoice->getDiscountAmount() != 0) {
            $discount = $invoice->getDiscountAmount();
            if ($discount < 0) {
                $discount = 0 - $discount;
            }
            $dataDiscount =
                [
                    'type' => 'Discount',
                    'desc' => $model->getDiscountDescription(),
                    'rate' => $discount,
                    'tax_amount' => $invoice->getDiscountTaxCompensationAmount(),
                    'txn_id' => $txn_id,
                    'taxcode' => $taxCode
                ];

            $xml .= $this->getOtherItem($dataDiscount, 'InvoiceLineAdd');
        }

        return $xml;
    }

    /**
     * Get Order Item XML
     *
     * @param \Magento\Sales\Model\Order\Invoice\Item $item *
     * @return string
     */
    protected function getOrderItem($item, $txn_id, $item_id)
    {
        $price = $item->getPrice();
        $taxAmount = $item->getTaxAmount();
        $qty = $item->getQty();

        if($item->getOrderItem()->getProductType() == 'bundle' || $item->getPrice() == 0){
            return '';
        }

        if($item->getOrderItem()->getParentItem() && $item->getOrderItem()->getParentItem()->getProductType() == 'configurable'){
            return '';
        }

        if($item->getOrderItem()->getProductType() == 'configurable'){
            if(isset($item->getOrderItem()->getChildrenItems()[0])){
                $item->setProductId($item->getOrderItem()->getChildrenItems()[0]->getProductId());
            }
        }

        $_productFactory = $this->objectManager->create(\Magento\Catalog\Model\ProductFactory::class)->create();
        $product = $_productFactory->load($item->getProductId());
        $sku = $product->getSku();

        // if ($sku) {
                $hasTax = false;
                $taxCode = $this->getTaxCodeItem($item_id);
                $txnLineId = $this->getTnxLineId($txn_id, $sku);

                $xml = '<InvoiceLineAdd>';

                if (!$txnLineId) {
                    $listId = $this->mappingHelper->searchListIdFromObject($product->getEntityId(),Type::QUEUE_PRODUCT);
                    if ($listId){
                        $xml .= $this->multipleXml($listId, ['ItemRef', 'ListID'], 30);
                    }else{
                        $xml .= $this->multipleXml($item->getSku(), ['ItemRef', 'FullName'], 30);
                    }
                }
                $xml .= $this->simpleXml($qty, 'Quantity');
                $xml .= $this->simpleXml(round($price, 2), 'Rate');

                if ($taxAmount != 0) {
                    $hasTax = true;
                }
                $xml .= $this->getXmlTax($taxCode, $hasTax);

                if ($txnLineId) {
                    $xml .= "<LinkToTxn>";
                    $xml .= $this->simpleXml($txn_id, 'TxnID');
                    $xml .= $this->simpleXml($txnLineId, 'TxnLineID');
                    $xml .= "</LinkToTxn>";
                }
                $xml .= '</InvoiceLineAdd>';
        // } else {
        //     $xml = '<InvoiceLineAdd>';
        //     $xml .= $this->multipleXml('Not Found Product From M2: '. $sku, ['ItemRef', 'ListID']);
        //     $xml .= '</InvoiceLineAdd>';
        //     return $xml;
        // }

        return $xml;
    }
}
