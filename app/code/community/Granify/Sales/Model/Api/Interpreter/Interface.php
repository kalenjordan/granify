<?php
/**
 * API exception class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
interface Granify_Sales_Model_Api_Interpreter_Interface
{
    /**
     * Render RAW to array
     *
     * @param array $raw
     * @return string
     */
    public function interpret($raw);
}
