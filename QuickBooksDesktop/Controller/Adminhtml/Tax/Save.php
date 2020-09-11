<?php
/**
 * Created by PhpStorm.
 * User: namnt
 * Date: 6/30/18
 * Time: 3:23 PM
 */

namespace Magenest\QuickBooksDesktop\Controller\Adminhtml\Tax;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{

    protected $_taxCodeFactory;


    public function __construct(
        Action\Context $context,
        \Magenest\QuickBooksDesktop\Model\TaxCodeFactory $taxCodeFactory
    ) {
        $this->_taxCodeFactory = $taxCodeFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            foreach ($post['tax'] as $tax => $value) {
                $taxCode = $this->_taxCodeFactory->create()->load($tax, "tax_id");
                if ($taxCode->getId()) {
                    $taxCode->setTaxTitle(@$value['title']);
                    $taxCode->setCode(@$value['code'])->save();
                } else {
                    $taxCode->addData([
                        'tax_id' => $tax,
                        'tax_title' => @$value['title'],
                        'code' => @$value['code']
                    ]);
                    $taxCode->save();
                }
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        $this->messageManager->addSuccessMessage("Success.");
        return $resultRedirect->setPath('*/*/');
    }
}
