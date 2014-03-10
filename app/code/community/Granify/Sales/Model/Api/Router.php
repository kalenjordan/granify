<?php
/**
 * API Router class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Router extends Mage_Core_Controller_Varien_Router_Standard
{
    /**
     * API route
     *
     * @var Zend_Controller_Router_Route
     */
    protected $_route;

    /**
     * Status of ability work with Granify service
     *
     * @var bool
     */
    protected $_isAble;

    /**
     * Get route
     *
     * @return Zend_Controller_Router_Route
     */
    protected function _getRoute()
    {
        if (null === $this->_route) {
            $this->_route = new Zend_Controller_Router_Route(
                '/granify_api/:api_resource/:api_resource_id',
                array(
                    'module'            => 'granify_sales',
                    'controller'        => 'api',
                    'action'            => 'index',
                    'api_resource'      => '',
                    'api_resource_id'   => null,
                )
            );
        }
        return $this->_route;
    }

    /**
     * Modify request and set to no-route action
     * If store is admin and specified different admin front name,
     * change store to default (Possible when enabled Store Code in URL)
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        //TODO ADD rules
//        if (!$this->_isAble()) {
//            return false;
//        }

        if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }

        $params = $this->_getRoute()->match($request->getPathInfo());
        if (!$params) {
            return false;
        }

        $this->_setRequestParams($request, $params);

        //TODO get module name from config
        $realModule = 'Granify_Sales';

        //set route name
        //TODO get router name from config
        $request->setRouteName('granify_api');

        //following code was copied from parent

        $controller = $params['controller'];
        $action = $params['action'];

        $controllerClassName = $this->_validateControllerClassName(
            $realModule,
            $controller
        );

        if (!$controllerClassName) {
            return false;
        }

        //checking if this place should be secure
        //TODO get code module name from config
        $this->_checkShouldBeSecure($request, '/granify_sales/' . $controller . '/' . $action);

        // instantiate controller class
        $controllerInstance = $this->_getControllerInstance(
            $controllerClassName,
            $request,
            $this->getFront()->getResponse()
        );

        if (!$controllerInstance->hasAction($action)) {
            return false;
        }

        // dispatch action
        $request->setDispatched(true);
        $controllerInstance->dispatch($action);
        return true;
    }

    /**
     * Get controller instance
     *
     * @param string $controllerClassName
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Controller_Response_Http $response
     * @return Mage_Core_Controller_Front_Action
     */
    protected function _getControllerInstance($controllerClassName, $request, $response)
    {
        return Mage::getControllerInstance(
            $controllerClassName,
            $request,
            $response
        );
    }

    /**
     * Set request params
     *
     * @param Zend_Controller_Request_Http $request
     * @param array $params
     * @return Granify_Sales_Model_Api_Router
     */
    public function _setRequestParams(Zend_Controller_Request_Http $request, array $params)
    {
        $request->setModuleName($params['module'])
            ->setControllerName($params['controller'])
            ->setActionName($params['action'])
            ->setParam('api_resource', $params['api_resource'])
            ->setParam('api_resource_id', $params['api_resource_id']);
        return $this;
    }

    /**
     * Get status of ability work with Granify service
     *
     * @return bool
     */
    protected function _isAble()
    {
        if (null === $this->_isAble) {
            /** @var $helper Granify_Sales_Helper_Data */
            $helper = $this->_getHelper('granify_sales');
            $this->_isAble = $helper->isAble();
        }
        return $this->_isAble;
    }

    /**
     * Get helper by class path
     *
     * @param string $classPath
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper($classPath)
    {
        return Mage::helper($classPath);
    }
}
