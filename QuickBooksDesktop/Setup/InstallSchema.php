<?php
/**
 * Copyright © 2017 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 * @package Magenest\QuickBooksDesktop\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        // install table ticket
        $installer->startSetup();
        if (!$installer->tableExists('magenest_qbd_ticket')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('magenest_qbd_ticket')
            )->addColumn(
                'ticket_id',
                Table::TYPE_INTEGER,
                null,
                [
                 'identity' => true,
                 'nullable' => false,
                 'primary'  => true,
                ],
                'Ticket ID'
            )->addColumn(
                'ticket',
                Table::TYPE_TEXT,
                28,
                ['nullable' => true],
                'Ticket'
            )->addColumn(
                'username',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'User Name'
            )->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Creatd At'
            )->addColumn(
                'processed',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => true],
                'Processed'
            )->addColumn(
                'current',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => true],
                'Current'
            )->addColumn(
                'ipaddr',
                Table::TYPE_TEXT,
                30,
                ['nullable' => true],
                'Ip Addr'
            )->addColumn(
                'lasterror_msg',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Laste Error Msg'
            )->setComment(
                'Ticket Table'
            );
            $installer->getConnection()->createTable($table);
        }

        // install table user
        if (!$installer->tableExists('magenest_qbd_user')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('magenest_qbd_user')
            )->addColumn(
                'user_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'User ID'
            )->addColumn(
                'username',
                Table::TYPE_TEXT,
                50,
                [
                 'nullable' => false,
                ],
                'Username'
            )->addColumn(
                'password',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Password'
            )->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'expired_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Expired Date'
            )->addColumn(
                'remote_ip',
                Table::TYPE_TEXT,
                20,
                ['nullable' => true],
                'Remote Ip '
            );
            $installer->getConnection()->createTable($table);
        }

        // install table queue
        if (!$installer->tableExists('magenest_qbd_queue')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('magenest_qbd_queue')
            )->addColumn(
                'queue_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Queue ID'
            )->addColumn(
                'ticket_id',
                Table::TYPE_TEXT,
                11,
                ['nullable' => true],
                'Ticket Id'
            )->addColumn(
                'action_name',
                Table::TYPE_TEXT,
                50,
                ['nullable' => true],
                'Action Name'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                50,
                ['nullable' => true],
                'Type'
            )->addColumn(
                'company_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Company ID'
            )->addColumn(
                'enqueue_datetime',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Enqueue Datetime'
            )->addColumn(
                'dequeue_datetime',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Dequeue Datetime'
            )->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                3,
                ['nullable' => true],
                'Status'
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => true],
                'Entity Id'
            )->addColumn(
                'operation',
                Table::TYPE_SMALLINT,
                2,
                ['nullable' => true],
                'Operation'
            )->addColumn(
                'qbd_delete_id',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => true],
                'Qbd Delete Id'
            )->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => true],
                'Priority'
            )->addColumn(
                'msg',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Msg'
            )->addColumn(
                'payment',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Payment Method'
            )->addColumn(
                'vendor_name',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Vendor Name'
            );

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('magenest_qbd_company')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('magenest_qbd_company')
            )->addColumn(
                'company_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Company ID'
            )->addColumn(
                'company_name',
                Table::TYPE_TEXT,
                20,
                ['nullable' => true],
                'Company Name'
            )->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'note',
                Table::TYPE_TEXT,
                20,
                ['nullable' => true],
                'Note '
            );
            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('magenest_qbd_mapping')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('magenest_qbd_mapping')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'ID'
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Entity ID'
            )->addColumn(
                'type',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Type'
            )->addColumn(
                'company_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Company ID'
            )->addColumn(
                'list_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'List ID'
            )->addColumn(
                'edit_sequence',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Edit Sequence '
            )->addColumn(
                'payment',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Payment Method'
            )->addIndex(
                $installer->getIdxName(
                    $installer->getTable('magenest_qbd_mapping'),
                    ['entity_id', 'company_id', 'type', 'list_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'company_id', 'type', 'list_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
