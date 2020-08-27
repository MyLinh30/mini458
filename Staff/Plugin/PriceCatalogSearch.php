<?php


namespace Magenest\Staff\Plugin;


use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;

class PriceCatalogSearch
{
    protected $customerSession;
    protected $eavConfig;
    public function __construct(Session $customerSession,Config $eavConfig)
    {
        $this->customerSession = $customerSession;
        $this->eavConfig = $eavConfig;

    }
    public function afterGetProductPrice(\Magento\CatalogSearch\Block\SearchResult\ListProduct $listProduct,$result){

//        $customer = $this->customerSession->getCustomer()->getData('staff_type');
//        $attribute = $this->eavConfig->getAttribute('customer','staff_type');
//        $optionText = $attribute->getSource()->getOptionText($customer);
//        return (string)$result. "${optionText}";
         return (string)$result."lv1";
    }
}
