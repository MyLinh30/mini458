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
 * @time: 21/08/2020 09:00
 */

namespace Magenest\QuickBooksDesktop\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Configuration extends AbstractHelper
{
    const QUERY_PRODUCT_ACTION = 'qbdesktop/product_list/query_product_action';

    const SYNC_INTO_A_CUSTOMER = 'qbdesktop/qbd_setting/customer_setting/sync_to_a_customer';

    const SYNC_TO_CUSTOMER_NAME = 'qbdesktop/qbd_setting/customer_setting/customer_name';

    /**
     * Configuration constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    public function getQueryProductAction()
    {
        return $this->scopeConfig->getValue(self::QUERY_PRODUCT_ACTION);
    }

    public function isSyncIntoACustomer($scopeCode)
    {
        return $this->scopeConfig->isSetFlag(self::SYNC_INTO_A_CUSTOMER, 'store', $scopeCode);
    }

    public function getCustomerName($scopeCode)
    {
        return $this->scopeConfig->getValue(self::SYNC_TO_CUSTOMER_NAME, 'store', $scopeCode);
    }
}
