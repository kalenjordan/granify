<?php
/**
 * API shop info resource class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Resource_ShopInfo_V2 extends Granify_Sales_Model_Api_Resource_ShopInfo_V1
{
    /**
     * Method get collection
     *
     * @return array
     */
    protected function _getCollection()
    {
        $data = parent::_getCollection(); //get currency
        if ($this->_getQuery('modules')) {
            $data['modules'] = $this->_getModulesInfo();
        }
        return $data;
    }

    /**
     * Get modules information
     *
     * @return array
     */
    protected function _getModulesInfo()
    {
        $modules = $this->_getApp()->getConfig()->getNode('modules')->asArray();
        $data = array();
        foreach ($modules as $name => $info) {
            /**
             * Collect modules (excluding modules in "core" code-pool)
             * or include if query param include_core set
             */
            if ($info['codePool'] != 'core' || $this->_isIncludeCoreModules()) {
                $data[$name] = array(
                    'active'   => $info['active'],
                    'codePool' => $info['codePool'],
                    'version'  => $info['version'],
                );
            }
        }
        return $data;
    }

    /**
     * Get status of including Magento Core modules
     *
     * @return bool
     */
    protected function _isIncludeCoreModules()
    {
        return (bool) $this->_getQuery('include_core');
    }
}
