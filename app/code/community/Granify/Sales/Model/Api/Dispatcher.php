<?php
/**
 * API dispatcher class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Dispatcher
{
    //region Constants
    /**
     * API version
     */
    const VERSION = 3;

    /**
     * Status code 200 "OK"
     */
    const CODE_OK = 200;

    /**
     * Status code 201 "Created"
     */
    const CODE_CREATED = 201;

    /**
     * Status code 204 "No content"
     */
    const CODE_NO_CONTENT = 204;

    /**
     * Status code 400 "Bad Request"
     */
    const CODE_BAD_REQUEST = 400;

    /**
     * Status code 401 "Unauthorized"
     */
    const CODE_UNAUTHORIZED = 401;

    /**
     * Status code 404 "Not found"
     */
    const CODE_NOT_FOUND = 404;

    /**
     * Status code 405 "Method not allowed"
     */
    const CODE_METHOD_NOT_ALLOWED = 405;

    /**
     * Status code 406 "Not Acceptable"
     */
    const CODE_NOT_ACCEPTABLE = 406;

    /**
     * Status code 500 "Internal error"
     */
    const CODE_INTERNAL_ERROR = 500;

    /**
     * Status code 501 "Not Implemented"
     */
    const CODE_NOT_IMPLEMENTED = 501;

    /**
     * Version header name
     */
    const HEADER_VERSION = 'VERSION';
    //endregion

    /**
     * Request
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Response
     *
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response;

    /**
     * Helper
     *
     * @var Granify_Sales_Helper_Data
     */
    protected $_helper;

    /**
     * API version
     *
     * @var int
     */
    protected $_version;

    /**
     * Request method
     *
     * @var string
     */
    protected $_method;

    /**
     * Requested resource name
     *
     * @var string
     */
    protected $_resourceName;

    /**
     * Constructor
     *
     * Set options
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (isset($options['request'])) {
            $this->setRequest($options['request']);
        }
        if (isset($options['response'])) {
            $this->setResponse($options['response']);
        }
        if (isset($options['helper'])) {
            $this->setHelper($options['helper']);
        }
    }

    /**
     * Set request
     *
     * @param Zend_Controller_Request_Http $request
     * @return Granify_Sales_Model_Api_Dispatcher
     */
    public function setRequest(Zend_Controller_Request_Http $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Get request
     *
     * @return \Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set response
     *
     * @param Zend_Controller_Response_Abstract $response
     * @return Granify_Sales_Model_Api_Dispatcher
     */
    public function setResponse(Zend_Controller_Response_Abstract $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Get response
     *
     * @return \Zend_Controller_Response_Abstract
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Set helper
     *
     * @param Granify_Sales_Helper_Data $helper
     * @return Granify_Sales_Model_Api_Dispatcher
     */
    public function setHelper(Granify_Sales_Helper_Data $helper)
    {
        $this->_helper = $helper;
        return $this;
    }

    /**
     * Get helper
     *
     * @return \Zend_Controller_Response_Abstract
     */
    public function getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = $this->_getHelper('granify_sales');
        }
        return $this->_helper;
    }

    /**
     * Get API requested version
     *
     * @return int
     * @throws Granify_Sales_Model_Api_Exception
     */
    protected function _getVersion()
    {
        if (null === $this->_version) {
            $param = (int) $this->getRequest()->getHeader(self::HEADER_VERSION);
            if (!$param) {
                $param = self::VERSION;
            } elseif ($param > self::VERSION) {
                throw new Granify_Sales_Model_Api_Exception(
                    sprintf('Unknown version %s.', $param),
                    self::CODE_BAD_REQUEST
                );
            }
            $this->_version = $param;
        }
        return $this->_version;
    }

    /**
     * Process request
     *
     * @return Granify_Sales_Model_Api_Dispatcher
     */
    public function dispatch()
    {
        try {
            $processor = $this->_getResourceProcessor();
            $result = $processor->process();
            $code = $this->_prepareStatusCode(
                $processor->getResource()->getStatusCode(),
                false
            );
        } catch (Mage_Core_Exception $e) {
            $code = $this->_prepareStatusCode($e->getCode());
            $result = array('businessException' => $e->getMessage());
        } catch (Granify_Sales_Model_Api_Exception $e) {
            $code = $this->_prepareStatusCode($e->getCode());
            $result = array('exception' => $e->getMessage());
        } catch (Exception $e) {
            $this->_logException($e);
            $this->_logExceptionByLogger($e);
            $code = self::CODE_INTERNAL_ERROR;
            $result = array('exception' => 'An error occurred while processing the request.');
        }
        $this->_makeResponse($result, $code);
        return $this;
    }

    /**
     * Get resource processor
     *
     * @return Granify_Sales_Model_Api_Processor
     */
    protected function _getResourceProcessor()
    {
        return $this->_getModel(
            'granify_sales/api_processor',
            array(
                'request' => $this->getRequest(),
                'version' => $this->_getVersion()
            )
        );
    }

    /**
     * Make response
     *
     * @param int $code     Response status code
     * @param string $result
     * @return Granify_Sales_Model_Api_Dispatcher
     */
    protected function _makeResponse($result, $code)
    {
        $this->_setResponseHeaders();
        $this->getResponse()
            ->setHttpResponseCode($code)
            ->appendBody($this->_renderResult($result));
        return $this;
    }

    /**
     * Get safety status code from exception or from resource
     *
     * @param int|null $code        Status code from resource or exception
     * @param bool $isException     Is status from exception?
     * @return int
     */
    protected function _prepareStatusCode($code = null, $isException = true)
    {
        if ($isException) {
            $code = $this->_isValidStatus($code) ? $code : self::CODE_BAD_REQUEST;
        } else {
            $code = $this->_isValidStatus($code) ? $code : self::CODE_OK;
        }
        return $code;
    }

    /**
     * Check valid status
     *
     * @param string $code
     * @return bool
     */
    protected function _isValidStatus($code)
    {
        return is_int($code) && 100 <= $code && 599 >= $code;
    }

    /**
     * Set response headers
     *
     * @return Granify_Sales_Model_Api_Dispatcher
     */
    protected function _setResponseHeaders()
    {
        $this->getResponse()
            ->setHeader(Zend_Http_Client::CONTENT_TYPE, $this->_getAcceptContentType())
            ->setHeader(self::HEADER_VERSION, $this->_getVersion());
        return $this;
    }

    /**
     * Get accepted content type
     *
     * @return string
     * @todo Implement accept mechanism
     */
    protected function _getAcceptContentType()
    {
        return 'application/json';
    }

    /**
     * Render result
     *
     * @param array|object $result
     * @return string
     * @todo Implement renderer
     */
    protected function _renderResult($result)
    {
        return json_encode($result);
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
     * Log exception
     *
     * @param Exception $e
     * @return Granify_Sales_Model_Api_Dispatcher
     * @codeCoverageIgnore
     */
    protected function _logException(Exception $e)
    {
        Mage::logException($e);
        return $this;
    }

    /**
     * Get model from Mage model factory
     *
     * @param string $classPath
     * @param array $arguments
     * @return Mage_Core_Model_Abstract
     * @codeCoverageIgnore
     */
    protected function _getModel($classPath, $arguments = array())
    {
        return Mage::getModel($classPath, $arguments);
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
}
