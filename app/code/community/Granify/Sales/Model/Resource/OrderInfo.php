<?php
/**
 * The Granify Sales Order info resource model
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Resource_OrderInfo extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Class local constructor
     */
    protected function _construct()
    {
        $this->_init('granify_sales/order_info', 'item_id');
    }
}
