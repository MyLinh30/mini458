<?php


namespace Magenest\Cybergame\Model;


class RoomExtraOption extends \Magento\Framework\Model\AbstractModel
{
    public function __construct(Context $context,
                                \Magento\Framework\Registry $registry,
                                ResourceModel\AbstractResource $resource = null,
                                \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
                                array $data = [])
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    protected function _construct()
    {
        $this->_init('Magenest\Cybergame\Model\ResourceModel\RoomExtraOption');
        parent::_construct(); // TODO: Change the autogenerated stub
    }

}
