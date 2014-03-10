<?php
/** @var $install Granify_Sales_Model_Resource_Setup */
$install = $this;
$tableName = $install->getTable('granify_sales/order_info');
if (!$install->tableExists($tableName)) {
    $table = $install->getConnection()->newTable($tableName);
    $table->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array('primary' => true, 'auto_increment' => true))
        ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP)
        ->addColumn('deferred_data', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, null,
            array('nullable' => false))
        ->addColumn('request_failure', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array('nullable' => false, 'unsigned' => true));
    $install->getConnection()->createTable($table);

    //fixed adding auto_increment
    $sql = "ALTER TABLE `{$tableName}` CHANGE COLUMN `item_id` `item_id` INT NOT NULL AUTO_INCREMENT FIRST;";
    $install->getConnection()->query($sql);
}
