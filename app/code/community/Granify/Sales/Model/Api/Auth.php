<?php
/**
 * API auth class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Auth implements Granify_Sales_Model_Api_Auth_Interface
{
    /**
     * API auth signature header name
     */
    const SIGNATURE_HEADER_NAME = 'HMAC';

    /**
     * API auth signature hash algorithm
     */
    const SIGNATURE_HASH_ALGORITHM = 'sha256';

    /**
     * Helper
     *
     * @var Granify_Sales_Helper_Data
     */
    protected $_helper;

    /**
     * Request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Authenticate method
     *
     * Compare API secret keys from request with from application
     *
     * @param Zend_Controller_Request_Http $request
     * @param array $options
     * @return bool
     * @throws Exception
     */
    public function authenticate(Zend_Controller_Request_Http $request, array $options)
    {
        $this->_request = $request;

        if (!isset($options['helper']) || !($options['helper'] instanceof Granify_Sales_Helper_Data)) {
            throw new Exception('Helper is not set.');
        }
        $this->_helper = $options['helper'];

        return $this->_isSignatureValid();
    }

    /**
     * Check valid signature
     *
     * @return bool
     */
    protected function _isSignatureValid()
    {
        return $this->_getExpectedSignature() === $this->_request->getHeader(self::SIGNATURE_HEADER_NAME);
    }

    /**
     * Get request signature
     *
     * @return string
     */
    protected function _getExpectedSignature()
    {
        return $this->getSignature(
            $this->_request->getRawBody(),
            $this->_helper->getApiSecret(),
            self::SIGNATURE_HASH_ALGORITHM
        );
    }

    /**
     * Get hash string with HMAC and base64
     *
     * @param string $algorithm
     * @param string $str
     * @param string $key
     * @return string
     */
    static public function getSignature($str, $key, $algorithm = self::SIGNATURE_HASH_ALGORITHM)
    {
        return base64_encode(hash_hmac($algorithm, $str, $key, true));
    }
}
