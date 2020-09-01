<?php


namespace Magenest\Staff\Controller\Staff;


use Magento\Framework\App\Action\Context;

abstract class DynamicController extends \Magento\Framework\App\Action\Action
{
    protected $pageFactory;
    public function __construct(Context $context,\Magento\Framework\View\Result\PageFactory $pageFactory )
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }
    /**
     * This action render random number for each request
     */

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->setHeader('Cache-Control','no-store,no-cache,must-revalidate',true);
        return $page;
        // TODO: Implement execute() method.
    }
}
