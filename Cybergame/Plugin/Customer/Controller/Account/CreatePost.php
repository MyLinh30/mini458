<?php


namespace Magenest\Cybergame\Plugin\Customer\Controller\Account;


class CreatePost
{
    public function afterExecute(\Magento\Customer\Controller\Account\CreatePost $subject)
    {
        $data = $subject->getRequest()->getParams();
        if(isset($data['is_cyber_manager']) && $data['is_cyber_manager']== "1")
        {

        }
        return $subject;
    }



}
