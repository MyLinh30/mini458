<?php


namespace Magenest\Test\Ui\Component\Listing\Columns;




use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns;

class ChangeCountry extends Columns
{
    public function __construct(ContextInterface $context, array $components = [], array $data = [])
    {
        parent::__construct($context, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])){
            foreach ($dataSource['data']['items'] as &$item){
                if ($item['country']=='VN'){
                    $item['country'] = 'Vietnam';
                }
            }
        }
        return $dataSource;
    }


}
