<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Block\System\Config\Form\Field\Export;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;
use Magenest\QuickBooksDesktop\Helper\CreateQueue;

/**
 * Class QWC
 *
 * @package Magenest\QuickBooksDesktop\Block\System\Config\Form\Field\Export
 */
class QWCCompany extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magenest\QuickBooksDesktop\Model\Connector
     */
    protected $_connector;

    /**
     * @var CreateQueue
     */
    protected $_queueHelper;

    /**
     * QWC constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magenest\QuickBooksDesktop\Model\Connector $connector
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magenest\QuickBooksDesktop\Model\Connector $connector,
        CreateQueue $queueHelper,
        array $data = []
    ) {
        $this->_connector = $connector;
        $this->_queueHelper = $queueHelper;
        parent::__construct($context, $data);
    }
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $config = $this->_connector->getUser();
        $companyId = $this->_queueHelper->getCompanyId();
        $buttonBlock = $this->getForm()->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');
        if ($config && !$companyId) {
            $params = [
                'website' => $buttonBlock->getRequest()->getParam('website'),
                'type' => TypeQuery::QUERY_COMPANY
            ];
            $url = $this->getUrl("qbdesktop/QWC/export", $params);
            $data = [
                'id' => 'system_qwc_company',
                'label' => __('Query Company'),
                'onclick' => "setLocation('" . $url . "')",
            ];
        } elseif ($config && $companyId) {
            $params = [
                'website' => $buttonBlock->getRequest()->getParam('website'),
                'type' => TypeQuery::QUERY_DISCONNECT
            ];
            $url = $this->getUrl("qbdesktop/QWC/export", $params);
            $data = [
                'id' => 'system_qwc_disconnect',
                'label' => __('Disconnect Company'),
                'onclick' => "setLocation('" . $url . "')",
            ];
        } else {
            $data = [
                'id' => 'system_qwc_company',
                'label' => __('No user set yet'),
                'disabled' => true
            ];
        }
        $html = $buttonBlock->setData($data)->toHtml();
        return $html;
    }


    /**
     * Return Varnish version to this class
     *
     * @return int
     */
    public function getQWCVersion()
    {
        return 4;
    }
}
