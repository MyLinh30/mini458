<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Model;

use Magenest\QuickBooksDesktop\Helper\Configuration;
use \Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;

/**
 * Class QBXML
 * @package Magenest\QuickBooksDesktop\Model
 */
abstract class QBXML
{

    protected $_version;

    protected $objectManager;

    protected $_configHelper;

    public function __construct(
        Configuration $configuration,
        ObjectManagerInterface $objectManager
    )
    {
        $this->objectManager = $objectManager;
        $this->_configHelper = $configuration;
    }

    /**
     * @param $id
     * @return string
     */
    public function getXml($id)
    {
        // TODO
        return '';
    }

    /**
     * @param \Magento\Sales\Model\AbstractModel $model
     * @return string
     */
    protected function getCustomerXml(\Magento\Sales\Model\AbstractModel $model)
    {
        $storeCode = $model->getStore()->getCode();
        if ($this->_configHelper->isSyncIntoACustomer($storeCode)) {
            $customerReceive = $this->_configHelper->getCustomerName($storeCode);
        } else {
            $billAddress = $model->getBillingAddress();

            $customerId = $model->getCustomerId();
            if ($customerId) {
                $customer = $this->customerFactory->create()->load($customerId);
                if ($customer->getEntityId()) {
                    $customerReceive = $customer->getName() . ' ' . $customerId;
                } else {
                    $customerReceive = $model->getCustomerName() . ' ' . $customerId;
                }
            } else {
                $customerReceive = $billAddress->getName() . ' ' . $model->getIncrementId();
            }
        }

        return $this->multipleXml($customerReceive, ['CustomerRef', 'FullName'], 40);
    }

    /** @var \Magento\Sales\Model\Order $order */
    public function getTaxCodeTransaction($order)
    {
        $taxCode = null;
        /** @var  \Magento\Sales\Model\Order\TaxFactory $taxOrder */
        $taxCodeOrder = $this->objectManager->create(\Magento\Sales\Model\Order\TaxFactory::class)->create()
            ->getCollection()
            ->addFieldToFilter("order_id", $order->getId())
            ->getLastItem()
            ->getCode();
        $modelTax = $this->getModelTax($taxCodeOrder);
        if ($modelTax && !empty($modelTax->getData())) {
            $taxCode = $modelTax->getTaxCode();
        } elseif ($order->getTaxAmount() != 0) {
            /** @var  \Magento\Tax\Model\Calculation\Rate $taxAlls */
            $taxAlls = $this->objectManager->create(\Magento\Tax\Model\Calculation\Rate::class)->getCollection()->getItems();
            foreach ($taxAlls as $taxAll) {
                if ($taxAll->getRate() * $order->getBaseSubtotal() / 100 == $order->getTaxAmount()
                    || round($taxAll->getRate() * $order->getBaseSubtotal() / 100, 2) == $order->getTaxAmount()) {
                    $modelTax = $this->getModelTax($taxAll->getCode());
                    if ($modelTax && !empty($modelTax->getData())) {
                        $taxCode = $modelTax->getTaxCode();
                        break;
                    }
                }
            }
        }
        return $taxCode;
    }

    /**
     * Create Tax
     */
    public function getTaxCodeItem($itemId)
    {
        /** @var \Magento\Sales\Model\Order\Tax\Item $taxItem */
        $taxItem = $this->objectManager->create(\Magento\Sales\Model\Order\Tax\Item::class)->load($itemId, 'item_id');
        $taxCode = null;
        if ($taxItem) {
            /** @var \Magento\Sales\Model\Order\Tax $taxCodeOrder */
            $taxCodeOrder = $this->objectManager->create(\Magento\Sales\Model\Order\Tax::class)->load($taxItem->getTaxId())->getCode();
            /** @var \Magento\Sales\Model\Order\Tax $modelTax */
            $modelTax = $this->getModelTax($taxCodeOrder);
            if ($modelTax && !empty($modelTax->getData())) {
                $taxCode = $modelTax->getTaxCode();
            }
        }
        return $taxCode;
    }

    /**
     * @param $taxCodeOrder
     * @return TaxFactory
     */
    public function getModelTax($taxCodeOrder)
    {
        /** @var \Magenest\QuickBooksDesktop\Model\TaxCodeFactory $qbTaxMapping */
        $qbTaxMapping = $this->objectManager->create(\Magenest\QuickBooksDesktop\Model\TaxCodeFactory::class)->create()
            ->getCollection()
            ->addFieldToFilter("tax_title", $taxCodeOrder)
            ->getLastItem();
        /** @var \Magenest\QuickBooksDesktop\Model\TaxFactory $modelTax */
        $qbTaxCode = $this->objectManager->create(\Magenest\QuickBooksDesktop\Model\TaxFactory::class)
            ->create()->load($qbTaxMapping->getCode());
        return $qbTaxCode;
    }

    public function getXmlTax($code, $hasTax)
    {
        $version = $this->_version;
        if ($hasTax) {
            if ($version == Version::VERSION_US) {
                $xml = $this->multipleXml('Tax', ['SalesTaxCodeRef', 'FullName'], 3);
            } elseif ($code) {
                $xml = $this->multipleXml($code, ['SalesTaxCodeRef', 'FullName'], 3);
            }
        } else {
            if ($version == Version::VERSION_US) {
                $xml = $this->multipleXml('Non', ['SalesTaxCodeRef', 'FullName'], 3);
            } elseif ($code) {
                $xml = $this->multipleXml('E', ['SalesTaxCodeRef', 'FullName'], 3);
            }
        }
        return $xml;
    }

    /**
     * @param $address
     * @param string $type
     * @return string
     */
    protected function getAddress($address, $type = 'bill')
    {
        if (!$address) {
            return '';
        }

        $country = $this->objectManager->create(\Magento\Directory\Model\Country::class)->loadByCode($address->getCountryId());

        $xml = $type == 'bill' ? '<BillAddress>' : '<ShipAddress>';
        $xml .= $this->simpleXml($address->getName(), 'Addr1', 40);
        $xml .= $this->simpleXml($address->getStreetLine(1), 'Addr2', 40);
        $xml .= $this->simpleXml($address->getStreetLine(2), 'Addr3', 40);
        $xml .= $this->simpleXml($address->getCity(), 'City', 30);
        $xml .= $this->simpleXml($address->getRegion(), 'State', 20);
        $xml .= $this->simpleXml($address->getPostcode(), 'PostalCode', 13);
        $xml .= $this->simpleXml($country->getName(), 'Country', 30);
        $xml .= $type == 'bill' ? '</BillAddress>' : '</ShipAddress>';

        return $xml;
    }

    /**
     * Get Other Item XML
     *
     * @param \Magento\Sales\Model\Order\Invoice\Item $item *
     * @return string
     */
    protected function getOtherItem($data, $tag)
    {
        $xml = "<$tag>";

        $txnLineId = null;
        if ($data['txn_id'] !== null) {
            $txnLineId = $this->getTnxLineId($data['txn_id'], $data['type']);
        }
        if (!$txnLineId) {
            $xml .= $this->multipleXml($data['type'], ['ItemRef', 'FullName'], 30);
        }
        $xml .= $this->simpleXml($data['desc'], 'Desc');
        $xml .= $this->simpleXml($data['rate'], 'Rate');

        if ($data['tax_amount'] > 0) {
            $xml .= $this->getXmlTax($data['taxcode'], true);
        } else {
            $xml .= $this->getXmlTax($data['taxcode'], false);
        }

        if ($txnLineId) {
            $xml .= "<LinkToTxn>";
            $xml .= $this->simpleXml($data['txn_id'], 'TxnID');
            $xml .= $this->simpleXml($txnLineId, 'TxnLineID');
            $xml .= "</LinkToTxn>";
        }

        $xml .= "</$tag>";
        return $xml;
    }

    protected function getTnxLineId($txnId, $sku)
    {
        $companyId = $this->objectManager->create(\Magenest\QuickBooksDesktop\Helper\CreateQueue::class)->getCompanyId();
        $result = $this->objectManager->create(\Magenest\QuickBooksDesktop\Model\ItemSalesOrderFactory::class)->create()->getCollection()
            ->addFieldToFilter('company_id', $companyId)
            ->addFieldToFilter('list_id_order', $txnId)
            ->addFieldToFilter('sku', $sku);

        if($result->count() != 1){
            return null;
        }
        $result = $result->getData();
        return @$result['txn_line_id'];
    }

    public function simpleXml($value, $tag, $length = 0)
    {
        if ($value !== '' && $value !== null) {
            $value = $this->validateXML($value, $length);
            return "<$tag>$value</$tag>";
        } else {
            return '';
        }
    }

    public function multipleXml($value, array $tags, $length = 0)
    {
        $xml = '';
        if ($value !== '' && $value !== null) {
            $value = $this->validateXML($value, $length);
            foreach ($tags as $tag) {
                $xml .= "<$tag>";
            }
            $xml .= "$value";
            $tags = array_reverse($tags);
            foreach ($tags as $tag) {
                $xml .= "</$tag>";
            }
        }
        return $xml;
    }

    /**
     * @param $value
     * @param int $length
     * @return bool|string
     */
    public function validateXML($value, $length = 0)
    {
        if ((int)$length > 0) {
            $value = substr($this->convertVNtoEN($value), 0, $length);
        } else {
            $value = $this->convertVNtoEN($value);
        }
		$value = htmlentities($value, null, 'utf-8');
		$value = str_replace("&nbsp;", "", $value);
		$value = html_entity_decode($value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);
        return $value;
    }

    protected function convertVNtoEN($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);

        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);

        $str = preg_replace("/(„|†|‡|‰|‹|‘|’|“|”|™|›|¡|¢|£|¤|¥|¦|§|¨|©|ª|«|¬|­|®|¯|°|±|²|³|´|µ|¶|·|¸)/", ' ', $str);
        $str = preg_replace("/(¹|º|»|¼|½|¾|¿|À|Á|Â|Ã|Ä|Å|Æ|Ç|È|É|Ê|Ë|Ì|Í|Î|Ï|Ð|Ñ|Ò|Ó|Ô|Õ|Ö|×|Ø|Ù|Ú|Û|Ü|Ý|Þ)/", ' ', $str);
        $str = preg_replace("/(ß|à|á|â|ã|ä|å|æ|ç|è|é|ê|ë|ì|í|î|ï|ð|ñ|ò|ó|ô|õ|ö|÷|ø|ù|ú|û|ü|ý|þ|ÿ|ƒ|Α|Β|Γ|Δ)/", ' ', $str);
        $str = preg_replace("/(Ε|Ζ|Η|Θ|Ι|Κ|Λ|Μ|Ν|Ξ|Ο|Π|Ρ|Σ|Τ|Υ|Φ|Χ|Ψ|Ω|α|β|γ|δ|ε|ζ|η|θ|ι|κ|λ|μ|ν|ξ|ο|π|ρ|ς)/", ' ', $str);
        $str = preg_replace("/(σ|τ|υ|φ|χ|ψ|ω|ϑ|ϒ|ϖ|•|…|′|″|‾|ℑ|℘|ℜ|ℵ|←|↑|→|↓|↔|↵|⇐|⇑|⇒|⇓|⇔|∀|∂|∃|∅|∇|∈|∉|∋)/", ' ', $str);
        $str = preg_replace("/(∏|∑|−|∗|√|∝|∞|∠|∧|∨|∩|∪|∫|∴|∼|≅|≈|≠|≡|≤|≥|⊂|⊃|⊄|⊆|⊇|⊕|⊗|⊥|⋅|⌈|⌉|⌊|⌋|〈|〉|◊|♠|♣|♥|♦)/", ' ', $str);

        $cyr = [
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
            'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
            'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'
        ];
        $lat = [
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
            'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya',
            'A', 'B', 'V', 'G', 'D', 'E', 'Io', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P',
            'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A', 'I', 'Y', 'e', 'Yu', 'Ya'
        ];
        $str = str_replace($cyr, $lat, $str);

        return $this->removeSpecialChracter($str);
    }

    protected function removeSpecialChracter($value)
    {
        return str_replace(['&', '”', '\'', '<', '>', '"', ','], ['&#38;', '&#34;', '&#39;', '&lt;', '&gt;', '&#34;', '&#44;'], $value);
    }

}
