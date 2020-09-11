<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Ui\Component\Listing\Columns\Queue;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class Actions
 * @package Magenest\QuickBooksDesktop\Ui\Component\Listing\Columns\Queue
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * UserActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');

            foreach ($dataSource['data']['items'] as &$item) {
                $path = null;
                $data = null;
                if ($item['type'] == 'Customer') {
                    $path = 'customer/index/edit';
                    $data = ['id' => $item['entity_id'], 'store' => $storeId];
                } elseif ($item['type'] == 'Product') {
                    $path = 'catalog/product/edit';
                    $data = ['id' => $item['entity_id'], 'store' => $storeId];
                } elseif (in_array($item['type'], ['SalesOrder'])) {
                    $path = 'sales/order/view';
                    $data = ['order_id' => $item['entity_id'], 'store' => $storeId];
                } elseif (in_array($item['type'], ['Invoice', 'ReceivePayment'])) {
                    $path = 'sales/invoice/view';
                    $data = ['invoice_id' => $item['entity_id'], 'store' => $storeId];
                } elseif (in_array($item['type'], ['CreditMemo'])) {
                    $path = 'sales/creditmemo/view';
                    $data = ['creditmemo_id' => $item['entity_id'], 'store' => $storeId];
                }
                if (@$path && @$data) {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            $path,
                            $data
                        ),
                        'label' => __('View'),
                        'hidden' => false,
                        'target' => 'blank'
                    ];
                }
            }
        }

        return $dataSource;
    }
}
