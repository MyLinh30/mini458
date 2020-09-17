<?php


namespace Magenest\Staff\Ui\Component\Staff\Listing\Columns;


use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns;

class ChangeStatus extends Columns
{
    public function __construct(ContextInterface $context, array $components = [], array $data = [])
    {
        parent::__construct($context, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])){
            foreach ($dataSource['data']['items'] as &$item){
                if ($item['type']==1){
                    $item['type'] = 'lv1';
                }else if ($item['type']==2){
                    $item['type'] = 'lv2';
                }else {
                    $item['type'] ='means not staff';
                }
            }
        }
        return $dataSource;

    }

}
