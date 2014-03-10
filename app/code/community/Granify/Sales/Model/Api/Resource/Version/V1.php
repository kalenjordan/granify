<?php
/**
 * API version resource class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Resource_Version_V1 extends Granify_Sales_Model_Api_Resource_Abstract
{
    /**
     * Method get
     *
     * @return array
     */
    public function get()
    {
        /** @var $helper Granify_Sales_Helper_Data */
        $helper = $this->_getHelper('granify_sales');
        return array(
            'version'             => Granify_Sales_Model_Api_Dispatcher::VERSION,
            'application_version' => $helper->getMagentoVersion(),
            'package_version'     => $helper->getPackageVersion(),
        );
    }
}
