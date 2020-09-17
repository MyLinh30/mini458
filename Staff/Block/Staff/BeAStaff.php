<?php


namespace Magenest\Staff\Block\Staff;


use Magento\Framework\View\Element\Template;
use Magenest\Staff\Model\ResourceModel\Staff\Collection;
use Magenest\Staff\Model\ResourceModel\Staff\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;


class BeAStaff extends Template
{
    protected $eavConfig;
    protected $customerSession;
    protected $staffCollectionFactory;
    public function __construct(Template\Context $context,
                                Session $customerSession,
                                CollectionFactory $staffCollectionFactory,
                                Config $eavConfig,
                                array $data = [])
    {
        $this->eavConfig = $eavConfig;
        $this->customerSession = $customerSession;
        $this->staffCollectionFactory = $staffCollectionFactory;
        parent::__construct($context, $data);
    }
    public function getUrlBeAStaff()
    {
        if ($this->getRequest()=='/staff/staff/index') {
            return true;
        }
        return false;
    }
    public function getCustomerLogin()
    {
        $customer_id = $this->customerSession->getCustomer()->getId();
        $staffCollection = $this->staffCollectionFactory->create();
        $staff = $staffCollection->addFieldToFilter('customer_id', array('in'=> $customer_id))->getData();
        switch ($staff[0]['type']) {
            case 1:
                $result[0]['type'] = 'lv1';
                break;
            case 2:
                $result[0]['type'] = 'lv2';
                break;
            case 3:
                $result[0]['type'] = 'means not staff';
                break;
        }
        return ['nick_name'=> $staff[0]['nick_name'],'staff_type'=>$staff[0]['type']];
   }

   public function getOptions()
   {
       $options = $this->eavConfig->getAttribute('customer', 'staff_type')->getOptions();
       foreach ($options as $option) {
           $allOption[] =
               [
               'label' => $option->getLabel(),
               'value' => $option->getValue()
               ];
       }
       unset($options[0]);
       return $options;
    }
}
