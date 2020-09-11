<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Block\System\Config\Form\Field\Export;

use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;
use Magenest\QuickBooksDesktop\Model\CustomQueue;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class QWC
 *
 * @package Magenest\QuickBooksDesktop\Block\System\Config\Form\Field\Export
 */
class QWCProduct extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magenest\QuickBooksDesktop\Model\Connector
     */
    protected $_connector;

    /**
     * @var string
     */
    protected $_buttonLabel = 'Generate QWC file for Products';

    /**
     * QWC constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magenest\QuickBooksDesktop\Model\Connector $connector
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magenest\QuickBooksDesktop\Model\Connector $connector,
        array $data = []
    ) {
        $this->_connector = $connector;
        parent::__construct($context, $data);
    }

    /**
     *
     * @param  $getButtonLabel
     * @return $this
     */
    public function setButtonLabel($getButtonLabel)
    {
        $this->_buttonLabel = $getButtonLabel;
        return $this;
    }

    /**
     * @return $this|\Magento\Config\Block\System\Config\Form\Field
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/qwcproduct.phtml');
        }

        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
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

    /**
     * @return int
     */
    public function setType()
    {
        return TypeQuery::QUERY_PRODUCT;
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
        $originalData = $element->getOriginalData();
        $buttonLabel  = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_buttonLabel;
        $config = $this->_connector->getUser();
        if ($config && $this->_connector->getConnectedCompany()) {
            $this->addData(
                [
                    'button_label' => __($buttonLabel),
                    'html_id'      => $element->getHtmlId(),
                    'ajax_url'     => $this->_urlBuilder->getUrl('qbdesktop/QWC/export'),
                ]
            );

            return $this->_toHtml();

        } else {
            $element->setDisabled('disabled');

            return $element->getElementHtml();
        }
    }
}
