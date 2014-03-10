<?php
/**
 * API base abstract resources class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
abstract class Granify_Sales_Model_Api_Resource_Abstract
{
    /**
     * Code for use default store code
     */
    const DEFAULT_STORE_CODE = '___default';

    /**
     * Status code
     *
     * @var int
     */
    protected $_statusCode;

    /**
     * Request
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Authorization object
     *
     * @var Granify_Sales_Model_Api_Auth
     */
    protected $_auth;

    /**
     * Input params options
     *
     * @var array
     */
    protected $_inputOptions;

    /**
     * Output params options
     *
     * @var array
     */
    protected $_outputOptions;

    /**
     * API version
     *
     * @var int
     */
    protected $_version;

    /**
     * Store
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Constructor
     */
    public function __construct(array $options)
    {
        $this->_setOptions($options);
        $this->_authenticate();
        $this->_init();
    }

    /**
     * Set options
     *
     * @param array $options
     * @throws Exception
     */
    protected function _setOptions(array $options)
    {
        if (!isset($options['request']) || !($options['request'] instanceof Zend_Controller_Request_Http)) {
            throw new Exception('Invalid request object.');
        }
        $this->_setRequest($options['request']);

        if (!isset($options['version']) || !is_int($options['version'])) {
            throw new Exception('Invalid version.');
        }
        $this->_setVersion($options['version']);

        if (isset($options['auth'])) {
            $this->_setAuth($options['auth']);
        }
    }

    /**
     * Initializing
     */
    protected function _init()
    {
        $this->_initStore();
    }

    /**
     * Initialize store
     *
     * @throws Granify_Sales_Model_Api_Exception
     */
    protected function _initStore()
    {
        /**
         * If store code equal ___default then skip validation and initialized store
         */
        $storeCode = $this->_getQuery('___store');
        $store = $this->_getApp()->getStore();
        if ($storeCode !== self::DEFAULT_STORE_CODE && $storeCode !== $store->getCode()) {
            throw new Granify_Sales_Model_Api_Exception(
                sprintf('Store "%s" not found.', $storeCode),
                Granify_Sales_Model_Api_Dispatcher::CODE_NOT_ACCEPTABLE
            );
        }
        $this->_store = $store;
    }

    /**
     * Authorizing
     *
     * @return bool
     * @throws Granify_Sales_Model_Api_Exception
     */
    protected function _authenticate()
    {
        if (!$this->_getAuth()->authenticate($this->_request, array('helper' => $this->_getHelper('granify_sales')))) {
            throw new Granify_Sales_Model_Api_Exception(
                'Unauthorized.',
                Granify_Sales_Model_Api_Dispatcher::CODE_UNAUTHORIZED
            );
        }
        return true;
    }

    /**
     * Set auth model
     *
     * @param Granify_Sales_Model_Api_Auth_Interface $auth
     * @return Granify_Sales_Model_Api_Dispatcher
     */
    public function _setAuth(Granify_Sales_Model_Api_Auth_Interface $auth)
    {
        $this->_auth = $auth;
        return $this;
    }

    /**
     * Get auth model
     *
     * @return Granify_Sales_Model_Api_Auth
     */
    public function _getAuth()
    {
        if (null === $this->_auth) {
            $this->_auth = $this->_getModel('granify_sales/api_auth');
        }
        return $this->_auth;
    }

    /**
     * Set request
     *
     * @param Zend_Controller_Request_Http $request
     * @return Granify_Sales_Model_Api_Resource_Abstract
     */
    protected function _setRequest(Zend_Controller_Request_Http $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Set API version
     *
     * @param int $version
     * @return Granify_Sales_Model_Api_Resource_Abstract
     */
    protected function _setVersion($version)
    {
        $this->_version = $version;
        return $this;
    }

    /**
     * Get API version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Get request param
     *
     * @param string $key
     * @param mixed $default
     * @return string
     */
    protected function _getParam($key, $default = null)
    {
        return $this->_request->getParam($key, $default);
    }

    /**
     * Get request param
     *
     * @param string $key
     * @param mixed $default
     * @return string
     */
    protected function _getQuery($key, $default = null)
    {
        return $this->_request->getQuery($key, $default);
    }

    /**
     * Get resource ID
     *
     * @return int|null
     */
    protected function _getId()
    {
        return $this->_getParam('api_resource_id');
    }

    /**
     * Get post/put data
     *
     * @param string|null $key
     * @return array|null
     */
    protected function _getPostData($key = null)
    {
        if ($key) {
            $data = $this->_getParam('data');
            return isset($data[$key]) ? $data[$key] : null;
        } else {
            return $this->_getParam('data');
        }
    }

    /**
     * Method get
     *
     * @return array
     * @throws Granify_Sales_Model_Api_Exception
     */
    public function get()
    {
        if (null !== $this->_getId()) {
            return $this->_get();
        } else {
            return $this->_getCollection();
        }
    }

    /**
     * Method get
     *
     * @return array    Resource data
     * @throws Granify_Sales_Model_Api_Exception
     */
    protected function _get()
    {
        throw $this->_getExceptionNotImplementedMethod(
            Granify_Sales_Model_Api_Processor::METHOD_GET
        );
    }

    /**
     * Method get collection
     *
     * @return array    Resources collection data
     * @throws Granify_Sales_Model_Api_Exception
     */
    protected function _getCollection()
    {
        throw new Granify_Sales_Model_Api_Exception(
            sprintf(
                'Method GET is not supported for requested resource collection. Version %s.',
                Granify_Sales_Model_Api_Processor::METHOD_GET,
                $this->getVersion()
            ),
            Granify_Sales_Model_Api_Dispatcher::CODE_METHOD_NOT_ALLOWED
        );

    }

    /**
     * Get exception with status 404 when resource not found
     *
     * @return Granify_Sales_Model_Api_Exception
     */
    protected function _getNotFoundException()
    {
        return new Granify_Sales_Model_Api_Exception(
            'Resource not found by requested URI.',
            Granify_Sales_Model_Api_Dispatcher::CODE_NOT_FOUND
        );
    }

    /**
     * Method put
     *
     * @return bool     Result of method
     * @throws Granify_Sales_Model_Api_Exception
     */
    public function put()
    {
        throw $this->_getExceptionNotImplementedMethod(
            Granify_Sales_Model_Api_Processor::METHOD_PUT
        );
    }

    /**
     * Method post
     *
     * @return bool     Result of method
     * @throws Granify_Sales_Model_Api_Exception
     */
    public function post()
    {
        throw $this->_getExceptionNotImplementedMethod(
            Granify_Sales_Model_Api_Processor::METHOD_POST
        );
    }

    /**
     * Method delete
     *
     * @return bool     Result of method
     * @throws Granify_Sales_Model_Api_Exception
     */
    public function delete()
    {
        throw $this->_getExceptionNotImplementedMethod(
            Granify_Sales_Model_Api_Processor::METHOD_DELETE
        );
    }

    /**
     * Get exception for does not implemented method
     *
     * @param string $method
     * @return Granify_Sales_Model_Api_Exception
     */
    protected function _getExceptionNotImplementedMethod($method)
    {
        return new Granify_Sales_Model_Api_Exception(
            sprintf(
                'Method %s is not supported for requested resource. Version %s.',
                strtoupper($method),
                $this->getVersion()
            ),
            Granify_Sales_Model_Api_Dispatcher::CODE_METHOD_NOT_ALLOWED
        );
    }

    /**
     * Get Mage model
     *
     * @param string $classPath
     * @return Mage_Core_Model_Abstract
     * @codeCoverageIgnore
     */
    protected function _getModel($classPath)
    {
        return Mage::getModel($classPath);
    }

    /**
     * Get helper from Mage helper factory
     *
     * @param string $classPath
     * @return Mage_Core_Helper_Abstract
     * @codeCoverageIgnore
     */
    protected function _getHelper($classPath)
    {
        return Mage::helper($classPath);
    }

    /**
     * Get App model
     *
     * @return Mage_Core_Model_App
     * @codeCoverageIgnore
     */
    protected function _getApp()
    {
        return Mage::app();
    }

    /**
     * Filter and validate data
     *
     * @param array $data
     * @param bool $skipRequiring   Skip required fields validation
     * @return array
     */
    protected function _filterInput(array $data, $skipRequiring = false)
    {
        /** @var $input Granify_Sales_Model_Api_Resource_Input */
        $input = $this->_getModel('granify_sales/api_resource_input');
        $input->setOptions(
            array(
                'rules'          => $this->_inputOptions,
                'skip_requiring' => $skipRequiring,
            )
        );
        return $input->process($data);
    }

    /**
     * Filter output
     *
     * @param array $data
     * @return array
     */
    protected function _filterOutput(array $data)
    {
        $filtered = array();
        foreach ($this->_outputOptions as $name) {
            if (is_string($name) && array_key_exists($name, $data)) {
                if (isset($this->_outputOptions['__map'][$name])) {
                    $newName = $this->_outputOptions['__map'][$name];
                } else {
                    $newName = $name;
                }
                $filtered[$newName] = $data[$name];
            }
        }
        return $filtered;
    }

    /**
     * Set HTTP status code
     *
     * @param int $code
     * @return Granify_Sales_Model_Api_Resource_Abstract
     */
    protected function _setStatusCode($code)
    {
        $this->_statusCode = (int) $code;
        return $this;
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }
}
