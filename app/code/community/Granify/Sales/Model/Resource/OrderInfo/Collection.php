<?php
/**
 * The Granify Sales Order info resource model collection
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Resource_OrderInfo_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Local constructor
     */
    protected function _construct()
    {
        $this->_init('granify_sales/orderInfo');
    }
}
