<?php


namespace Magenest\Cybergame\Setup;


use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if(version_compare($context->getVersion(),'1.0.1','<'))
        {
            $installer = $setup;
            $installer->startSetup();
            $connection = $installer->getConnection();
            $gamerTable = $installer->getConnection()->newTable(
                $installer->getTable('gamer_account_list')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10, [
                'nullable' => false,
            ],
                'Product ID'
            )->addColumn(
                'account_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50, [
                'nullable' => false,
            ],
                'Account Name'
            )->addColumn(
                'password',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10, [
                'nullable' => false,
            ],
                'Password'
            )->addColumn(
                'hour',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                5, [
                'nullable' => false,
            ],
                'Hour'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->setComment('Gamer Account List Table');




            $roomTable = $installer->getConnection()->newTable(
                $installer->getTable('room_extra_option')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null, [
                'nullable' => false,
            ],
                'Product ID'
            )->addColumn(
                'number_pc',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null, [
                'nullable' => false,
            ],
                'Number PC'
            )->addColumn(
                'address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null, [
                'nullable' => false,
            ],
                'Address'
            )->addColumn(
                'food_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null, [
                'nullable' => false,
            ],
                'Food Price'
            )->addColumn(
                'drink_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null, [
                'nullable' => false,
            ],
                'Drink Price'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->setComment('Room Extra Option Table');

            $installer->getConnection()->createTable($gamerTable);
            $installer->getConnection()->addIndex(
                $installer->getTable('gamer_account_list'),
                $setup->getIdxName('gamer_account_list',['product_id','account_name']),['product_id','account_name']
            );
            $installer->getConnection()->createTable($roomTable);
            $installer->getConnection()->addIndex(
                $installer->getTable('room_extra_option'),
                $setup->getIdxName('room_extra_option',['product_id']),['product_id']
            );
            $installer->endSetup();
        }
    }
}
