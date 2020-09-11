<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\User;

use Magenest\QuickBooksDesktop\Controller\Adminhtml\User as AbstractUser;
use Magenest\QuickBooksDesktop\Model\ResourceModel\User\CollectionFactory as UserFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class MassDelete
 * @package Magenest\QuickBooksDesktop\Controller\Adminhtml\User
 */
class MassDelete extends AbstractUser
{

    const CONFIG_USER = 'qbdesktop/qbd_setting/user_name';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;


    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param UserFactory $userFactory
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        UserFactory $userFactory,
        ForwardFactory $resultForwardFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        Filter $filter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        parent::__construct($context, $coreRegistry, $resultPageFactory, $userFactory, $resultForwardFactory, $filter);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        try {
            $collections = $this->_filter->getCollection($this->_collectionFactory->create());
            $totals = 0;
            $user = $this->_scopeConfig->getValue(self::CONFIG_USER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            foreach ($collections as $item) {
                if ($item->getUserId() == $user) {
                    $this->configWriter->save(
                        self::CONFIG_USER,
                        null,
                        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        $scopeId = 0
                    );
                    $this->clearCache();
                }
                /** @var \Magenest\QuickBooksDesktop\Model\User $item */
                $item->delete();
                $totals++;
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deteled.', $totals));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while delete the post(s).'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }

    public function clearCache()
    {
        $types = ['config',];
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
