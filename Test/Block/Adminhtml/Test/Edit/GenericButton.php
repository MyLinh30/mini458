<?php


namespace Magenest\Test\Block\Adminhtml\Test\Edit;


use Magento\Backend\Block\Widget\Context;

class GenericButton
{
    protected $context;

    public function __construct(Context $context
    ) {
        $this->context = $context;
    }

    public function getId()
    {
        return $this->context->getRequest()->getParam('id');
    }
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
