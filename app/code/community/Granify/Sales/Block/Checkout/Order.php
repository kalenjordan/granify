<?php
/**
 * Block for render order data in as JS object
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Block_Checkout_Order extends Mage_Core_Block_Template
{
    /**
     * Get JSON order data
     *
     * @return string
     */
    public function getOrderJson()
    {
        /** @var $helperData Granify_Sales_Helper_Data */
        $helperData = $this->helper('granify_sales');
        if (!$helperData->isAble()) {
            return '';
        }

        /** @var $helperOrder Granify_Sales_Helper_Order */
        $helperOrder = $this->helper('granify_sales/order');
        return $helperOrder->getOrderJson();
    }
}
