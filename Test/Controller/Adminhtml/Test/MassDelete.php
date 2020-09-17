<?php


namespace Magenest\Test\Controller\Adminhtml\Test;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends \Magento\Backend\App\Action
{
    protected $_filter;
    protected $collectionFactory;
    protected $resultFactory;
    public function __construct(Action\Context $context,
                                Filter $_filter,
                                \Magenest\Test\Model\ResourceModel\Test\CollectionFactory $collectionFactory,
                                \Magento\Framework\View\Result\PageFactory $resultFactory)
    {
        $this->collectionFactory = $collectionFactory;
        $this->_filter = $_filter;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->collectionFactory->create();
        $collection = $this->_filter->getCollection($result);
        $collectionSize = $collection->getSize();
        foreach ($collection as $items){
            $items->delete();
        }
        $this->messageManager->addSuccess(__('A total of %1 element(s) have been deleted.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('test/index/index');
    }
}
