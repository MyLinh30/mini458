<?php


namespace Magenest\Staff\Controller\Staff;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Customer\Model\Session;

class Index extends \Magento\Framework\App\Action\Action
{

    private $resultPageFactory;
    private $customerSession;

    public function __construct(Context $context,
                                Session $customerSession,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage('Login required.');
            $this->_redirect('customer/account/login');
        };
        return $this->resultPageFactory->create();
    }
}
