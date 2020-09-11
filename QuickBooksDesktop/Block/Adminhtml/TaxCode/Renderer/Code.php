<?php

namespace Magenest\QuickBooksDesktop\Block\Adminhtml\TaxCode\Renderer;

use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;

/**
 * Class Code
 * @package Magenest\QuickBooksDesktop\Block\Adminhtml\TaxCode\Renderer
 */
class Code extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_taxCodeFactory;

    protected $_taxQBFactory;


    /**
     * Website constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Block\Context $context,
        \Magenest\QuickBooksDesktop\Model\TaxCodeFactory $taxCodeFactory,
        \Magenest\QuickBooksDesktop\Model\TaxFactory $taxQBFactory,
        QueueHelper $queueHelper,
        array $data = []
    ) {
        $this->_taxQBFactory = $taxQBFactory;
        $this->_taxCodeFactory = $taxCodeFactory;
        $this->_storeManager = $storeManager;
        $this->_queueHelper = $queueHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render the grid cell value
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $taxRateId = $row->getData('tax_calculation_rate_id');
        $companyId = $this->_queueHelper->getCompanyId();
        $taxsQB = $this->_taxQBFactory->create()->getCollection()->addFieldToFilter('company_id', $companyId);
        $taxRate = $this->_taxCodeFactory->create()->getCollection()
            ->addFieldToFilter("tax_id", $taxRateId);
        if ($taxRate->count() > 0) {
            $value = $taxRate->getFirstItem()->getCode();
        } else {
            $value = "";
        }
        $hidden = '<input type="hidden"'
            . 'name="tax[' . $row->getData('tax_calculation_rate_id') . '][title]"'
            . ' value="' . $row->getCode() . '">';
        $input = '<select name="tax[' . $row->getData('tax_calculation_rate_id') . '][code]"'
            . ' class=" select admin__control-select">';
        $options = [];
        foreach ($taxsQB as $tax) {
            if ($tax->getId() == $value) {
                $options[] = '<option value="' . $tax->getId() . '" selected="selected">'
                    . $tax->getTaxCode() . '</option>';
            } else {
                $options[] = '<option value="' . $tax->getId() . '">' . $tax->getTaxCode()
                    . '</option>';
            }
        }
        $options = implode("", $options);
        $input .= $options . '</select>';

        return $hidden . $input;
    }
}
