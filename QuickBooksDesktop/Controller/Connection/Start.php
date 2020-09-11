<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Controller\Connection;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\Dir;
use Magenest\QuickBooksDesktop\WebConnector\Handlers\Queue;
use Zend_Soap_Server as ZendSoapServer;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class Start
 * @package Magenest\QuickBooksDesktop\Controller\Connector
 */
class Start extends Action
{
    /**
     * @var string
     */
    protected $_wsdl;

    /**
     * @var Queue
     */
    protected $_handlers;

    /**
     * @var ZendSoapServer
     */
    protected $_soapServer;

    /**
     * @var Request
     */
    protected $_requestWebapi;

    /**
     * Start constructor.
     * @param Context $context
     * @param Reader $configReader
     * @param Queue $handlers
     * @param ZendSoapServer $soapServer
     * @param Request $request
     */
    public function __construct(
        Context $context,
        Reader $configReader,
        Queue $handlers,
        ZendSoapServer $soapServer,
        Request $request
    ) {
        $wsdlBasePath = $configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magenest_QuickBooksDesktop') . '/wsdl/';
        $this->_wsdl = $wsdlBasePath . 'QBWebConnectorSvc.wsdl';
        $this->_handlers = $handlers;
        $this->_soapServer = $soapServer;
        $this->_requestWebapi = $request;
        parent::__construct($context);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Zend_Soap_Server_Exception
     */
    public function execute()
    {
        $method = $this->_requestWebapi->getHttpMethod();
        if ($method != Request::HTTP_METHOD_POST) {
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('/');
        }

        $soapClass = $this->_soapServer;
        $soapClass->setWsdl($this->_wsdl);
        $soapClass->setObject($this->getHandler());
        $soapClass->handle();
    }

    /**
     * @return mixed
     */
    protected function getHandler()
    {
        return $this->_handlers;
    }
}
