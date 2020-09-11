<?php


namespace Magenest\Cybergame\Controller\Ajax;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class CheckAccountName extends \Magento\Framework\App\Action\Action
{
    protected $gamerAccountListCollectionFactory;
    protected $resultPageFactory;
    protected $resultJsonFactory;
    public function __construct(Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory,
                                \Magenest\Cybergame\Model\ResourceModel\GamerAccountList\CollectionFactory $gamerAccountListCollectionFactory,
                                \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->gamerAccountListCollectionFactory= $gamerAccountListCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $account_name = $this->getRequest()->getPost('name');
        $gamer_account = $this->gamerAccountListCollectionFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $gamer_account_collection = $gamer_account->getData();
        foreach ($gamer_account_collection as $item){
            if($item['account_name']==$account_name)
            {
                $data = array('Account was exist in our system. You are buying hour for this acocunt');
            }
            else {
                $data= array('We will create new account for you. Default password = 1. You should change the password after login');
            }
        }
        return $resultJson->setData($data);
    }
}
