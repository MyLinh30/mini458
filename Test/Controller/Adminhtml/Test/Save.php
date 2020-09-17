<?php


namespace Magenest\Test\Controller\Adminhtml\Test;


use Magento\Backend\App\Action;


class Save extends \Magento\Backend\App\Action
{
   public function __construct(Action\Context $context)
   {
       parent::__construct($context);
   }

    public function execute()
    {
        $data = (array)$this->getRequest()->getPost();
        $testModel = $this->_objectManager->create('Magenest\Test\Model\Test');
        $testModel->setData($data);
        $testModel->save();

        $this->_eventManager->dispatch('test_country_save_after',['test'=>$testModel]);

        $this->messageManager->addSuccess(__('Save test succesfully'));
        $this->_redirect('test/index/index');
    }
}
