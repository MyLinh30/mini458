<?php


namespace Magenest\Staff\Model\ResourceModel\Staff;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public $_idFieldName = "id";
    protected function _construct()
    {
        $this->_init('Magenest\Staff\Model\Staff','Magenest\Staff\Model\ResourceModel\Staff');
        parent::_construct(); // TODO: Change the autogenerated stub
    }
}