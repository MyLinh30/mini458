<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Ticket
 * @package Magenest\QuickBooksDesktop\Model
 * @method int getTicketId()
 * @method string getTicket()
 * @method string getUserName()
 * @method string geCreatedAt()
 * @method string getProcessed()
 * @method string getCurrent()
 * @method string getIpAddr()
 * @method string getLastErrorMsg()
 * @method Ticket setCurrent(int $current)
 * @method Ticket setData(string $data)
 */
class Ticket extends AbstractModel
{
    /**
     * Initize
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksDesktop\Model\ResourceModel\Ticket');
    }

    /**
     * @param $ticketCode
     * @return $this
     */
    public function loadByCode($ticketCode)
    {
        return $this->load($ticketCode, 'ticket');
    }
}
