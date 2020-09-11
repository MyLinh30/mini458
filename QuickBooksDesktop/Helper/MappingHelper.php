<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Helper;

use Magenest\QuickBooksDesktop\Model\CompanyFactory;
use Magenest\QuickBooksDesktop\Model\ResourceModel\Mapping\Collection;
use Magenest\QuickBooksDesktop\Model\ResourceModel\Mapping\CollectionFactory;
use Magenest\QuickBooksDesktop\Model\ResourceModel\Company;
use Magenest\QuickBooksDesktop\Model\TicketFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Generate
 *
 * @package Magenest\QuickBooksDesktop\Model
 */
class MappingHelper
{
    /**
     * @var CollectionFactory
     */
    private $mappingCollectionFactory;
    /**
     * @var Company\CollectionFactory
     */
    private $companyCollectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        Company\CollectionFactory $companyFactory
    ){
        $this->companyCollectionFactory = $companyFactory;
        $this->mappingCollectionFactory = $collectionFactory;
    }

    public function searchListIdFromObject($entityId,$type,$company = null){
        if (!$company) $company = $this->getActiveCompany();
        /** @var Collection $mappingCollection */
        $mappingCollection = $this->mappingCollectionFactory->create();
        $matchedMapping =  $mappingCollection->addFieldToFilter('entity_id',$entityId)
            ->addFieldToFilter('type',$type)
            ->addFieldToFilter('company_id',$company)
            ->getFirstItem();

        if ($matchedMapping->getId()){
            return $matchedMapping->getListId();
        }
        return false;


    }
    public function getActiveCompany(){
        /** @var Company\Collection $companyCollection */
        $companyCollection = $this->companyCollectionFactory->create();
        $enabledCompany = $companyCollection->addFieldToFilter("status",1)->getFirstItem();
        return $enabledCompany->getId();
    }
}
