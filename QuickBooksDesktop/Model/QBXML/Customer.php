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
use Magento\Customer\Model\Customer as CustomerModel;
use Magenest\QuickBooksDesktop\Model\QBXML;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class Customer extends QBXML
{
    /**
     * @var CustomerModel
     */
    protected $_customer;

    /**
     * Customer constructor.
     * @param CustomerModel $customer
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        CustomerModel $customer,
        Configuration $configuration,
        ObjectManagerInterface $objectManager
    ) {
        $this->_customer = $customer;
        parent::__construct($configuration, $objectManager);
    }

    /**
     * Get XML using sync to QBD
     *
     * @param int $id
     * @return string
     */
    public function getXml($id)
    {
        /** @var \Magento\Customer\Model\Customer $model */
        $model = $this->_customer->load($id);
        $billAddress = $model->getDefaultBillingAddress();
        $shipAddress = $model->getDefaultShippingAddress();

        $xml = $this->simpleXml($model->getName() . ' ' . $id, 'Name', 40);
        $xml .= $billAddress ? $this->simpleXml($billAddress->getCompany(), 'CompanyName', 40) : '';
        $xml .= $this->simpleXml($model->getFirstname(), 'FirstName', 25);
        $xml .= $this->simpleXml($model->getLastname(), 'LastName', 25);
        $xml .= $this->getAddress($billAddress);
        $xml .= $this->getAddress($shipAddress, 'ship');
        $xml .= $billAddress ? $this->simpleXml($billAddress->getTelephone(), 'Phone') : '';
        $xml .= $this->simpleXml($model->getEmail(), 'Email');

        return $xml;
    }
}
