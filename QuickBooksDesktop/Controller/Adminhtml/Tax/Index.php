<?php
/**
 * Created by PhpStorm.
 * User: namnt
 * Date: 6/30/18
 * Time: 1:23 PM
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Tax;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_QuickBooksDesktop::manage_tax');
        $resultPage->getConfig()->getTitle()->prepend(__('Mapping Tax Code'));

        return $resultPage;
    }
}
