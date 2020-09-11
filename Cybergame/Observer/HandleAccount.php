<?php


namespace Magenest\Cybergame\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class HandleAccount implements ObserverInterface
{
    protected $_gamerAccountListFactory;
    protected $_gamerAccountListCollectionFactory;

    public function __construct(\Magenest\Cybergame\Model\GamerAccountListFactory $gamerAccountListFactory,
                                \Magenest\Cybergame\Model\ResourceModel\GamerAccountList\CollectionFactory $gamerAccountListCollectionFactory)
    {
       $this->_gamerAccountListFactory= $gamerAccountListFactory;
       $this->_gamerAccountListCollectionFactory = $gamerAccountListCollectionFactory;
    }

    public function execute(Observer $observer)
    {
        $model = $this->_gamerAccountListFactory->create();
        $collection = $this->_gamerAccountListCollectionFactory->create();
        $array = $observer->getEvent()->getOrder()->getData();
        foreach ($array['items'] as $item) {
            $data = $item->getData();
            $accountName = $data["product_options"]['info_buyRequest']['account_name'];
            $isAccount = $collection->addFieldToFilter('account_name', $accountName);
            if(empty($isAccount->getData())){
                $model->setData('product_id', $item['product_id']);
                $model->setData('account_name', $accountName);
                $model->setData('password', 1);
                $model->setData('hour', $item['qty_ordered']);
                $model->save();
            }
            else{
                foreach ($collection->getData() as $account) {
                    //Compare account name with account_name in table in database
                    if ($account['account_name'] !== $accountName || $account['product_id']!==$item['product_id']) {
                        $model->setData('product_id', $item['product_id']);
                        $model->setData('account_name', $accountName);
                        $model->setData('password', 1);
                        $model->setData('hour', $item['qty_ordered']);
                        $model->save();
                    } else {
                        $collection->addFieldToFilter('account_name', $accountName);
                        foreach($collection->getData() as $value){
                            $model->load($value['id']);
                            $currentHour = $model->getData('hour');
                            $model->setData('hour', $currentHour + $item['qty_ordered']);
                            $model->save();
                        }
                    }
                }
            }
        }
    }
}
