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
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;
use Magenest\QuickBooksDesktop\Model\CustomQueue as CustomQueue;
use Magenest\QuickBooksDesktop\Model\MappingFactory;
use Magenest\QuickBooksDesktop\Model\Ticket;
use Magenest\QuickBooksDesktop\Model\TaxFactory;
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\WebConnector;
use Magenest\QuickBooksDesktop\WebConnector\Receive\Response as ReceiveResponse;
use \Magento\Store\Model\StoreManagerInterface;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;

/**
 * Class Tax
 * @package Magenest\QuickBooksDesktop\WebConnector\Handlers
 */
class Tax extends WebConnector\Handlers
{
    /**
     * @var CustomQueue
     */
    public $customQueue;

    /**
     * @var TaxFactory
     */
    public $taxFactory;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var customerAddressMapping
     */
    protected $customerAddressFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Customer constructor.
     * @param GenerateTicket $generateTicket
     * @param Ticket $ticket
     * @param CustomQueue $customQueue
     * @param ReceiveResponse $receiveResponse
     * @param ObjectManagerInterface $objectManager
     * @param WebConnector\Driver\Customer $driverCustomer
     * @param MappingFactory $mappingFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param TaxFactory $taxFactory
     * @param customerAddressMapping $customerAddressFactory
     */
    public function __construct(
        GenerateTicket $generateTicket,
        Ticket $ticket,
        CustomQueue $customQueue,
        ReceiveResponse $receiveResponse,
        ObjectManagerInterface $objectManager,
        WebConnector\Driver\Tax $driverTax,
        MappingFactory $mappingFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TaxFactory $taxFactory,
        QueueHelper $queueHelper
    ) {
        parent::__construct(
            $generateTicket,
            $ticket,
            $receiveResponse,
            $objectManager,
            $mappingFactory,
            $queueHelper
        );
        $this->_driver = $driverTax;
        $this->customQueue = $customQueue;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->taxFactory = $taxFactory;
    }

    /**
     * @param $dataFromQWC
     */
    protected function processResponse($dataFromQWC)
    {
        try {
            $websites = $this->_storeManager->getWebsites();
            $websiteIds = [];
            foreach ($websites as $website) {
                $websiteIds [] = $website->getId();
            }
            $response = $this->getReceiveResponse();
            $response->setResponse($dataFromQWC);
            $result = $response->getValue();

            $iteratorId = $response->getIteratorId();

            $companyId = $this->_queueHelper->getCompanyId();

            \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info("ComeONnnn123123   " . print_r($result, true) . "\n");

            if (!empty($result['ItemSalesTaxRet'])) {
                $data = [
                    'status' => Status::STATUS_SUCCESS,
                    'iterator_id' => $iteratorId
                ];
                $this->taxFactory->create()->getCollection()->addFieldToFilter('company_id', $companyId)->walk('delete');
            } else {
                $data = [
                    'status' => Status::STATUS_FAIL,
                    'iterator_id' => $iteratorId
                ];
            }

            $version = $this->_queueHelper->getQuickBooksVersion();
            if ($version == Version::VERSION_US) {
                $action = 'ItemSalesTaxRet';
            } else {
                $action = 'SalesTaxCodeRet';
            }

            $this->_driver->getCurrentQueue()->addData($data)->save();
            if (isset($result[$action])) {
                if (isset($result['ItemSalesTaxRet']['ListID'])) {
                    $taxcode = $result['ItemSalesTaxRet']['Name'];
                    $listID = $result['ItemSalesTaxRet']['ListID'];
                    $editSequence = $result['ItemSalesTaxRet']['EditSequence'];
                    $dataTax = [
                        'tax_code' => $taxcode,
                        'list_id' => $listID,
                        'edit_sequence' => $editSequence,
                        'company_id' => $companyId
                    ];
                    $this->saveTax($dataTax);
                } else {
                    foreach ($result[$action] as $child) {
                        $taxcode = $child['Name'];
                        $listID = $child['ListID'];
                        $editSequence = $child['EditSequence'];
                        $dataTax = [
                            'tax_code' => $taxcode,
                            'list_id' => $listID,
                            'edit_sequence' => $editSequence,
                            'company_id' => $companyId
                        ];
                        $this->saveTax($dataTax);
                    }
                }
            }
            $this->check();
        } catch (\Exception $exception) {
            $this->_queueHelper->writeDebug($exception->getMessage());
        }
    }

    public function saveTax($data)
    {
        $check = $this->taxFactory->create()->getCollection()->addFieldToFilter('list_id', $data['list_id'])->getLastItem();
        if ($check->getId()) {
            $check->addData($data)->save();
        } else {
            $model = $this->taxFactory->create();
            $model->addData($data)->save();
        }
    }


    /**
     * check request
     */
    public function check()
    {
        $count = $this->_driver->getCollection()->getSize();
        if ($count == 0) {
            $companyId = $this->_queueHelper->getCompanyId();
            $model = $this->_objectManager->create('\Magenest\QuickBooksDesktop\Model\CustomQueue')->getCollection()
                ->addFieldToFilter('type', TypeQuery::QUERY_TAX)
                ->addFieldToFilter('company_id', $companyId);
            foreach ($model as $queue) {
                $queue->setStatus(Status::STATUS_QUEUE);
                $queue->save();
            }
        }
    }
}
