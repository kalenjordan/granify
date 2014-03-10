<?php
/**
 * Class of Logger
 *
 * @category    Granify
 * @package     Granify_Sales
 * @abstract
 */
class Granify_Sales_Model_Logger_HoneyBadger extends Varien_Object
{
    /**#@+
     * API credentials to HoneyBadger
     */
    const XML_PATH_API_URI = 'default/granify/logger/honeybadger/api_uri';
    const XML_PATH_API_KEY = 'default/granify/logger/honeybadger/api_key';
    /**#@-*/

    /**
     * Content type JSON
     */
    const APPLICATION_JSON = 'application/json';

    /**
     * Default status response
     */
    const DEFAULT_RESPONSE_STATUS = 201;

    /**
     * HTTP client
     *
     * @var Zend_Http_Client
     */
    protected $_httpClient;

    /**
     * Set options
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (isset($options['httpClient'])) {
            $this->_setHttpClient($options['httpClient']);
        }
    }

    /**
     * Log exception
     *
     * @param Exception $e
     */
    public function logException(Exception $e)
    {
        $this->setData(array(
            'notifier' => $this->_getNotifierData(),
            'server' => $this->_getServerData(),
            'request' => $this->_getRequestData(),
            'error' => $this->_getErrorData($e),
        ));
        $this->_request();
    }

    /**
     * @return array
     */
    protected function _getNotifierData()
    {
        /** @var $helper Granify_Sales_Helper_Data */
        $helper = $this->_getHelper('granify_sales');
        return array(
            'name' => 'Magento ' . $helper->getMagentoVersion(),
            'url' => $this->_getBaseUrl(),
            'version' => $helper->getPackageVersion(),
        );
    }

    /**
     * @return array
     */
    protected function _getServerData()
    {
        return array(
            'project_root' => array(
                'path' => $this->_getProjectRoot()
            ),
            'environment_name' => $this->_getEnvironmentMode(),
            'hostname' => $this->_getRequest()->getHttpHost(false)
        );
    }

    /**
     * Get request data
     *
     * @return array
     */
    protected function _getRequestData()
    {
        return array(
            'url' => $this->_getFullRequestUri(),
            'params' => $this->_getRequestParams(),
            'context' => array(
                'is_admin' => (int) $this->_getApp()->getStore()->isAdmin(),
            )
        );
    }

    /**
     * Get error data
     *
     * @param Exception $e
     * @return array
     */
    protected function _getErrorData(Exception $e)
    {
        return array(
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'backtrace' => $this->_getFormattedExceptionTrace($e),
        );
    }

    /**
     * Get formatted trace
     *
     * @param Exception $e
     * @return array
     */
    protected function _getFormattedExceptionTrace(Exception $e)
    {
        $trace = $e->getTrace();
        foreach ($trace as &$item) {
            $item = array(
                'file'   => $item['file'],
                'number' => $item['line'],
                'method' => $item['function'],
            );
        }
        return $trace;
    }

    /**
     * Get request params
     *
     * @return array
     */
    public function _getRequestParams()
    {
        $params = $this->_getRequest()->getParams();

        $params['module'] = $this->_getApp()->getRequest()->getModuleName();
        $params['controller'] = $this->_getApp()->getRequest()->getControllerName();
        $params['action'] = $this->_getApp()->getRequest()->getActionName();
        $params['___method'] = $this->_getApp()->getRequest()->getMethod();
        $params['___SERVER'] = $this->_getApp()->getRequest()->getServer();

        return $params;
    }

    /**
     * get full request URI
     *
     * @return string
     */
    protected function _getFullRequestUri()
    {
        $host = $this->_getRequest()->getHttpHost(false);
        $arr = explode($host, $this->_getBaseUrl());
        return $arr[0] . $host . $this->_getRequest()->getRequestUri();
    }

    /**
     * Get project root
     *
     * @return string
     */
    protected function _getProjectRoot()
    {
        $host = $this->_getRequest()->getHttpHost(false);
        $arr = explode($host, $this->_getBaseUrl());

        if (isset($arr[1])) {
            return $arr[1];
        } else {
            return '/';
        }
    }

    /**
     * Get environment mode (development/production)
     *
     * @return string
     */
    protected function _getEnvironmentMode()
    {
        return Mage::getIsDeveloperMode() ? 'development' : 'production';
    }

    /**
     * Send log data
     *
     * @return $this
     * @throws Granify_Sales_Model_Logger_Exception
     */
    protected function _request()
    {
        $response = $this->getHttpClient()
            ->setRawData(
                Zend_Json_Encoder::encode($this->getData())
            )
            ->request();

        if ((int)$response ->getStatus() !== self::DEFAULT_RESPONSE_STATUS) {
            throw new Granify_Sales_Model_Logger_Exception('Invalid response status from HoneyBadger.');
        }
        return $this;
    }

    /**
     * Get API URI
     *
     * @return string
     */
    public function getApiUri()
    {
        return (string) $this->_getApp()->getConfig()->getNode(self::XML_PATH_API_URI);
    }

    /**
     * Get API key
     *
     * @return string
     */
    public function getApiKey()
    {
        return (string) $this->_getApp()->getConfig()->getNode(self::XML_PATH_API_KEY);
    }

    /**
     * Set Http client
     *
     * @param Zend_Http_Client $httpClient
     * @return $this
     */
    protected function _setHttpClient(Zend_Http_Client $httpClient)
    {
        $this->_httpClient = $httpClient;
        return $this;
    }

    /**
     * Get HTTP client
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if (null === $this->_httpClient) {
            $this->_httpClient = $this->_getDefaultHttpClient();
            $this->_configureHttpClient();
        }
        return $this->_httpClient;
    }

    /**
     * Configure HTTP client
     *
     * @return $this
     */
    protected function _configureHttpClient()
    {
        $this->_httpClient->setUri($this->getApiUri())
            ->setConfig(array(
                'maxredirects' => 0,
                'timeout'      => 30
            ))
            ->setHeaders(array(
                'X-API-Key'                     => $this->getApiKey(),
                'Accept'                        => self::APPLICATION_JSON,
                Zend_Http_Client::CONTENT_TYPE  => self::APPLICATION_JSON,
            ))
            ->setMethod(Zend_Http_Client::POST);
        return $this;
    }

    /**
     * Init default client
     *
     * @return Zend_Http_Client
     */
    protected function  _getDefaultHttpClient()
    {
        return new Zend_Http_Client();
    }

    /**
     * Get base url
     *
     * @return string
     */
    protected function _getBaseUrl()
    {
        return $this->_getApp()->getStore()->getBaseUrl();
    }

    /**
     * @return Mage_Core_Controller_Request_Http
     */
    protected function _getRequest()
    {
        return $this->_getApp()->getRequest();
    }

    /**
     * Get Mage::app() model
     *
     * @codeCoverageIgnore
     * @return Mage_Core_Model_App
     */
    protected function _getApp()
    {
        return Mage::app();
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
