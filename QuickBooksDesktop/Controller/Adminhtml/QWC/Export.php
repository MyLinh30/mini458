<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\QWC;

use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;
use Magenest\QuickBooksDesktop\Model\CustomQueue;
use Magenest\QuickBooksDesktop\Model\CustomQueueFactory;
use Magenest\QuickBooksDesktop\WebConnector\Driver\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Operation;
use Magenest\QuickBooksDesktop\Model\Config\Source\Status;
use Magenest\QuickBooksDesktop\Model\Config\Source\Version;

/**
 * Class Export
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\QWC
 */
class Export extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magenest\QuickBooksDesktop\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_configInterface;

    /**
     * @var \Magenest\QuickBooksDesktop\Helper\CreateQueue
     */
    protected $queueHelper;

    protected $_customQueue;

    private $companyId = null;

    /**
     * Export constructor.
     * @param CustomQueueFactory $customQueueFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magenest\QuickBooksDesktop\Model\Config $config
     * @param \Magenest\QuickBooksDesktop\Helper\CreateQueue $queueHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
     */
    public function __construct(
        \Magenest\QuickBooksDesktop\Model\CustomQueueFactory $customQueueFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magenest\QuickBooksDesktop\Model\Config $config,
        \Magenest\QuickBooksDesktop\Helper\CreateQueue $queueHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
    ) {
        parent::__construct($context);
        $this->queueHelper = $queueHelper;
        $this->config = $config;
        $this->fileFactory = $fileFactory;
        $this->_configInterface = $configInterface;
        $this->_customQueue = $customQueueFactory;
    }

    /**
     * Export Varnish Configuration as .qwc
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $fileName = 'connect.qwc';
        $appName = 'Synchronization from Magento';
        $companyId = $this->queueHelper->getCompanyId();
        $checkType = $this->getRequest()->getParam('type');

        if ($checkType == TypeQuery::QUERY_COMPANY) {
            $fileName = 'company.qwc';
            $appName = 'Query Company';
        } elseif ($checkType == TypeQuery::QUERY_TAX) {
            $number = $this->_configInterface->getValue(
                'qbdesktop/qbd_setting/number_tax',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $maxRequest = 30;
            $check = ceil((float)$number / (float)$maxRequest);
            $version = $this->queueHelper->getQuickBooksVersion();
            if ($version != Version::VERSION_US) {
                $check = 1;
            }

            $this->_objectManager
                ->create('\Magenest\QuickBooksDesktop\Model\CustomQueue')->getCollection()
                ->addFieldToFilter('type', TypeQuery::QUERY_TAX)
                ->addFieldToFilter('company_id', $companyId)
                ->walk('delete');

            for ($i = 1; $i <= $check; $i++) {
                if ($i == 1) {
                    $operation = Operation::OPERATION_MOD; //start
                } else {
                    $operation = Operation::OPERATION_ADD; //continue
                }
                $data = [
                    'ticket_id' => rand(0, 1000000),
                    'company_id' => $companyId,
                    'status' => Status::STATUS_QUEUE,
                    'type' => TypeQuery::QUERY_TAX,
                    'operation' => $operation
                ];
                $model = $this->_objectManager->create('\Magenest\QuickBooksDesktop\Model\CustomQueue');
                $model->addData($data);
                $model->save();
            }

            $appName = 'Mapping Tax';
            $fileName = 'tax.qwc';
        } elseif ($checkType == TypeQuery::QUERY_PRODUCT) {
            $this->processQueryProduct();
            $appName = 'Mapping Product';
            $fileName = 'mapping-product.qwc';
        } elseif ($checkType == TypeQuery::QUERY_DISCONNECT) {
            $this->queueHelper->disconnectCompany();
            $this->_redirect('adminhtml/system_config/edit/section/qbdesktop');
        }
        $content = $this->config->getQWCFile($appName);
        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }

    protected function processQueryProduct()
    {
        $number = $this->_configInterface->getValue(Product::XML_PATH_NUMBER_OF_PRODUCT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $maxRequest = Product::QUEUE_DEFAULT_MAX_RETURN;
        $check = ceil((float)$number / (float)$maxRequest);
        $version = $this->queueHelper->getQuickBooksVersion();
        if ($version != Version::VERSION_US) {
            $check = 1;
        }

        $this->_customQueue->create()->getCollection()
            ->addFieldToFilter('type', TypeQuery::QUERY_PRODUCT)
            ->addFieldToFilter('company_id', $this->getCompanyId())
            ->walk('delete');

        $data = [];
        for ($i = 1; $i <= $check; $i++) {
            if ($i == 1) {
                $operation = CustomQueue::OPERATION_START; //start
            } else {
                $operation = CustomQueue::OPERATION_CONTINUE; //continue
            }
            $data[] = [
                'ticket_id' => null,
                'company_id' => $this->getCompanyId(),
                'status' => Status::STATUS_QUEUE,
                'type' => TypeQuery::QUERY_PRODUCT,
                'operation' => $operation
            ];
        }
        $this->_customQueue->create()->insertMultiple($data);
    }

    private function getCompanyId()
    {
        if ($this->companyId == null) {
            $this->companyId = $this->queueHelper->getCompanyId();
        }
        return $this->companyId;
    }
    /**
     * Always true
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return true;
    }
}
