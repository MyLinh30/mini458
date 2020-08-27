<?php


namespace Magenest\Staff\Observer;


use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Api\Data\CustomerInterface;

class UpdateMagenestStaff implements ObserverInterface
{
    protected $logger;
    protected $staffFactory;
    public function __construct(\Psr\Log\LoggerInterface $logger,
                                \Magenest\Staff\Model\StaffFactory $staffFactory)
    {

        $this->logger = $logger;
        $this->staffFactory = $staffFactory;
    }
    public function execute(Observer $observer)
    {
        $staffModel = $this->staffFactory->create();
        $customer = $observer->getData('customer');
        $staffTypeId = $customer->getCustomAttribute('staff_type')->getValue();
        $data = [
            'customer_id' => $customer->getId(),
            'nick_name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
            'type' => $staffTypeId,
            'status' => 2,
        ];
        $staffModel->setData($data);
        $staffModel->save();
        return;
    }

}
