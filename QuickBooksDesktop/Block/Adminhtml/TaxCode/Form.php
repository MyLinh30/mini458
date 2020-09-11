<?php

namespace Magenest\QuickBooksDesktop\Block\Adminhtml\TaxCode;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class Form extends \Magento\Backend\Block\Template
{
    protected $registry;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }
}
