<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\WebConnector\Handlers;

use Magenest\QuickBooksDesktop\Helper\GenerateTicket;
use Magenest\QuickBooksDesktop\Model\Company as CompanyModel;
use Magenest\QuickBooksDesktop\Model\MappingFactory;
use Magenest\QuickBooksDesktop\Model\Ticket;
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\WebConnector;
use Magenest\QuickBooksDesktop\WebConnector\Receive\Response as ReceiveResponse;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;

/**
 * Class Company
 * @package Magenest\QuickBooksDesktop\WebConnector\Handlers
 */
class Company extends WebConnector\Handlers
{
    /**
     * @var CompanyModel
     */
    public $company;

    /**
     * Company constructor.
     * @param GenerateTicket $generateTicket
     * @param Ticket $ticket
     * @param CompanyModel $company
     * @param ReceiveResponse $receiveResponse
     * @param ObjectManagerInterface $objectManager
     * @param WebConnector\Driver\Company $driverCompany
     */
    public function __construct(
        GenerateTicket $generateTicket,
        Ticket $ticket,
        CompanyModel $company,
        QueueHelper $queueHelper,
        ReceiveResponse $receiveResponse,
        ObjectManagerInterface $objectManager,
        WebConnector\Driver\Company $driverCompany,
        MappingFactory $mappingFactory
    ) {
        parent::__construct(
            $generateTicket,
            $ticket,
            $receiveResponse,
            $objectManager,
            $mappingFactory,
            $queueHelper
        );
        $this->_driver = $driverCompany;
        $this->company = $company;
    }

    /**
     * @param $dataFromQWC
     */
    protected function processResponse($dataFromQWC)
    {
        $response = $this->getReceiveResponse();
        $response->setResponse($dataFromQWC);
        $result = $response->getValue();
        $companyName = $result['CompanyRet']['CompanyName'];
        $count = $this->company->getCollection()->addFieldToFilter('company_name', $companyName)->count();
        $info = [
            'company_name' => $companyName,
            'status' => 1
        ];

        $this->company->getCollection()->setDataToAll('status', 0);

        if ($count == 0) {
            $model = $this->company->setData($info);
            $model->save();
        } else {
            $model = $this->company->load($companyName, 'company_name');
            $model->addData($info)->save();
        }
    }
}
