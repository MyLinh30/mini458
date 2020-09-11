<?php


namespace Magenest\QuickBooksDesktop\Model\Config\Source\Mapping;


class Type implements \Magento\Framework\Option\ArrayInterface
{

    const QUEUE_CUSTOMER = 1;
    const QUEUE_PRODUCT = 2;
    const QUEUE_SALESORDER = 3;
    const QUEUE_INVOICE = 4;
    const QUEUE_SALESTAXCODE = 5;
    const QUEUE_ITEMSALESTAX = 6;
    const QUEUE_PAYMENTMETHOD = 7;
    const QUEUE_CREDITMEMO = 8;
    const QUEUE_SHIPMETHOD = 9;
    const QUEUE_VENDOR = 10;
    const QUEUE_RECEIVEPAYMENT = 20;
    const QUEUE_GUEST = 11;

    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => 'QUEUE_CUSTOMER', 'value' => self::QUEUE_CUSTOMER];
        $options[] = ['label' => 'QUEUE_PRODUCT', 'value' => self::QUEUE_PRODUCT];
        $options[] = ['label' => 'QUEUE_SALESORDER', 'value' => self::QUEUE_SALESORDER];
        $options[] = ['label' => 'QUEUE_INVOICE', 'value' => self::QUEUE_INVOICE];
        $options[] = ['label' => 'QUEUE_SALESTAXCODE', 'value' => self::QUEUE_SALESTAXCODE];
        $options[] = ['label' => 'QUEUE_ITEMSALESTAX', 'value' => self::QUEUE_ITEMSALESTAX];
        $options[] = ['label' => 'QUEUE_PAYMENTMETHOD', 'value' => self::QUEUE_PAYMENTMETHOD];
        $options[] = ['label' => 'QUEUE_CREDITMEMO', 'value' => self::QUEUE_CREDITMEMO];
        $options[] = ['label' => 'QUEUE_SHIPMETHOD', 'value' => self::QUEUE_SHIPMETHOD];
        $options[] = ['label' => 'QUEUE_VENDOR', 'value' => self::QUEUE_VENDOR];
        $options[] = ['label' => 'QUEUE_RECEIVEPAYMENT', 'value' => self::QUEUE_RECEIVEPAYMENT];
        $options[] = ['label' => 'QUEUE_GUEST', 'value' => self::QUEUE_GUEST];
        return $options;
    }
}


