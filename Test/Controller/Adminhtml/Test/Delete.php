<?php


namespace Magenest\Test\Controller\Adminhtml\Test;


use Magento\Framework\App\ResponseInterface;
use Magento\Setup\Exception;

class Delete extends \Magento\Backend\App\Action
{
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Rsgitech_News::news_delete');
    }

    public function execute()
    {
       $id = $this->getRequest()->getParam('id');
       if($id) {
           try{
               $testModel = $this->_objectManager->create('Magenest\Test\Model\Test')->load($id);
               $testModel->delete();
               $this->messageManager->addSuccess('Xoa thanh cong');
               $this->_redirect('test/index/index');

           }catch (\Exception $e){
               $this->messageManager->addError($e->getMessage());
           }
       }
    }
}
