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
use Magento\Sales\Model\Order as OrderModel;
use Magenest\QuickBooksDesktop\Model\QBXML;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class Guest extends QBXML
{
    /**
     * @var OrderModel
     */
    protected $_order;

    /**
     * Guest constructor.
     * @param OrderModel $order
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        OrderModel $order,
        Configuration $configuration,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct($configuration, $objectManager);
        $this->_order = $order;
    }

    /**
     * Get XML using sync to QBD
     *
     * @param  int $id
     * @return string
     */
    public function getXml($id)
    {
        /** @var \Magento\Sales\Model\Order $model */
        $model = $this->_order->load($id);
        $billAddress = $model->getBillingAddress();
        $shipAddress = $model->getShippingAddress();

        $xml = $this->simpleXml($billAddress->getName().' '. $model->getIncrementId(), 'Name', 40);
        $xml .= $billAddress ? $this->simpleXml($billAddress->getCompany(), 'CompanyName', 40) : '';
        $xml .= $this->simpleXml($model->getCustomerFirstname(), 'FirstName', 25);
        $xml .= $this->simpleXml($model->getCustomerLastname(), 'LastName', 25);
        $xml .= $this->getAddress($billAddress);
        $xml .= $this->getAddress($shipAddress, 'ship');
        $xml .= $billAddress ? $this->simpleXml($billAddress->getTelephone(), 'Phone') : '';
        $xml .= $this->simpleXml($model->getCustomerEmail(), 'Email');

        return $xml;
    }
}
