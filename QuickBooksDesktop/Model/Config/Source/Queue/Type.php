<?php
/**
 * Created by PhpStorm.
 * User: Toan FrontEnd
 * Date: 7/30/2018
 * Time: 3:05 PM
 */

namespace Magenest\QuickBooksDesktop\Model\Config\Source\Queue;

class Type
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
}
