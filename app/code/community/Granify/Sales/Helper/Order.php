<?php
/**
 * Granify Sales Order helper
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Helper_Order extends Granify_Sales_Helper_BaseAbstract
{
    /**
     * URL part of checkout cart page
     */
    const CHECKOUT_ORDER_URI_PART = 'checkout/onepage/success';

    

    /**
     * Get order info in JSON format
     *
     * @return string
     */
    public function getOrderJson()
    {
        if($this->isSuccessPage())
            {
                $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
                if ($order) {
                    $orderInfo = $this->_getModel('granify_sales/orderFullInfo');
                    $json = Zend_Json::encode(
                        $orderInfo->setOrder($order)->prepare()->getNormalData()
                    );
                    }
                }
        return $json;
    }

    /**
     * Check registered cart model as singleton
     *
     * @return bool
     */
    public function isSuccessPage()
    {
        $uri = $this->_getApp()->getRequest()->getRequestUri();
        return (bool)strpos($uri, self::CHECKOUT_ORDER_URI_PART);
    }
}
