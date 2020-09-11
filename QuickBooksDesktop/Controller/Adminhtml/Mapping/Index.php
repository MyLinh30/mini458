<?php


namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Mapping;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(Action\Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('Manage Mapping'));
        return $page;
        // TODO: Implement execute() method.
    }
}
