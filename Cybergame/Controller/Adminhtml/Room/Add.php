<?php


namespace Magenest\Cybergame\Controller\Adminhtml\Room;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;

class Add extends \Magento\Backend\App\Action
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
        return $page;
        // TODO: Implement execute() method.
    }
}
