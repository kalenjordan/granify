<?php
/**
 * API shop info resource class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Resource_ShopInfo_V1 extends Granify_Sales_Model_Api_Resource_Abstract
{
    /**
     * Authenticate skip for PUT method
     *
     * @return bool
     */
    protected function _authenticate()
    {
        if ($this->_request->getMethod() == Granify_Sales_Model_Api_Processor::METHOD_POST) {
            //skip authenticate for update api_secret and site_id
            return true;
        }
        return parent::_authenticate();
    }


    /**
     * Method get collection
     *
     * @return array
     */
    protected function _getCollection()
    {
        $data = array(
            'currency' => $this->_getCurrency(),
        );
        return $data;
    }

    /**
     * Get shop currency
     *
     * @return string
     */
    protected function _getCurrency()
    {
        /** @var $currency Mage_Directory_Model_Currency */
        $currency = $this->_getModel('directory/currency');
        $value = $currency->getConfigBaseCurrencies();
        return $value ? $value[0] : null;
    }

    /**
     * Method POST
     *
     * @return bool|string
     * @throws Exception|Zend_Http_Client_Adapter_Exception
     * @throws Granify_Sales_Model_Api_Exception
     */
    public function post()
    {
        $apiSecret = $this->_getPostData('api_secret');
        $siteId = $this->_getPostData('site_id');
        try {
            $response = $this->_makeRequestToGranify($siteId, $apiSecret);
            if (Granify_Sales_Model_Api_Dispatcher::CODE_OK === (int) $response->getStatus()) {
                $this->_updateConfig($apiSecret, $siteId);
                $this->_setStatusCode(Granify_Sales_Model_Api_Dispatcher::CODE_OK);
                return 'OK';
            }
        } catch (Zend_Http_Client_Adapter_Exception $e) {
            $this->_logExceptionByLogger($e);
            if (false !== strpos($e->getMessage(), 'Unable to Connect to')) {
                throw new Granify_Sales_Model_Api_Exception(
                    'Granify server is not respond.',
                    Granify_Sales_Model_Api_Dispatcher::CODE_INTERNAL_ERROR
                );
            }
            throw $e;
        }
        throw new Granify_Sales_Model_Api_Exception(
            'Bad response from Granify.',
            Granify_Sales_Model_Api_Dispatcher::CODE_BAD_REQUEST
        );
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
     * Make request to Granify
     *
     * @param string|int $siteId
     * @param string $apiSecret
     * @return Zend_Http_Response
     */
    protected function _makeRequestToGranify($siteId, $apiSecret)
    {
        list($url, $path) = $this->_getGranifyResourceUri($siteId);
        $rest = $this->_getClient();
        $data = Zend_Json::encode($this->_getCollection());
        $this->_configureClient($rest, $url, $apiSecret, $siteId, $data);
        return $rest->restPost($path, $data);
    }

    /**
     * Save settings to config
     *
     * @param string $apiSecret
     * @param string|int $siteId
     * @return Granify_Sales_Model_Api_Resource_ShopInfo_V1
     */
    protected function _updateConfig($apiSecret, $siteId)
    {
        /** @var $config Granify_Sales_Helper_Config */
        $config = $this->_getHelper('granify_sales/config');
        $config->update(
            array(
                Granify_Sales_Helper_Data::XML_PATH_API_SECRET => $apiSecret,
                Granify_Sales_Helper_Data::XML_PATH_SITE_ID    => $siteId,
            ),
            null, true
        );
        return $this;
    }

    /**
     * Configure REST client
     *
     * @param Zend_Rest_Client $rest
     * @param string $url
     * @param string $apiSecret
     * @param string $siteId
     * @param string $body
     * @return Granify_Sales_Model_Api_Resource_ShopInfo_V1
     * @todo should be use default HTTP client from Granify_Sales_Model_Sender_Transport
     */
    protected function _configureClient(Zend_Rest_Client $rest, $url, $apiSecret, $siteId, $body = '')
    {
        //TODO use transport of module
        $rest->setUri($url);
        $hmac = $this->_getSignature($apiSecret, $body);
        $rest->getHttpClient()
            ->setHeaders(Granify_Sales_Model_Sender_Transport::HEADER_HOSTNAME, $this->_getBaseUrl())
            ->setHeaders(Granify_Sales_Model_Sender_Transport::HEADER_SITE_ID, $siteId)
            ->setHeaders(Granify_Sales_Model_Api_Auth::SIGNATURE_HEADER_NAME, $hmac)
            ->setHeaders(
                Zend_Http_Client::CONTENT_TYPE,
                Granify_Sales_Model_Sender_Transport::CONTENT_TYPE_APPLICATION_JSON
            );
        return $this;
    }

    /**
     * Get HTTP host from server
     *
     * @return mixed
     */
    protected function _getBaseUrl()
    {
        return $this->_getApp()->getStore()->getBaseUrl();
    }

    /**
     * Get App model
     *
     * @return Mage_Core_Model_App
     */
    protected function _getApp()
    {
        return Mage::app();
    }

    /**
     * Get signature
     *
     * @param string $apiSecret
     * @param string $body
     * @return string
     */
    protected function _getSignature($apiSecret, $body = '')
    {
        return $this->_getAuth()->getSignature($body, $apiSecret);
    }

    /**
     * Get parsed URL and path to granify shop info API resource
     *
     * @param int|string $siteId
     * @return array
     */
    protected function _getGranifyResourceUri($siteId)
    {
        /** @var $helper Granify_Sales_Helper_Data */
        $helper = $this->_getHelper('granify_sales');
        $uri = $helper->getGranifyShopInfoResourceUri();
        $uri = sprintf($uri, $siteId);
        $info = parse_url($uri);
        $url = $info['scheme'] . '://' . $info['host'];
        return array($url, $info['path']);
    }

    /**
     * Get REST client
     *
     * @return Zend_Rest_Client
     */
    protected function _getClient()
    {
        return new Zend_Rest_Client();
    }
}
