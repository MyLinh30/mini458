<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\Helper;

use Magenest\QuickBooksDesktop\Model\TicketFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Generate
 *
 * @package Magenest\QuickBooksDesktop\Model
 */
class GenerateTicket
{
    /**
     * @var TicketFactory
     */
    protected $_ticket;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * Generate constructor.
     * @param TicketFactory $ticketFactory
     * @param DateTime $date
     */
    public function __construct(
        TicketFactory $ticketFactory,
        DateTime $date
    ) {
        $this->_ticketFactory = $ticketFactory;
        $this->_date = $date;
    }

    /**
     * Generate new ticket
     *
     * @param string $username
     * @param int $processed
     * @return string
     */
    public function generateTicket($username, $processed)
    {
        try {
            /** @var \Magenest\QuickBooksDesktop\Model\Ticket $ticket */
            $ticket = $this->_ticketFactory->create();

            $ticketCode = $this->generateCode();
            $data = [
                'username' => $username,
                'ticket' => $ticketCode,
                'processed' => $processed,
                'created_at' => $this->_date->gmtDate()
            ];

            $ticket->setData($data);
            $ticket->save();

            return $ticketCode;
        } catch (\Exception $exception) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Psr\Log\LoggerInterface')
                ->info($exception->getMessage());
        }
    }

    /**
     * Generate code
     *
     * @return mixed
     */
    public function generateCode()
    {
        $gen_arr = [];
        $pattern = '[A2][N1][A1][N1]-[A1][N1][A2][N1]-[N2][A1]-[A1][N1][A1][N2]-[A1][N1][A2][N2]';

        preg_match_all("/\[[AN][.*\d]*\]/", $pattern, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $delegate = substr($match [0], 1, 1);
            $length = substr($match [0], 2, strlen($match [0]) - 3);
            $gen = '';
            if ($delegate == 'A') {
                $gen = $this->generateString($length);
            } elseif ($delegate == 'N') {
                $gen = $this->generateNum($length);
            }

            $gen_arr [] = $gen;
        }
        foreach ($gen_arr as $g) {
            $pattern = preg_replace('/\[[AN][.*\d]*\]/', $g, $pattern, 1);
        }

        return $pattern;
    }

    /**
     * Generate String
     *
     * @param $length
     * @return string
     */
    public function generateString($length)
    {
        if ($length == 0 || $length == null || $length == '') {
            $length = 5;
        }
        $c = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $rand = '';
        for ($i = 0; $i < $length; $i++) {
            $rand .= $c [rand(0, 51)];
        }

        return $rand;
    }

    /**
     * Generate Number
     *
     * @param $length
     * @return string
     */
    public function generateNum($length)
    {
        if ($length == 0 || $length == null || $length == '') {
            $length = 5;
        }
        $c = "0123456789";
        $rand = '';
        for ($i = 0; $i < $length; $i++) {
            $rand .= $c [rand(0, 9)];
        }

        return $rand;
    }
}
