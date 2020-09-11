<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Company
 * @package Magenest\QuickBooksDesktop\Model
 * @method int getCompanyId()
 * @method string getCompanyName()
 * @method boolean getStatus()
 * @method Use setStatus(string $status)
 */
class Company extends AbstractModel
{

    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksDesktop\Model\ResourceModel\Company');
    }
}
