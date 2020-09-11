<?php


namespace Magenest\Cybergame\Block\RoomExtraOption;


use Magento\Framework\View\Element\Template;

class BeAManager extends Template
{
    protected $customerSession;
    protected $eavConfig;
    protected $_resource;
    protected $productCollectionFactory;
    public function __construct(Template\Context $context,
                                \Magento\Customer\Model\Session $customerSession,
                                \Magento\Eav\Model\Config $eavConfig,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,

                                array $data = [])
    {
        $this->customerSession = $customerSession;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $data);
    }

    public function getUrlBeAManager()
    {
        if($this->getRequest()=='cybergame/cyber/index'){
            return true;
        }
        else return false;
    }
    public function getDataRoomExtraOption()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $joinConditions = 'e.entity_id = s.product_id';
        $collection->addAttributeToSelect('*');
        $collection->getSelect()->join(
            ['s' => 'room_extra_option'],
            $joinConditions,
            []
        )->columns('*');
        return $collection;
    }
    public function getIsCyberManager()
    {
        $customer = $this->customerSession->getCustomer();
        if(isset($customer['is_cyber_manager']) && $customer['is_cyber_manager']== "1")
        {
            return true;
        }
        return false;

    }

}