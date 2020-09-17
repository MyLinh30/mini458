<?php


namespace Magenest\Staff\Setup;


use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $linhTable = $installer->getConnection()->newTable($installer->getTable('magenest_staff'))
                ->addColumn('id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID')
                ->addColumn('customer_id',
                    \Magento\Framework\Db\Ddl\Table::TYPE_INTEGER,
                    null, [
                        'nullable' => false], 'Customer ID')
                ->addColumn('nick_name',
                    \Magento\Framework\Db\Ddl\Table::TYPE_TEXT,
                    255, ['nullable' => false], 'Nick name')
                ->addColumn('type',
                    \Magento\Framework\Db\Ddl\Table::TYPE_INTEGER,
                    null, ['nullable' => false],
                    'Type')
                ->addColumn('status',
                    \Magento\Framework\Db\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status')
                ->addColumn('update_at',
                    \Magento\Framework\Db\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Update at');
            $installer->getConnection()->createTable($linhTable);
            $installer->endSetup();
        }
    }
}
