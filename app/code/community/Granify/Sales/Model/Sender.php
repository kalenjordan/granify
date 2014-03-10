<?php
/**
 * Model for send order info to granify server
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Sender extends Varien_Object
{
    /**
     * Send transport
     *
     * @var Granify_Sales_Model_Sender_Transport
     */
    protected $_transport;

    /**
     * Get transport model
     *
     * @return Granify_Sales_Model_Sender_Transport
     */
    public function getTransport()
    {
        if (null === $this->_transport) {
            $this->_transport = Mage::getModel('granify_sales/sender_transport');
        }
        return $this->_transport;
    }

    /**
     * Send data
     *
     * @return Granify_Sales_Model_Sender
     * @throws Exception
     */
    public function send()
    {
        $data = $this->getData();
        if (!$data) {
            throw new Exception('Data is not set.');
        }

        try {
            $this->getTransport()->request($data);
        } catch (Zend_Http_Client_Adapter_Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Is request successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        $lastResponse = $this->getTransport()->getHttpClient()->getLastResponse();
        return $lastResponse && $lastResponse->isSuccessful();
    }
}
