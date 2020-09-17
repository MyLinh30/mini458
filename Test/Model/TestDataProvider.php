<?php


namespace Magenest\Test\Model;




class TestDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $_loadedData;
    public function __construct($name,
                                $primaryFieldName,
                                $requestFieldName,
                                \Magenest\Test\Model\ResourceModel\Test\CollectionFactory $testCollectionFactory,
                                array $meta = [],
                                array $data = [])
    {
        $this->collection = $testCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    public function getData()
    {
        if(isset($this->_loadedData)){
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $test) {
            $this->_loadedData[$test->getId()] = $test->getData();
        }
        return $this->_loadedData;
    }

}
