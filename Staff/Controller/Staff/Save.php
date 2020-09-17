<?php


namespace Magenest\Staff\Controller\Staff;



use Magenest\Staff\Model\StaffFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magenest\Staff\Model\ResourceModel\Staff\CollectionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Framework\App\Action\Action
{
    private $customerSession;
    private $staffFactory;
    private $staffCollectionFactory;
    private $resultJsonFactory;
    public function __construct(
        Context $context,
        Session $customerSession,
        StaffFactory $staffFactory,
        CollectionFactory $collectionFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->staffFactory = $staffFactory;
        $this->staffCollectionFactory = $collectionFactory;

        parent::__construct($context);
    }
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $customer = $this->customerSession->getCustomer();
        $customerId= $customer->getId();

        $this->saveStaffLevelAttribute($customer, $data['type']);
        $this->saveStaff($customerId, $data);

        $result = $this->resultJsonFactory->create();
        $result->setStatusHeader(200);
        $result->setData('Data saved.');
        return $result;
    }

    private function saveStaffLevelAttribute(\Magento\Customer\Model\Customer $customer, $type)
    {
        $customer->addData(['staff_type' => $type]);
        try {
            $customer->save();
        } catch (\Exception $e) {
            throwException($e);
        }
    }

    private function saveStaff($customerId, $data)
    {
        $staffModel = $this->staffFactory->create();
        $staffCollection = $this->staffCollectionFactory->create();
        $staffRecord = $staffCollection
            ->addFieldToFilter('customer_id', ['eq'=>$customerId])
            ->addFieldToSelect('id')
            ->toArray();

        switch ($data['typeString']) {
            case 'No staff': $data['type'] = 0;break;
            case 'Level 1': $data['type'] = 1;break;
            case  'Level 2': $data['type'] = 2;break;
        }

        $newData = [
            'customer_id' => $customerId,
            'nick_name' => $data['nickname'],
            'type' => $data['type'],
            'status' => '1'
        ];

        if ($staffRecord['totalRecords']>0) {
            $oldRecord = $this->staffFactory->create();
            $ordStaff = $oldRecord->load($staffRecord['items'][0]);
            $ordStaff->addData($newData);
            try {
                $ordStaff->save();
            } catch (\Exception $e) {
                throwException($e);
            }
        } else {
            $newRecord = $this->staffFactory->create();
            $newRecord->setData($newData);
            try {
                $newRecord->save();
            } catch (\Exception $e) {
                throwException($e);
            }
        }
    }
}
