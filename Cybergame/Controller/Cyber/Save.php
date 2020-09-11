<?php


namespace Magenest\Cybergame\Controller\Cyber;


use Magenest\Cybergame\Model\RoomExtraOption;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Save extends \Magento\Framework\App\Action\Action
{

    protected $_roomExtraOptionFactoy;
    protected $_request;
    protected $_coreRegistry;
    public function __construct(Context $context,
                                \Magenest\Cybergame\Model\RoomExtraOptionFactoy $roomExtraOptionFactoy,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Framework\Registry $coreRegistry)
    {
        $this->_roomExtraOptionFactoy = $roomExtraOptionFactoy;
        $this->_coreRegistry = $coreRegistry;
        $this->_request = $request;
        parent::__construct($context);
    }

    public function execute()
    {
        // $id = $this->_coreRegistry->registry('editRecordId');
        $data = (array)$this->getRequest()->getPostValue();
        $testModel = $this->_roomExtraOptionFactoy->create();
        $testModel->load($data['id']);
        $testModel->addData($data);
        $testModel->save();
        $this->messageManager->addSuccess(__('Save test succesfully'));
        $this->_redirect('test/test/index');
    }

}
