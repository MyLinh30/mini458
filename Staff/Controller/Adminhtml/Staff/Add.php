<?php


namespace Magenest\Staff\Controller\Adminhtml\Staff;


use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Add extends \Magento\Backend\App\Action
{

    public function execute()
    {
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $page;
        // TODO: Implement execute() method.
    }
}
