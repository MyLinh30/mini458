<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * qbd extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package qbd
 * @time: 28/07/2020 08:51
 */

namespace Magenest\QuickBooksDesktop\Model\Config\Source;

use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;

class ListTax implements \Magento\Framework\Option\ArrayInterface
{
    protected $_queueHelper;

    protected $_taxQBFactory;

    public function __construct(
        \Magenest\QuickBooksDesktop\Model\TaxFactory $taxQBFactory,
        QueueHelper $queueHelper
    ) {
        $this->_queueHelper = $queueHelper;
        $this->_taxQBFactory = $taxQBFactory;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $companyId = $this->_queueHelper->getCompanyId();
        $taxesQB = $this->_taxQBFactory->create()->getCollection()->addFieldToFilter('company_id', $companyId);
        $listTaxes = ['value' => '', 'label' => __('Choose Tax')];
        foreach ($taxesQB as $tax) {
            $listTaxes[] = [
                'value' => $tax->getTaxCode(),
                'label' => $tax->getTaxCode()
            ];
        }

        return $listTaxes;
    }
}