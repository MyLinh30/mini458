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
use Magenest\QuickBooksDesktop\Model\QBXML;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class Company
 *
 * @package Magenest\QuickBooksDesktop\Model\QBXML
 */
class Company extends QBXML
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Company constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Configuration $configuration,
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct($configuration, $objectManager);
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Get XML using sync to QBD
     *
     * @return string
     */
    public function getXml($id)
    {
        return '';
    }
}
