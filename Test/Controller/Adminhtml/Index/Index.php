<?php


namespace Magenest\Test\Controller\Adminhtml\Index;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;

class Index extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;
    public function __construct(Action\Context $context,\Magento\Framework\View\Result\PageFactory $_resultPageFactory)
    {
        $this->_resultPageFactory = $_resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->_resultPageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('TEST'));
        return $page;
    }
}
