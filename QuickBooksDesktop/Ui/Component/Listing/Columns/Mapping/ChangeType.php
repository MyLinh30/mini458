<?php


namespace Magenest\QuickBooksDesktop\Ui\Component\Listing\Columns\Mapping;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ChangeType extends Column
{
    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, array $components = [], array $data = [])
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                switch ($item['type']){
                    case 1:  $item['type']=html_entity_decode("<p>QUEUE_CUSTOMER</p>");
                    break;
                    case 2 : $item['type']=html_entity_decode("<p>QUEUE_PRODUCT</p>");
                    break;
                    case 3 : $item['type']=html_entity_decode("<p>QUEUE_SALESORDER</p>");
                    break;
                    case 4 : $item['type']=html_entity_decode("<p>QUEUE_INVOICE</p>");
                    break;
                    case 5 : $item['type']=html_entity_decode("<p>QUEUE_SALESTAXCODE</p>");
                    break;
                    case 6: $item['type']=html_entity_decode("<p>QUEUE_ITEMSALESTAX</p>");
                    break;
                    case 7: $item['type']=html_entity_decode("<p>QUEUE_PAYMENTMETHOD</p>");
                    break;
                    case 8: $item['type']=html_entity_decode("<p>QUEUE_CREDITMEMO</p>");
                    break;
                    case 9: $item['type']=html_entity_decode("<p>QUEUE_SHIPMETHOD</p>");
                    break;
                    case 10: $item['type']=html_entity_decode("<p>QUEUE_VENDOR</p>");
                    break;
                    case 20: $item['type']=html_entity_decode("<p>QUEUE_RECEIVEPAYMENT</p>");
                    break;
                    case 11: $item['type']=html_entity_decode("<p>QUEUE_GUEST</p>");
                    break;
                }

            }
        }
        return $dataSource;
    }


}
