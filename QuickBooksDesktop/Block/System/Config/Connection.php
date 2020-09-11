<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Connection
 * @package Magenest\QuickBooksDesktop\Block\System\Config
 */
class Connection extends \Magento\Config\Block\System\Config\Form\Field implements RendererInterface
{
    /**
     * @var \Magenest\QuickBooksDesktop\Model\Company
     */
    protected $_company;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magenest\QuickBooksDesktop\Model\Company $company,
        array $data = []
    ) {
        $this->_company=$company;
        parent::__construct($context, $data);
    }


    public function render(AbstractElement $element)
    {
        $model = $this->_company->load(1, 'status');
        $name = $model->getCompanyName();
        if ($name) {
            return  "<h2 style='color:green; text-align: center; font-weight: bold'>".'Company: '.$name.' is now connected'."</h2>";
        } else {
            return "<h2 style='color:red; text-align: center; font-weight: bold'>The company is currently disconnected</h2>";
        }
    }
}
