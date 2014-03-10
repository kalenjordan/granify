<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

//clean old version credentials
/* @var $configDataCollection Mage_Core_Model_Mysql4_Config_Data_Collection */
$configDataCollection = Mage::getModel('core/config_data')->getCollection();
$configDataCollection->addFieldToFilter('path', array('like' => 'granify/general/%'));
$configDataCollection->addFieldToFilter('scope', array('in' => array('default', 'websites')));
/** @var $model Mage_Core_Model_Config_Data */
foreach ($configDataCollection as $model) {
    $model->delete();
}
