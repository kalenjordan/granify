<?php
/**
 * Factory of resources
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Resource_Factory
{
    /**
     * Make resource
     *
     * @param int $version
     * @param Zend_Controller_Request_Http $request
     * @param array $resourceOptions
     * @throws Granify_Sales_Model_Api_Exception
     * @return Granify_Sales_Model_Api_Resource_Abstract
     */
    public function makeResource($version, Zend_Controller_Request_Http $request, array $resourceOptions)
    {
        $resourceName = $request->getParam('api_resource');
        $resourceConfig = $this->_getResourceConfig($resourceName);
        if ($resourceConfig) {
            $object = $this->_matchResource($version, $resourceConfig, $resourceOptions);
            if ($object) {
                return $object;
            }
        }
        throw new Granify_Sales_Model_Api_Exception(
            sprintf('Resource "%s" not found.', $resourceName),
            Granify_Sales_Model_Api_Dispatcher::CODE_NOT_FOUND
        );
    }

    /**
     * Get available resources
     *
     * @return array
     */
    protected function _getResourcesMap()
    {
        //TODO Refactor this method, move to config
        return array(
            'test'      => array(
                'path' => 'granify_sales/api_resource_test_v%s',
                'version' => '1,2'
            ),
            'version'   => array(
                'path' => 'granify_sales/api_resource_version_v%s',
                'version' => '1',
            ),
            'shopInfo'   => array(
                'path' => 'granify_sales/api_resource_shopInfo_v%s',
                'version' => '1,2',
            ),
            'stores'   => array(
                'path' => 'granify_sales/api_resource_store_v%s',
                'version' => '1'
            ),
        );
    }

    /**
     * Get resource config
     *
     * @param string $resourceName
     * @return array
     */
    protected function _getResourceConfig($resourceName)
    {
        $existResources = $this->_getResourcesMap();
        return isset($existResources[$resourceName]) ? $existResources[$resourceName] : null;
    }

    /**
     * Match resource by version
     *
     * @param string $version
     * @param array $resourceConfig
     * @param array $resourceOptions
     * @return Granify_Sales_Model_Api_Resource_Abstract
     */
    protected function _matchResource($version, $resourceConfig, $resourceOptions)
    {
        $resource = null;
        $versionsAllowed = explode(',', $resourceConfig['version']);
        while ($version && !$resource) {
            if (in_array($version, $versionsAllowed)) {
                $resourceClassPath = sprintf($resourceConfig['path'], $version);
                $resource = $this->_getModel($resourceClassPath, $resourceOptions);
                break;
            }
            $version--;
        }
        return $resource;
    }

    /**
     * Get model from Mage model factory
     *
     * @param string $classPath
     * @param array|mixed $options
     * @return Mage_Core_Model_Abstract
     */
    protected function _getModel($classPath, $options = array())
    {
        return Mage::getModel($classPath, $options);
    }
}
