<?php
/**
 * Transport model for use Zend HTTP client for request to granify server
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Sender_Transport
{
    /**
     * Default content type
     */
    const CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    /**
     * Header name of hostname
     */
    const HEADER_HOSTNAME = 'SITE-DOMAIN';

    /**
     * Header name of site ID
     */
    const HEADER_SITE_ID = 'SITE-ID';

    /**
     * HTTP client
     *
     * @var Zend_Http_Client
     */
    protected $_httpClient;

    /**
     * Helper
     *
     * @var Granify_Sales_Helper_Data
     */
    protected $_helper;

    /**
     * Constructor. Set HTTP client and helper if its not null
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (isset($options['httpClient'])) {
            $this->_setHttpClient($options['httpClient']);
        }
        if (!isset($options['helper'])) {
            $options['helper'] = Mage::helper('granify_sales');
        }
        $this->_setHelper($options['helper']);
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
     * Set HTTP client
     *
     * @param Granify_Sales_Helper_Data $helper
     * @return $this
     */
    protected function _setHelper(Granify_Sales_Helper_Data $helper)
    {
        $this->_helper = $helper;
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
            $this->_setUri()
                ->_setConfig()
                ->_setHeaders()
                ->_setMethod();
        }
        return $this->_httpClient;
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
     * Set HTTP client headers
     *
     * @return Granify_Sales_Model_Sender_Transport
     */
    protected function _setHeaders()
    {
        $this->_httpClient->setHeaders(
            array(
                self::HEADER_HOSTNAME          => $this->_getBaseUrl(),
                self::HEADER_SITE_ID           => $this->_helper->resetStore()->getSiteId(),
                Zend_Http_Client::CONTENT_TYPE => $this->_getContentType(),
            )
        );
        return $this;
    }

    /**
     * Get default content type
     *
     * @return string
     */
    protected function _getContentType()
    {
        return self::CONTENT_TYPE_APPLICATION_JSON;
    }

    /**
     * Set HTTP client config
     *
     * @return Granify_Sales_Model_Sender_Transport
     */
    protected function _setConfig()
    {
        $this->_httpClient->setConfig(
            array(
                'maxredirects' => 0,
                'timeout'      => $this->_helper->getPostOrderTimeout())
        );
        return $this;
    }

    /**
     * Set HTTP client config
     *
     * @return Granify_Sales_Model_Sender_Transport
     * @todo URI should be get from outside
     */
    protected function _setUri()
    {
        $this->_httpClient->setUri($this->_helper->getPostOrderUri());
        return $this;
    }

    /**
     * Set HTTP client method
     *
     * @return Granify_Sales_Model_Sender_Transport
     */
    protected function _setMethod()
    {
        $this->_httpClient->setMethod(Zend_Http_Client::POST);
        return $this;
    }

    /**
     * Send request
     *
     * @param array $data
     * @return Zend_Http_Response
     */
    public function request(array $data)
    {
        $client = $this->getHttpClient();
        $client->setRawData(Zend_Json_Encoder::encode($data));
        $result = $client->request();
        $this->_debug($result);
        return $result;
    }

    /**
     * Make logging
     *
     * @param Zend_Http_Response|string $result
     */
    protected function _debug($result)
    {
        if ($result instanceof Zend_Http_Response) {
            $result = $result->asString();
        }
        Mage::log($result, null, 'granify.log');
    }
}
