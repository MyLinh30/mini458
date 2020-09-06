<?php


namespace Magenest\Test\Block\Test;




use Magento\Framework\View\Element\Template;

class MyTestAddress extends Template
{
    protected $testCollectionFactory;
    protected $_filesystem;
    public function __construct(Template\Context $context,
                                \Magenest\Test\Model\ResourceModel\Test\CollectionFactory $testCollectionFactory,
                                array $data = [])
    {
        $this->testCollectionFactory = $testCollectionFactory;
        parent::__construct($context, $data);
    }
    public function getUrlMyTestAddress(){
        if($this->getRequest()=='/test/test/index'){
            return true;
        }
        return false;
    }
    public function getDataTest()
    {
        $collection= $this->testCollectionFactory->create();
        //...
        $collection->addFieldToSelect('*');
        return $collection;
        //...
        //        $test = $collection->getCollection();
        //        return $test;
    }
}
