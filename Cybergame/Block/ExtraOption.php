<?php


namespace Magenest\Cybergame\Block;


use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\View\Element\Template;

class ExtraOption extends Template
{
    protected $productCollectionFactory;
    protected $_resource;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_resource = $resource;
        parent::__construct($context, $data);
    }

    public function getProductInfo()
    {
        $id = $this->getRequest()->getParam('id');
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToSelect('*')->addAttributeToFilter('entity_id', $id);
        $collection->getSelect()->join(
            array('second' => 'room_extra_option'),
            'e.entity_id = second.product_id',
            ['id', 'product_id', 'number_pc', 'address', 'food_price', 'drink_price']
        );
        return $collection->getItems();
    }
}
