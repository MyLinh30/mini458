<?php


namespace Magenest\Staff\Controller\Staff;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;

class SearchNickName extends \Magento\Framework\App\Action\Action
{
    protected $staffCollectionFactory;
    protected $resultJsonFactory;
    public function __construct(Context $context,
                                JsonFactory $resultJsonFactory,
                                \Magenest\Staff\Model\ResourceModel\Staff\CollectionFactory $staffCollectionFactory)
    {
        $this->staffCollectionFactory = $staffCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $staffNickName = $this->getRequest()->getPostValue('name');
        $staffCollection = $this->staffCollectionFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $result = $staffCollection->addFieldToSelect('nick_name')->addFieldToFilter('nick_name', array('like' => "%${staffNickName}%"))->toArray();
        $data = array($result);
        return $resultJson->setData($data);
    }
}
