<?php


namespace Magenest\Staff\Model\ResourceModel;


class Staff extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
{
    $this->_init('magenest_staff','id');
    // TODO: Implement _construct() method.
}
}
