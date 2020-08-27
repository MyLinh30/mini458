<?php


namespace Magenest\Staff\Model\Config\Source;


class ShowStaffType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_options;

    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                [
                    'value'=>null,
                    'label'=>__('--Select Option--')
                ],
                [
                    'value'=> 1,
                    'label'=>__('lv1')
                ],
                [
                    'value'=> 2,
                    'label' => __('lv2')
                ],
                [
                    'value'=> 3,
                    'label' => __('means not staff')
                ]
            ];
        }
        return $this->_options;
        // TODO: Implement getAllOptions() method.
    }

}
