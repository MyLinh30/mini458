<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model;

/**
 * Class Connector
 * @package Magenest\QuickBooksDesktop\Model
 */
class Connector
{
    const CONFIG_OPTION = 'qbdesktop/qbd_setting/option';
    const CONFIG_CUSTOMER_RECEIVE = 'qbdesktop/qbd_setting/customer_receive';
    const CONFIG_VENDOR = 'qbdesktop/qbd_setting/vendor';
    const CONFIG_SYNC_FILE = 'qbdesktop/qbd_setting/selected';
    const CONFIG_BASE_URL = 'web/secure/base_url';
    const CONFIG_BASE_URL_DEFAULT = 'web/secure/base_url';
    const CONFIG_SUPPORT_URL = 'qbdesktop/qbd_setting/support_url';
    const CONFIG_USER = 'qbdesktop/qbd_setting/user_name';
    const CONFIG_OWNER_ID= 'qbdesktop/qbd_setting/owner_id';
    const CONFIG_FILE_ID = 'qbdesktop/qbd_setting/file_id';
    const CONFIG_SCHEDULER = 'qbdesktop/qbd_setting/scheduler';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magenest\QuickBooksDesktop\Helper\CreateQueue
     */
    protected $_queueHlp;

    /**
     * Connector constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magenest\QuickBooksDesktop\Helper\CreateQueue $createQueue,
        array $data = []
    ) {
        $this->_queueHlp = $createQueue;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getSync()
    {
        $syncFile =  $this->_scopeConfig->getValue(self::CONFIG_SYNC_FILE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $syncFile;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        $baseUrl =  $this->_scopeConfig
            ->getValue(self::CONFIG_BASE_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $baseUrl;
    }

    /**
     * @return mixed
     */
    public function getDefaultUrl()
    {
        $defaultUrl =  $this->_scopeConfig->getValue(self::CONFIG_BASE_URL_DEFAULT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $defaultUrl;
    }

    /**
     * @return mixed
     */
    public function getSupportUrl()
    {
        $supportUrl =  $this->_scopeConfig->getValue(self::CONFIG_SUPPORT_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $supportUrl;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        $user =  $this->_scopeConfig->getValue(self::CONFIG_USER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $user;
    }

    /**
     * @return mixed
     */
    public function getOwnerId()
    {
        $ownerId =  $this->_scopeConfig->getValue(self::CONFIG_OWNER_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $ownerId;
    }

    /**
     * @return mixed
     */
    public function getFileId()
    {
        $fileId =  $this->_scopeConfig->getValue(self::CONFIG_FILE_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $fileId;
    }

    /**
     * @return mixed
     */
    public function getScheduler()
    {
        $scheduler =  $this->_scopeConfig->getValue(self::CONFIG_SCHEDULER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $scheduler;
    }

    /**
     * @return mixed
     */
    public function getOption()
    {
        $option =  $this->_scopeConfig->getValue(self::CONFIG_OPTION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $option;
    }

    /**
     * @return mixed
     */
    public function getCustomerReceive()
    {
        $cusReceive =  $this->_scopeConfig->getValue(self::CONFIG_CUSTOMER_RECEIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $cusReceive;
    }

    /**
     * @return mixed
     */
    public function getVendor()
    {
        $vendor =  $this->_scopeConfig->getValue(self::CONFIG_VENDOR, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $vendor;
    }

    public function getConnectedCompany()
    {
        return $this->_queueHlp->getCompanyId();
    }
}
