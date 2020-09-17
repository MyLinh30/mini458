<?php


namespace Magenest\Cybergame\Controller\Index;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
   public function __construct(Context $context,
                               \Magento\Framework\View\Result\PageFactory $pageFactory)
   {
       $this->_pageFactory = $pageFactory;
       parent::__construct($context);
   }

    public function execute()
    {
        $page = $this->_pageFactory->create();
        return $page;
        // TODO: Implement execute() method.
    }
}
