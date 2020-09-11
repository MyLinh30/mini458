<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Helper\Result;

use Magenest\QuickBooksDesktop\Helper\Result;
use Magento\Framework\App\ProductMetadata;

/**
 * Class Authenticate
 * @package Magenest\QuickBooksDesktop\Model\Result
 */
class ServerVersion extends Result
{
    /**
     * @var string
     */
    protected $serverVersionResult;

    /**
     * ServerVersion constructor.
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     */
    public function __construct(ProductMetadata $productMetadata)
    {
        $magentoVersion = $productMetadata->getVersion();
        $magentoEdition = $productMetadata->getEdition();
        $this->serverVersionResult = 'Magento ' . $magentoEdition . ' ver.' . $magentoVersion;
    }
}
