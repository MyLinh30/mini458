<?php


namespace Magenest\Test\Block\Test;

use Magento\Framework\View\Element\Template;

class EditTest extends Template
{
    protected $_pageFactory;
    protected $_testFactory;
    protected $_coreRegistry;
    public function __construct(Template\Context $context,
                                \Magento\Framework\View\Result\PageFactory $pageFactory,
                                \Magento\Framework\Registry $coreRegistry,
                                \Magenest\Test\Model\TestFactory $testFactory,
                                array $data = [])
    {
        $this->_pageFactory = $pageFactory;
        $this->_testFactory = $testFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    public function getRecordEdit()
    {
        $id = $this->_coreRegistry->registry('editRecordId');
        $testModel = $this->_testFactory->create();
        $result = $testModel->load($id);
        $result = $result->getData();
        return $result;

    }

}
