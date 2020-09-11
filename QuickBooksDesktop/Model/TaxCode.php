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
 * Class TaxCode
 * @package Magenest\QuickBooksDesktop\Model
 * @method int getId()
 * @method int getTaxId()
 * @method string getTaxTitle()
 * @method string getCode()

 */
class TaxCode extends AbstractModel
{
    /**
     * Initize
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksDesktop\Model\ResourceModel\TaxCode');
    }
}
