<?php
/**
 * Granify Sales config helper
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Helper_Config extends Granify_Sales_Helper_BaseAbstract
{
    /**
     * Update application config data
     *
     * @param string|int|array $data    Value for config node. If it's array then should be config groups data
     * @param string $path              Config path with the form "section/group/node"
     * @param bool $cleanCache          If TRUE refresh cache
     * @param bool $refreshConfig       If TRUE refresh config object
     * @return Granify_Sales_Helper_Config
     * @throws Exception
     */
    public function update($data, $path, $cleanCache = true, $refreshConfig = false)
    {
        $toSave = $this->_getConfigData($data, $path);
        $this->_saveConfig($toSave);

        if ($cleanCache) {
            if ($refreshConfig) {
                $this->_refreshConfig();
            }
            //refresh cache
            $this->_cleanConfigCache();
        }
        return $this;
    }

    /**
     * Save config
     *
     * @param array $toSave
     * @return Granify_Sales_Helper_Config
     */
    protected function _saveConfig(array $toSave)
    {
        /** @var $config Mage_Adminhtml_Model_Config_Data */
        $config = $this->_getModel('adminhtml/config_data');
        $config->addData($toSave);
        //set scope
        $config->setStore($this->_getApp()->getStore()->getCode()); //set store scope
        $config->setWebsite($this->_getApp()->getWebsite()->getCode()); //set website scope
        $config->save();
        return $this;
    }

    /**
     * Get config data for saving to Config Data model
     *
     * @param string|array $data
     * @param string $path
     * @return array
     * @throws Exception
     */
    protected function _getConfigData($data, $path)
    {
        if (!is_array($data)) {
            $data = array($path => $data);
        }

        $section = $this->_getSection($data);
        $groups = array();
        foreach ($data as $path => $value) {
            $this->_addGroupToGroups($groups, $section, $path, $value);
        }
        return array('section' => $section, 'groups' => $groups);
    }

    /**
     * Get section from data
     *
     * @param array $data
     * @return string
     * @throws Exception
     */
    protected function _getSection($data)
    {
        reset($data);
        $path = key($data);
        list($section) = explode('/', $path);
        if (!$section) {
            throw new Exception(sprintf('Section inside path "%s" is empty.', $path));
        }
        return $section;
    }

    /**
     * Add group data to groups
     *
     * @param array $groups
     * @param string $toSection
     * @param string $path
     * @param string|int|null $value
     * @return Granify_Sales_Helper_Config
     * @throws Exception
     */
    protected function _addGroupToGroups(array &$groups, $toSection, $path, $value)
    {
        list($section, $group, $node) = explode('/', $path);
        if ($section != $toSection) {
            throw new Exception('Able to save to only one section.');
        }
        if (!$group || !$node) {
            throw new Exception(
                sprintf('Config path have to looks like "section/group/node" but it has "%s"', $path)
            );
        }
        $groups[$group]['fields'][$node]['value'] = $value;
        return $this;
    }

    /**
     * Refresh config object
     *
     * @return Granify_Sales_Helper_Config
     */
    protected function _refreshConfig()
    {
        $this->_getApp()->getConfig()->reinit();
        $this->_getApp()->reinitStores();
        return $this;
    }

    /**
     * Clean config cache
     *
     * @return bool
     */
    protected function _cleanConfigCache()
    {
        return $this->_getApp()->cleanCache(Mage_Core_Model_Config::CACHE_TAG);
    }
}
