<?php


namespace Magenest\Cybergame\Block\RoomExtraOption;



use Magento\Framework\View\Element\Template;

class EditCyberRoom extends Template
{
    protected $_pageFactory;
    protected $_resource;
    protected $_coreRegistry;
    protected $productCollectionFactory;
    public function __construct(Template\Context $context,
                                \Magento\Framework\View\Result\PageFactory $pageFactory,
                                \Magento\Framework\Registry $coreRegistry,
                                \Magento\Framework\App\ResourceConnection $Resource,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                array $data = [])
    {
        $this->_resource = $Resource;
        $this->_pageFactory = $pageFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    public function getRecordRoomExtraOptionEdit()
    {
        $id = $this->_coreRegistry->registry('editRecordId');
        $second_table_name = $this->_resource->getTableName('room_extra_option');
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToSelect('*')->addAttributeToFilter('entity_id',$id);
        $collection->getSelect()->join(
            array('second' => $second_table_name),
            'e.entity_id = second.product_id',
            ['id','product_id','number_pc','address','food_price','drink_price']
        );
       return $collection->getItems();
    }
}
