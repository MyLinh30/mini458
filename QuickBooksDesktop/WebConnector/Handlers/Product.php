<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * peruvianlink.com extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package peruvianlink.com
 * @time: 19/08/2020 08:10
 */

namespace Magenest\QuickBooksDesktop\WebConnector\Handlers;

use Magenest\QuickBooksDesktop\Helper\Configuration;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;
use Magenest\QuickBooksDesktop\Helper\GenerateTicket;
use Magenest\QuickBooksDesktop\Helper\ProductDataHelper;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\Type;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;
use Magenest\QuickBooksDesktop\Model\CustomQueue;
use Magenest\QuickBooksDesktop\Model\CustomQueueFactory as CustomQueueModel;
use Magenest\QuickBooksDesktop\Model\MappingFactory;
use Magenest\QuickBooksDesktop\Model\Ticket;
use Magenest\QuickBooksDesktop\WebConnector\Handlers;
use Magenest\QuickBooksDesktop\WebConnector\Receive\Response as ReceiveResponse;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;

class Product extends Handlers
{
    /**
     * @var CustomQueueModel
     */
    protected $_customQueue;

    /**
     * @var Configuration
     */
    protected $_configHelper;

    /**
     * @var ProductDataHelper
     */
    protected $_productDataHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Product constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param ProductDataHelper $productDataHelper
     * @param Configuration $configuration
     * @param \Magenest\QuickBooksDesktop\WebConnector\Driver\Product $product
     * @param CustomQueueModel $customQueueFactory
     * @param GenerateTicket $generateTicket
     * @param Ticket $ticket
     * @param ReceiveResponse $receiveResponse
     * @param ObjectManagerInterface $objectManager
     * @param MappingFactory $mappingFactory
     * @param QueueHelper $queueHelper
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ProductDataHelper $productDataHelper,
        Configuration $configuration,
        \Magenest\QuickBooksDesktop\WebConnector\Driver\Product $product,
        CustomQueueModel $customQueueFactory,
        GenerateTicket $generateTicket,
        Ticket $ticket,
        ReceiveResponse $receiveResponse,
        ObjectManagerInterface $objectManager,
        MappingFactory $mappingFactory,
        QueueHelper $queueHelper
    ) {
        parent::__construct($generateTicket, $ticket, $receiveResponse, $objectManager, $mappingFactory, $queueHelper);
        $this->_customQueue = $customQueueFactory;
        $this->_driver = $product;
        $this->_configHelper = $configuration;
        $this->_productDataHelper = $productDataHelper;

        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param $dataFromQWC
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    protected function processResponse($dataFromQWC)
    {
        $response = $this->getReceiveResponse();
        $response->setResponse($dataFromQWC);
        $result = $response->getValue();
        $statusCode = $response->getStatusCode();

        $iteratorId = $response->getIteratorId();
        $ticketId = $this->_ticket->loadByCode($dataFromQWC->ticket)->getId();

        if ($statusCode != 0) {
            $this->_driver->getCurrentQueue($ticketId)->saveCustomQueueStatus($ticketId, CustomQueue::CUSTOM_QUEUE_STATUS_ERROR, $iteratorId)->save();
        } else {
            if (empty($iteratorId)) {
                $iteratorId = $this->_customQueue->create()->getCollection()->addFieldToFilter('ticket_id', $ticketId)->getLastItem()->getData('iterator_id');
            }
            $this->_driver->getCurrentQueue($ticketId)->saveCustomQueueStatus($ticketId, CustomQueue::CUSTOM_QUEUE_STATUS_SUCCESS, $iteratorId)->save();

            if (isset($result['ItemInventoryRet'])) {
                $this->handleItemRetResponseData($result['ItemInventoryRet'], true);
            }

            if (isset($result['ItemNonInventoryRet'])) {
                $this->handleItemRetResponseData($result['ItemNonInventoryRet']);
            }
        }
    }

    /**
     * @param $itemInventoryRet
     *
     * @param bool $inventoryItem
     * @throws \Exception
     */
    private function handleItemRetResponseData($itemInventoryRet, $inventoryItem = false)
    {
        if (isset($itemInventoryRet['ListID'])) {
            $itemInventoryRet = [$itemInventoryRet];
        }

        foreach ($itemInventoryRet as $child) {
            try {
                $sku = @$child['Name'] ?: $child['FullName'];
                $itemsData[$sku] = [
                    'sku' => $sku,
                    'list_id' => $child['ListID'],
                    'edit_sequence' => $child['EditSequence']
                ];
                if ($inventoryItem) {
                    $itemsData[$sku]['qty'] = (int)$child['QuantityOnHand'] - (int)$child['QuantityOnSalesOrder'];
                }
            } catch (\Exception $e) {
                $this->logger()->critical('Data Handle: ' . $e->getMessage());
            }
        }

        $mappingData = $this->mappingProduct($itemsData ?? []);
        $queryProductAction = $this->_configHelper->getQueryProductAction();
        if ($queryProductAction == \Magenest\QuickBooksDesktop\Model\Config\Source\ProductQueryAction::MAPPING_PRODUCT) {
            $this->saveProductMappingData($mappingData);
        } else {
            $this->updateStock($mappingData);
        }
    }

    private function mappingProduct($itemsData)
    {
        if (!empty($itemsData)) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->productCollectionFactory->create()->addFieldToFilter(ProductInterface::SKU, ['in' => array_column($itemsData, 'sku')]);
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
            $collection->setFlag('has_stock_status_filter', true); // skip filter by stock status

            $products = $collection->getItems();

            foreach ($products as $product) {
                if (isset($itemsData[$product->getSku()])) {
                    $itemsData[$product->getSku()]['entity_id'] = $product->getId();
                }
            }

            // unset product is NOT exist in Magento
            foreach ($itemsData as $key => $item) {
                if (!isset($item['entity_id']) || empty($item['entity_id'])) {
                    unset($itemsData[$key]);
                }
            }
        }

        return $itemsData;
    }

    /**
     * @param $data
     *
     * @throws \Exception
     */
    private function saveProductMappingData($mappingData)
    {
        $companyId = $this->_queueHelper->getCompanyId();

        $saveData = [];
        foreach ($mappingData as $key => $item) {
            $saveData[] = [
                'company_id' => $companyId,
                'type' => Type::QUEUE_PRODUCT,
                'entity_id' => $item['entity_id'],
                'list_id' => $item['list_id'],
                'edit_sequence' => $item['edit_sequence'],
                'payment' => $item['sku'],
            ];
        }
        $this->_mapping->create()->saveMultipleData($saveData);
    }

    private function updateStock($mappingData)
    {
        foreach ($mappingData as $sku => $product) {
            if (isset($product['qty']) && !empty($product['qty'])) {
                $this->_productDataHelper->updateProductStock($sku, ['is_in_stock' => true, 'qty' => $product['qty']]);
            }
        }
    }

    public function getLastError($obj)
    {
        $ticketId = $this->_ticket->loadByCode($obj->ticket)->getId();
        $iteratorId = $this->_customQueue->create()->getCollection()->addFieldToFilter('ticket_id', $ticketId)->getLastItem()->getData('iterator_id');
        $this->_driver->getCurrentQueue()->saveCustomQueueStatus($ticketId, CustomQueue::CUSTOM_QUEUE_STATUS_SUCCESS, $iteratorId)->save();

        return new \Magenest\QuickBooksDesktop\Helper\Result\GetLastError('NOOP');
    }
}
