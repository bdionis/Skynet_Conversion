<?php

namespace Skynet\Conversion\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class UpgradeSchema
 * @package Skynet\Conversion\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->upgradeToVersion002($setup);
        }

        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->upgradeToVersion003($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function upgradeToVersion002(SchemaSetupInterface $setup)
    {
        $conversionTable = $setup->getTable('eav_attribute_conversion');

        $table = $setup->getConnection()
            ->newTable($conversionTable)
            ->addColumn(
                'conversion_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => true,
                    'length' => 5,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Conversion Attribute Id'
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'length' => 5,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => false
                ],
                'Attribute Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'length' => 5,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => false
                ],
                'Store Id'
            )
            ->addColumn(
                'conversion_name',
                Table::TYPE_TEXT,
                null,
                [
                    'identity' => false,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => false
                ],
                'Conversion attribute name'
            )
            ->addColumn(
                'conversion_rate',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => false
                ],
                'Conversion rate to default attribute'
            )
            ->addIndex(
                $setup->getIdxName(
                    $conversionTable,
                    ['conversion_id']
                ),
                ['conversion_id'],
                ['type' => AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $setup->getIdxName(
                    $conversionTable,
                    ['store_id', 'attribute_id'],
                    true
                ),
                ['store_id', 'attribute_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName(
                    $conversionTable,
                    'store_id',
                    $setup->getTable('store'),
                    'store_id'
                ),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    $conversionTable,
                    'attribute_id',
                    $setup->getTable('eav_attribute'),
                    'attribute_id'
                ),
                'attribute_id',
                $setup->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Table with attribute conversion names and rates');

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function upgradeToVersion003(SchemaSetupInterface $setup)
    {
        $conversionTable = $setup->getTable('eav_attribute_option_main_conversion');

        $table = $setup->getConnection()
            ->newTable($conversionTable)
            ->addColumn(
                'conversion_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'length' => 5,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => false
                ],
                'Conversion Attribute Id'
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'length' => 5,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => false
                ],
                'Attribute Id'
            )
            ->addIndex(
                $setup->getIdxName(
                    $conversionTable,
                    ['conversion_id', 'attribute_id'],
                    true
                ),
                ['conversion_id', 'attribute_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName(
                    $conversionTable,
                    'conversion_id',
                    $setup->getTable('eav_attribute_conversion'),
                    'conversion_id'
                ),
                'conversion_id',
                $setup->getTable('eav_attribute_conversion'),
                'conversion_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    $conversionTable,
                    'attribute_id',
                    $setup->getTable('eav_attribute'),
                    'attribute_id'
                ),
                'attribute_id',
                $setup->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Table with main conversion option for attribute');

        $setup->getConnection()->createTable($table);
    }
}
