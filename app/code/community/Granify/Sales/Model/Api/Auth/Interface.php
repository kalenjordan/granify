<?php
/**
 * Auth model interface
 *
 * @category    Granify
 * @package     Granify_Sales
 */
interface Granify_Sales_Model_Api_Auth_Interface
{
    /**
     * Authenticate method
     *
     * @param Zend_Controller_Request_Http $request
     * @param array $options
     * @return bool
     */
    public function authenticate(Zend_Controller_Request_Http $request, array $options);
}
