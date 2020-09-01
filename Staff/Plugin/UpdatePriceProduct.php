<?php


namespace Magenest\Staff\Plugin;


use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\Render;
use Magento\Catalog\Block\Product\AbstractProduct;


class UpdatePriceProduct
{
    private $customerSession;
    private $eavConfig;

    public function __construct(Session $customerSession,
                                Config $eavConfig)
    {
        $this->customerSession = $customerSession;
        $this->eavConfig = $eavConfig;
    }

    public function afterGetProductPrice(\Magento\Catalog\Block\Product\ListProduct $product, $result)
    {
        $customer =  $this->customerSession->getCustomer();
        $valueStaffType = $customer->getData('staff_type');
        $labelstaffType = $this->eavConfig
            ->getAttribute('customer', 'staff_type')
            ->getSource()
            ->getOptionText($valueStaffType);

        $exp_result = explode('</span>', $result);
        $exp_result[0]= $exp_result[0].' ('.$labelstaffType->getText().')';
        $imp_result = implode("</span>", $exp_result);
        return $imp_result;


    }
}
