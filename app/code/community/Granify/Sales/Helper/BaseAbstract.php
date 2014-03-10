<?php
/**
 * Granify Sales base abstract class helper
 *
 * @category    Granify
 * @package     Granify_Sales
 */
abstract class Granify_Sales_Helper_BaseAbstract extends Mage_Core_Helper_Abstract
{
    /**
     * Get app model
     *
     * @return Mage_Core_Model_App
     * @codeCoverageIgnore
     */
    protected function _getApp()
    {
        return Mage::app();
    }

    /**
     * Get model singleton
     *
     * @param string $classPath
     * @return Mage_Core_Model_Abstract
     * @codeCoverageIgnore
     */
    protected function _getSingleton($classPath)
    {
        return Mage::getSingleton($classPath);
    }

    /**
     * Get Magento helper
     *
     * @param string $classPath             The helper class path
     * @return Mage_Core_Helper_Abstract
     * @codeCoverageIgnore
     */
    protected function _getHelper($classPath)
    {
        return Mage::helper($classPath);
    }

    /**
     * Get Magento model
     *
     * @param string $classPath             The model class path
     * @return Mage_Core_Model_Abstract
     * @codeCoverageIgnore
     */
    protected function _getModel($classPath)
    {
        return Mage::getModel($classPath);
    }

    /**
     * Get Mage Registry
     *
     * @param string $key
     * @return mixed
     * @codeCoverageIgnore
     */
    public function registry($key)
    {
        return Mage::registry($key);
    }
}
