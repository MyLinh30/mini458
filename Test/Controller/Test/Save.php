<?php


namespace Magenest\Test\Controller\Test;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $_testFactory;
    protected $_request;
    protected $_coreRegistry;
    public function __construct(Context $context,
                                \Magenest\Test\Model\TestFactory $testFactory,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Framework\Registry $coreRegistry)
    {
        $this->_testFactory = $testFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_request = $request;
        parent::__construct($context);
    }

    public function execute()
    {
       // $id = $this->_coreRegistry->registry('editRecordId');
        $data = (array)$this->getRequest()->getPostValue();
        $testModel = $this->_testFactory->create();
        $testModel->load($data['id']);
        $testModel->addData($data);
        $testModel->save();
        $this->messageManager->addSuccess(__('Save test succesfully'));
        $this->_redirect('test/test/index');
    }

}
