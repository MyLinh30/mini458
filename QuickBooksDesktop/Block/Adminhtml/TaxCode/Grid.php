<?php

namespace Magenest\QuickBooksDesktop\Block\Adminhtml\TaxCode;

use Magento\Backend\Block\Widget\Grid as WidgetGrid;

/**
 * Class Grid
 * @package Magenest\QuickBooksDesktop\Block\Adminhtml\TaxCode
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_taxRateCollection;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Tax\Model\Calculation\Rate $taxRateCollection,
        array $data = []
    ) {
        $this->_taxRateCollection = $taxRateCollection;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $backendHelper, $data);
        $this->setEmptyText(__('No Results Found'));
    }


    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('tax_calculation_rate_id', [
            'header' => __('Tax Rate ID'),
            'align' => 'left',
            'width' => '10px',
            'index' => 'tax_calculation_rate_id'
        ]);

        $this->addColumn('code', [
            'header' => __('Tax Title'),
            'align' => 'left',
            'index' => 'code'
        ]);

        $this->addColumn('rate', [
            'header' => __('Rate'),
            'align' => 'right',
            'index' => 'rate'
        ]);

        $this->addColumn('code_mapping', [
            'header' => __('Code Mapping'),
            'index' => 'code_mapping',
            'align' => 'left',
            'renderer' => \Magenest\QuickBooksDesktop\Block\Adminhtml\TaxCode\Renderer\Code::class
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Initialize the Result collection
     *
     * @return WidgetGrid
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->_taxRateCollection->getCollection());
        return parent::_prepareCollection();
    }
}
