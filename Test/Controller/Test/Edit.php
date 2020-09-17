<?php


namespace Magenest\Test\Controller\Test;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Framework\App\Action\Action
{

    protected $_pageFactory;
    protected $_request;
    protected $_coreRegistry;
    public function __construct(Context $context,
                                \Magento\Framework\View\Result\PageFactory $pageFactory,
                                \Magento\Framework\Registry $coreRegistry,
                                \Magento\Framework\App\Request\Http $request)
    {
        $this->_pageFactory = $pageFactory;
        $this->_request = $request;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->_request->getParam('id');
        $this->_coreRegistry->register('editRecordId',$id);
        return $this->_pageFactory->create();
    }
}
