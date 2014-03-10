<?php
/**
 * API end point controller class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_ApiController extends Mage_Core_Controller_Front_Action
{
    /**
     * Get singleton of Checkout Session Model
     *
     * @return Granify_Sales_Model_Api_Dispatcher
     */
    protected function _getDispatcher()
    {
        return $this->_getSingleton(
            'granify_sales/api_dispatcher',
            array(
                'request' => $this->getRequest(),
                'response' => $this->getResponse(),
            )
        );
    }

    /**
     * API enter point
     */
    public function indexAction()
    {
        try {
            $this->_getDispatcher()->dispatch();
        } catch (Exception $e) {
            $this->_logException($e);
            $this->_logExceptionByLogger($e);
            $this->getResponse()->setHttpResponseCode(Granify_Sales_Model_Api_Dispatcher::CODE_INTERNAL_ERROR)
                ->appendBody('An error occurred out of dispatcher while processing the request.');
        }
    }

    /**
     * Log exception
     *
     * @param Exception $e
     * @return Granify_Sales_ApiController
     * @codeCoverageIgnore
     */
    protected function _logException(Exception $e)
    {
        Mage::logException($e);
        return $this;
    }

    /**
     * Log exception via logger
     *
     * @param Exception $e
     * @return Granify_Sales_ApiController
     */
    protected function _logExceptionByLogger(Exception $e)
    {
        if (!($e instanceof Granify_Sales_Model_Logger_Exception)) {
            /** @var $helper Granify_Sales_Helper_Data */
            $helper = $this->_getHelper('granify_sales');
            $helper->getLogger()->logException($e);
        }
        return $this;
    }

    /**
     * Get Mage singleton
     *
     * @param string $modelClass
     * @param array $arguments
     * @return Mage_Core_Model_Abstract
     * @codeCoverageIgnore
     */
    protected function _getSingleton($modelClass, array $arguments = array())
    {
        return Mage::getSingleton($modelClass, $arguments);
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
}
