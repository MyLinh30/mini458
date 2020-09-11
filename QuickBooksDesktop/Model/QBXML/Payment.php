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
use Magenest\QuickBooksDesktop\Model\Mapping;
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Customer
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class Payment extends QBXML
{

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Mapping
     */
    protected $_map;

    /**
     * Payment constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectManagerInterface $objectManager
     * @param Mapping $map
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Configuration $configuration,
        ObjectManagerInterface $objectManager,
        Mapping $map
    ) {
        parent::__construct($configuration, $objectManager);
        $this->_scopeConfig = $scopeConfig;
        $this->_map = $map;
    }

    /**
     * Get XML using sync to QBD
     * @param $code
     * @return string
     */
    public function getXml($code)
    {
        $code = substr($code, 0, 31);
        $xml = $this->simpleXml($code, 'Name');
        $xml .= $this->simpleXml('ECheck', 'PaymentMethodType');

        return $xml;
    }
}
