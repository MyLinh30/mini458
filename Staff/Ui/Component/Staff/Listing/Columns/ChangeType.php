<?php


namespace Magenest\Staff\Ui\Component\Staff\Listing\Columns;


use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns;

class ChangeType extends Columns
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
                    $item['status'] = 'approved';
                }else{
                    $item['status'] = 'pending';
                }
            }
        }
        return $dataSource;
    }
}
