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

           $exp_result = explode('</span>',$result);
           $exp_result[0] = $exp_result[0].' (lv1)';
           $show2 = implode("</span>",$exp_result);
           return $show2;



//            $valueStaffType = $this->customerSession->getCustomer()->getData('staff_type');
//            $labelstaffType = $this->eavConfig
//                ->getAttribute('customer', 'staff_type')
//                ->getSource()
//                ->getOptionText($valueStaffType);

//            return  $result.("<span class='price'>g${labelstaffType}</span>");

//            return "<div class='price-box price-final_price' data-role='priceBox' data-product-id='4' data-price-box='product-id-4'>
//                <span class='price-container price-final_price&#x20;tax&#x20;weee'>
//                <span  id='product-price-4'  data-price-amount='20' data-price-type='finalPrice' class='price-wrapper'>
//                        $result.concat(<span class='price'>ggg${labelstaffType})
//                        </span>
//                </span>
//                </span>
//                </div>";

//            $priceRender = $rendererPool->createPriceRender($priceCode, $saleableItem, $useArguments);
//            return $priceRender->toHtml();







    }
}
