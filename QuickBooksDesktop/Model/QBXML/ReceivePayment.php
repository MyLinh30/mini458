<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Model\QBXML;

use Magenest\QuickBooksDesktop\Helper\Configuration;
use Magento\Sales\Model\Order\Invoice as InvoiceModel;
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magenest\QuickBooksDesktop\Model\Connector;
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class ReceivePayment extends QBXML
{
    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Invoice constructor.
     * @param InvoiceModel $invoice
     * @param Connector $connector
     */
    public function __construct(
        InvoiceModel $invoice,
        Connector $connector,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Mapping $map,
        Configuration $configuration,
        ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        QueueHelper $queueHelper
    )
    {
        parent::__construct($configuration, $objectManager);
        $this->connector = $connector;
        $this->_invoice = $invoice;
        $this->scopeConfig = $scopeConfig;
        $this->_map = $map;
        $this->_queueHelper = $queueHelper;
        $this->_version = $this->_queueHelper->getQuickBooksVersion();
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param $id
     * @return string
     */
    public function getXml($id)
    {
        $model = $this->_invoice->load($id);
        if (!$model->getId()) {
            return '';
        }
        $order = $model->getOrder();
        if (!$order->getId()) {
            return '';
        }

        $companyId = $this->_queueHelper->getCompanyId();
        $xml = $this->getCustomerXml($order);
        $date = date("Y-m-d", strtotime($model->getCreatedAt()));
        $xml .= $this->simpleXml($date,'TxnDate');
        $xml .= $this->simpleXml($model->getIncrementId(), 'RefNumber', 11);

        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        $xml .= $this->simpleXml(str_replace(',', '', number_format($model->getBaseGrandTotal(), 2)), 'TotalAmount');

        if ($paymentMethod) {
            $xml .= $this->multipleXml($paymentMethod, ['PaymentMethodRef', 'FullName'], 30);
        }

        $txnid = $this->_map->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('type', Type::QUEUE_INVOICE)
            ->addFieldToFilter('entity_id', $id)
            ->getLastItem()
            ->getData('list_id');

        if (!empty($txnid)) {
            $xml .= '<AppliedToTxnAdd>';
            $xml .= '<TxnID useMacro="MACROTYPE">' . $txnid . '</TxnID>';
            $xml .= $this->simpleXml(str_replace(',', '', number_format($model->getBaseGrandTotal(), 2)), 'PaymentAmount');
            $xml .= '</AppliedToTxnAdd>';
        } else {
            $xml .= '<AppliedToTxnAdd>';
            $xml .= '<TxnID useMacro="MACROTYPE">Not Found Txn Id Invoice</TxnID>';
            $xml .= '</AppliedToTxnAdd>';
        }

        return $xml;
    }
}
