<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\WebConnector;

use Magenest\QuickBooksDesktop\Model\ResourceModel\Queue\CollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\Model\Mapping as Mapping;
use Magenest\QuickBooksDesktop\Model\User as UserModel;
use Psr\Log\LoggerInterface;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;

/**
 * Class Driver
 * @package Magenest\QuickBooksDesktop\WebConnector
 */
abstract class Driver
{
    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var UserModel
     */
    protected $_user;

    /**
     * @var Mapping
     */
    protected $_map;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Driver constructor.
     * @param CollectionFactory $collectionFactory
     * @param ObjectManagerInterface $objectManager
     * @param UserModel $user
     * @param Mapping $map
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ObjectManagerInterface $objectManager,
        LoggerInterface $loggerInterface,
        UserModel $user,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Mapping $map,
        QueueHelper $queueHelper
    ) {
        $this->_logger = $loggerInterface;
        $this->_user = $user;
        $this->_map = $map;
        $this->_collectionFactory = $collectionFactory;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_queueHelper = $queueHelper;
    }

    /**
     * Authenticate Username and password
     *
     * @param \stdClass $obj
     * @return bool
     */
    public function authenticate($obj)
    {
        $username = $obj->strUserName;
        $password = $obj->strPassword;

        $pass = md5($password);
        $model = $this->_user->load($username, 'username');

        if ($model->getId()) {
            $passUser = $model->getPassword();
            $status = $model->getStatus();

            if (($pass == $passUser) && ($status == 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function getTotalsQueue()
    {
        //TODO in Children
    }

    /**
     * @param null $ticket
     * @return \Magento\Framework\DataObject
     */
    abstract public function getCurrentQueue($ticket = null);

    /**
     * @param $queue
     * @return string
     */
    public function prepareSendRequestXML($dataFromQWC)
    {
        //TODO in Children
    }



    public function simpleXml($value, $tag, $length = 0)
    {
        if ($value !== '' && $value !== null) {
            $value = $this->validateXML($value, $length);
            return "<$tag>$value</$tag>";
        } else {
            return '';
        }
    }

    public function multipleXml($value, array $tags, $length = 0)
    {
        $xml = '';
        if ($value !== '' && $value !== null) {
            $value = $this->validateXML($value, $length);
            foreach ($tags as $tag) {
                $xml .= "<$tag>";
            }
            $xml .= "$value";
            $tags = array_reverse($tags);
            foreach ($tags as $tag) {
                $xml .= "</$tag>";
            }
        }
        return $xml;
    }

    /**
     * @param $value
     * @param int $length
     * @return bool|string
     */
    public function validateXML($value, $length = 0)
    {
        if ((int)$length > 0) {
            $value = substr($this->convertVNtoEN($value), 0, $length);
        } else {
            $value = $this->convertVNtoEN($value);
        }
        return $value;
    }

    protected function convertVNtoEN($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);

        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);

        $cyr = [
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
            'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
            'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'
        ];
        $lat = [
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
            'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya',
            'A', 'B', 'V', 'G', 'D', 'E', 'Io', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P',
            'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A', 'I', 'Y', 'e', 'Yu', 'Ya'
        ];
        $str = str_replace($cyr, $lat, $str);

        return htmlspecialchars($str, ENT_QUOTES);
    }

    protected function removeSpecialChracter($value)
    {
        return str_replace(['&', '”', '\'', '<', '>', '"'], ['&#38;', '&#34;', '&#39;', '&lt;', '&gt;', '&#34;'], $value);
    }
}
