<?php
/**
 * Block for render cart data in as JS object
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Block_Checkout_Cart extends Mage_Core_Block_Template
{
    /**
     * Get JSON cart data
     *
     * @return string
     */
    public function getCartJson()
    {
        /** @var $helperData Granify_Sales_Helper_Data */
        $helperData = $this->helper('granify_sales');
        if (!$helperData->isAble()) {
            return '';
        }
        
        /** @var $helperCart Granify_Sales_Helper_Cart */
        $helperCart = $this->helper('granify_sales/cart');
        return $helperCart->getCartJson();
    }
}
