<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\QuickBooksDesktop\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\SetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**@#+
     * @constant
     */
    const TABLE_PREFIX = 'magenest_qbd_';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->createCreateCustomQueue($setup->startSetup());
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->createQBTax($setup->startSetup());
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->createTaxCodeTable($setup->startSetup());
        }

        if (version_compare($context->getVersion(), '2.1.1', '<')) {
            $this->removeExpire($setup->startSetup());
            $this->createItemSalesOrderTable($setup->startSetup());
        }

        if (version_compare($context->getVersion(), '2.1.2', '<')) {
            $this->updateMsg($setup->startSetup());
        }

        if (version_compare($context->getVersion(), '2.1.21', '<')) {
            $this->setIndexMapping($setup->startSetup());
        }

        $setup->endSetup();
    }

    private function createTaxCodeTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'tax_code_mapping';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Mapping Id'
        )->addColumn(
            'tax_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Tax ID'
        )->addColumn(
            'tax_title',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Tax Title'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Code'
        );
        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the table magenest_qbonline_customer_queue
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createQBTax($installer)
    {
        $tableName = self::TABLE_PREFIX . 'tax_code';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Mapping Id'
        )->addColumn(
            'tax_code',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Tax Code'
        )->addColumn(
            'company_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Company ID'
        )->addColumn(
            'list_id',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'List ID'
        )->addColumn(
            'edit_sequence',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Edit Sequence'
        );
        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the table magenest_qbonline_customer_queue
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createCreateCustomQueue($installer)
    {
        $tableName = self::TABLE_PREFIX . 'custom_queue';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Mapping Id'
        )->addColumn(
            'ticket_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Ticket ID'
        )->addColumn(
            'type',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Type'
        )->addColumn(
            'company_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Company ID'
        )->addColumn(
            'status',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Status'
        )->addColumn(
            'iterator_id',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Iterator ID'
        )->addColumn(
            'operation',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Operation'
        );
        $installer->getConnection()->createTable($table);
    }

    /**
     *
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function removeExpire($installer)
    {
        $installer->getConnection()->dropColumn(
            $installer->getTable('magenest_qbd_user'),
            'expired_date'
        );
    }

    /**
     * Create the table magenest_qbd_item_sales_order
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createItemSalesOrderTable($installer)
    {
        $tableName = self::TABLE_PREFIX . 'item_sales_order';
        if ($installer->tableExists($tableName)) {
            return;
        }
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Mapping Id'
        )->addColumn(
            'list_id_order',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'List ID Order'
        )->addColumn(
            'company_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Company Id'
        )->addColumn(
            'txn_line_id',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'TxnLineID'
        )->addColumn(
            'list_id_item',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'List ID Item'
        )->addColumn(
            'sku',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Sku'
        );
        $installer->getConnection()->createTable($table);
    }

    /**
     *
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function updateMsg($installer)
    {
        $installer->getConnection()->modifyColumn(
            $installer->getTable('magenest_qbd_queue'),
            'msg',
            [
                'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'    => null,
                'nullable'  => true,
                'comment'   => 'Msg'
            ]
        );

        $installer->getConnection()->modifyColumn(
            $installer->getTable('magenest_qbd_company'),
            'company_name',
            [
                'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'    => null,
                'nullable'  => true,
                'comment'   => 'Company Name'
            ]
        );
    }

    private function setIndexMapping(\Magento\Framework\Setup\SchemaSetupInterface $installer)
    {
        $installer->getConnection()->modifyColumn(
            $installer->getTable('magenest_qbd_mapping'),
            'list_id',
            [
                'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false
            ]
        );

        $installer->getConnection()->addIndex(
            $installer->getTable('magenest_qbd_mapping'),
            $installer->getIdxName(
                $installer->getTable('magenest_qbd_mapping'),
                ['entity_id', 'company_id', 'type', 'list_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'company_id', 'type', 'list_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
