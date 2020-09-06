<?php


namespace Magenest\Test\Controller\Index;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    public function __construct(Context $context,\Magento\Framework\View\Result\PageFactory $_resultPageFactory)
    {
        $this->_resultPageFactory = $_resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->_resultPageFactory->create();
        return $page;
        // TODO: Implement execute() method.
    }
}
