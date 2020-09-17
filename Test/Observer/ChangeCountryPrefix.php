<?php


namespace Magenest\Test\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ChangeCountryPrefix implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $test = $observer->getData('test');
        $country = $test->getCountry();
        if($country == "VN")
        {
            $test->setPrefix('Vietnam');
            $test->save();
        }
//        $exp_str = explode(" ", $country);
//        if(count($exp_str)<2){
//            $test->setPrefix($country);
//            $test->save();
//        }
//        else {
//            $test->setPrefix($exp_str[0]);
//            $test->save();
//        }
        return $this;
    }
}
