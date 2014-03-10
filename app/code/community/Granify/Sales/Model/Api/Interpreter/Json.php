<?php
/**
 * Response JSON renderer
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Interpreter_Json
    implements Granify_Sales_Model_Api_Interpreter_Interface
{
    /**
     * Interpret RAW data to array
     *
     * @param array $raw
     * @return mixed|string
     * @throws Granify_Sales_Model_Api_Exception
     */
    public function interpret($raw)
    {
        try {
            return Zend_Json_Decoder::decode($raw, Zend_Json::TYPE_ARRAY);
        } catch (Zend_Json_Exception $e) {
            throw new Granify_Sales_Model_Api_Exception(
                'Cannot decode JSON string.',
                Granify_Sales_Model_Api_Dispatcher::CODE_BAD_REQUEST
            );
        }
    }
}
