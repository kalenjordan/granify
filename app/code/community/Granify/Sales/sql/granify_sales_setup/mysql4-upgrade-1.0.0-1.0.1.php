<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$connection = $installer->getConnection();

/* @var $collection Granify_Sales_Model_Resource_OrderInfo_Collection */
$collection = Mage::getModel('granify_sales/orderInfo')->getCollection();

/* @var $item Granify_Sales_Model_OrderInfo */
foreach ($collection as $item) {
    $item->delete();
}

$table = $installer->getTable('granify_sales/order_info');
$connection->addColumn($table, 'store_id', 'SMALLINT UNSIGNED NOT NULL');
$connection->addConstraint(
    'FK_ORDER_INFO_STORE_ID', $table, 'store_id',
    $installer->getTable('core/store'), 'store_id'
);
