<?php
/**
 * API request processor class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Processor
{
    /**
     * Requested resource
     *
     * @var Granify_Sales_Model_Api_Resource_Abstract
     */
    protected $_resource;

    /**
     * Method request
     */
    const METHOD_GET    = 'GET';

    /**
     * Method request
     */
    const METHOD_PUT    = 'PUT';

    /**
     * Method request
     */
    const METHOD_POST   = 'POST';

    /**
     * Method request
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * Request
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * API version
     *
     * @var int
     */
    protected $_version;

    /**
     * List of applicable methods
     *
     * @var array
     */
    protected $_applicableMethods = array(
        self::METHOD_GET,
        self::METHOD_PUT,
        self::METHOD_POST,
        self::METHOD_DELETE,
    );

    /**
     * Construct
     *
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        if (!isset($options['request']) && !($options['request'] instanceof Zend_Controller_Request_Http)) {
            throw new Exception('Unknown request object.');
        }
        if (!isset($options['version']) && !is_int($options['version'])) {
            throw new Exception('Unknown version value.');
        }
        $this->_request = $options['request'];
        $this->_version = $options['version'];
    }

    /**
     * Process request
     *
     * @return mixed
     */
    public function process()
    {
        $this->_validateMethod();
        $this->_processRawBody();
        return $this->_callResource();
    }

    /**
     * Validate request method
     *
     * @return Granify_Sales_Model_Api_Processor
     * @throws Granify_Sales_Model_Api_Exception
     */
    protected function _validateMethod()
    {
        if (!in_array(strtoupper($this->_request->getMethod()), $this->_applicableMethods)) {
            throw new Granify_Sales_Model_Api_Exception(
                sprintf('Unknown method "%s" is not applicable for API.', $this->_request->getMethod()),
                Granify_Sales_Model_Api_Dispatcher::CODE_METHOD_NOT_ALLOWED
            );
        }
        return $this;
    }

    /**
     * Is request method for create/update data?
     *
     * @return bool
     */
    protected function _isUpdateDataMethod()
    {
        return self::METHOD_PUT == $this->_request->getMethod()
            || self::METHOD_POST == $this->_request->getMethod();
    }

    /**
     * Process raw body and set to params as param "data"
     *
     * @return Granify_Sales_Model_Api_Dispatcher
     */
    protected function _processRawBody()
    {
        if ($this->_isUpdateDataMethod()) {
            /** @var $interpreter Granify_Sales_Model_Api_Interpreter */
            $interpreter = $this->_getInterpreter();
            $this->_request->setParam(
                'data',
                $interpreter->interpret(
                    $this->_request->getHeader(Zend_Http_Client::CONTENT_TYPE),
                    $this->_request->getRawBody()
                )
            );
        }
        return $this;
    }

    /**
     * Get interpreter model
     *
     * @return Granify_Sales_Model_Api_Interpreter
     */
    protected function _getInterpreter()
    {
        return $this->_getModel('granify_sales/api_interpreter');
    }

    /**
     * Process resource model
     *
     * @return mixed
     */
    protected function _callResource()
    {
        return $this->getResource()
            ->{strtolower($this->_request->getMethod())}();
    }

    /**
     * Init resource
     *
     * @return Granify_Sales_Model_Api_Resource_Abstract
     */
    public function getResource()
    {
        if (null === $this->_resource) {
            $this->_resource = $this->_getResourceFactory()->makeResource(
                $this->_version,
                $this->_request,
                $this->_getResourceOptions()
            );
        }
        return $this->_resource;
    }

    /**
     * Get specific resource options
     *
     * @return array
     */
    protected function _getResourceOptions()
    {
        return array(
            'version' => $this->_version,
            'request' => $this->_request,
        );
    }

    /**
     * Get resource factory
     *
     * @return Granify_Sales_Model_Api_Resource_Factory
     */
    protected function _getResourceFactory()
    {
        return $this->_getModel('granify_sales/api_resource_factory');
    }

    /**
     * Get model from Mage model factory
     *
     * @param string $classPath
     * @return Mage_Core_Model_Abstract
     */
    protected function _getModel($classPath)
    {
        return Mage::getModel($classPath);
    }
}
