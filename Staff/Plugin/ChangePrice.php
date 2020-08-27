<?php


namespace Magenest\Staff\Plugin;


use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;

class ChangePrice
{
    private $customerSession;
    private $eavConfig;

    public function __construct(Session $customerSession,Config $eavConfig)
    {
        $this->customerSession = $customerSession;
        $this->eavConfig = $eavConfig;
    }

    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        $staffTypeId = $this->customerSession->getCustomer()->getData('staff_type');
        $staffType = $this->eavConfig
            ->getAttribute('customer', 'staff_type')
            ->getSource()
            ->getOptionText($staffTypeId);

        $result = (string)$result." (${staffType})";
        return $result;
    }
}
