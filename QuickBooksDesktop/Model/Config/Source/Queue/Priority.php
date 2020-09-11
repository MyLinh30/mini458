<?php
/**
 * Created by PhpStorm.
 * User: Toan FrontEnd
 * Date: 7/30/2018
 * Time: 3:05 PM
 */

namespace Magenest\QuickBooksDesktop\Model\Config\Source\Queue;

class Priority
{
    const PRIORITY_PAYMENTMETHOD = 1;
    const PRIORITY_SHIPMETHOD = 1;
    const PRIORITY_CUSTOMER = 2;
    const PRIORITY_GUEST = 2;
    const PRIORITY_VENDOR = 2;
    const PRIORITY_PRODUCT = 3;
    const PRIORITY_SALESORDER = 4;
    const PRIORITY_INVOICE = 5;
    const PRIORITY_RECEIVEPAYMENT = 5;
    const PRIORITY_CREDITMEMO = 6;
}
