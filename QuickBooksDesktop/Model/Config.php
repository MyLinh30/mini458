<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;

/**
 * Class Config
 * @package Magenest\QuickBooksDesktop\Model
 */
class Config
{
    const NONCE = '0123456789ABCDEF';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface $_cacheState
     */
    protected $_cacheState;

    /**
     * @var Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;

    /**
     * @var Connector
     */
    protected $_connector;

    /**
     * @var User
     */
    protected $_user;

    /**
     * Config constructor.
     * @param Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param Dir\Reader $reader
     * @param Connector $connector
     * @param User $user
     */
    public function __construct(
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magenest\QuickBooksDesktop\Model\Connector $connector,
        \Magenest\QuickBooksDesktop\Model\User $user
    ) {
        $this->readFactory = $readFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_cacheState = $cacheState;
        $this->reader = $reader;
        $this->_connector = $connector;
        $this->_user = $user;
    }

    /**
     * Return generated sample.qwc configuration file
     *
     * @return string
     * @api
     */
    public function getQWCFile($appName)
    {
        $moduleEtcPath = $this->reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magenest_QuickBooksDesktop');
        $configFilePath = $moduleEtcPath . '/qwc/sample.qwc';
        $directoryRead = $this->readFactory->create($moduleEtcPath);
        $configFilePath = $directoryRead->getRelativePath($configFilePath);
        $data = $directoryRead->readFile($configFilePath);

        return strtr($data, $this->_getReplacements($appName));
    }

    /**
     * Random string with lenght
     *
     * @param int $length
     * @return string
     */
    protected function getNonce($length = 32)
    {
        $tmp = str_split(self::NONCE);
        shuffle($tmp);

        return substr(implode('', $tmp), 0, $length);
    }

    /**
     * Prepare data for qwc config
     *
     * @return array
     */
    protected function _getReplacements($appName)
    {
        $config = $this->_connector;
        $baseUrl = $config->getBaseUrl();
        if (!empty($baseUrl)) {
            $url = $baseUrl;
        } else {
            $url = $config->getDefaultUrl();
        }

        if (strpos($url, "://localhost") !== false) {
            if (strpos($url, 'https://') === 0) {
                $url = str_replace("https", "http", $url);
            }
        } else {
            if (strpos($url, 'https://') !== 0) {
                $url = str_replace("http", "https", $url);
            }
        }
        $certUrl = $this->getCertUrl($url);

        $supportUrl = $url . 'support.php';
        $userId = $config->getUser();
        $userName = $this->_user->load($userId)->getUsername();
        $ownerId = $this->getNonce(8) . '-' . $this->getNonce(4) . '-' . $this->getNonce(4) . '-' . $this->getNonce(4) . '-' . $this->getNonce(12);
        $fileId = $this->getNonce(8) . '-' . $this->getNonce(4) . '-' . $this->getNonce(4) . '-' . $this->getNonce(4) . '-' . $this->getNonce(12);
        $scheduler = $config->getScheduler();

        if ($appName == "Synchronization from Magento") {
            $appUrl = $url . 'qbdesktop/connection/start';
        } elseif ($appName == "Mapping Tax") {
            $appUrl = $url . 'qbdesktop/connection_tax/sync';
        } elseif ($appName == "Mapping Product") {
            $appUrl = $url . 'qbdesktop/connection_product/sync';
        } else {
            $appUrl = $url . 'qbdesktop/connection_company/sync';
        }

        $appName .= ' ' . $url;

        return [
            '{{AppName}}' => $appName,
            '{{AppURL}}' => $appUrl,
            '{{CertURL}}' => $certUrl,
            '{{username}}' => $userName,
            '{{supportURL}}' => $supportUrl,
            '{{OwnerID}}' => '{' . $ownerId . '}',
            '{{FileID}}' => '{' . $fileId . '}',
            '{{minutes}}' => $scheduler,
        ];
    }

    public function getCertUrl($tempUrl){
        $isHttps = strpos($tempUrl,'https');
        $certUrl = $isHttps !== false ? 'https://' : 'http://';
        $arr_url = explode('.',$tempUrl);
        if(count($arr_url) <=2){
            $certUrl =  $tempUrl;
        }
        else{
            $count = count($arr_url);
            $certUrl .= $arr_url[$count-2].'.'.$arr_url[$count-1];
        }
        return $certUrl;
    }
}
