<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * peruvianlink.com extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package peruvianlink.com
 * @time: 19/08/2020 13:41
 */

namespace Magenest\QuickBooksDesktop\Model\Config\Source;

class ProductQueryAction implements \Magento\Framework\Option\ArrayInterface
{
    const MAPPING_PRODUCT = 'mapping';

    const UPDATE_QTY = 'update_qty';

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Mapping Product'), 'value' => self::MAPPING_PRODUCT],
            ['label' => __('Update Quantity'), 'value' => self::UPDATE_QTY],
        ];
    }
}
