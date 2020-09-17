<?php


namespace Magenest\Test\Model\ResourceModel;


class Test extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('magenest_test_addess','id');
        // TODO: Implement _construct() method.
    }
}
