<?php
/**
 * Granify Sales shopping cart helper
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Helper_Cart extends Granify_Sales_Helper_BaseAbstract
{
    /**
     * URL part of checkout cart page
     */
    const CHECKOUT_CART_URI_PART = 'checkout/cart';

    /**
     * Get cart token
     *
     * @param string|int|null $quoteId
     * @return string
     */
    public function getCartToken($quoteId = null)
    {
        if ($quoteId) {
            $token = $this->_generateToken($quoteId);
        } else {
            //generate random cart token if quote ID is not set
            $token = $this->_getRandomToken();
        }
        return $token;
    }

    /**
     * Generate random cart token
     *
     * @return mixed|string
     */
    protected function _getRandomToken()
    {
        /** @var $session Mage_Catalog_Model_Session */
        $session = $this->_getSingleton('catalog/session');
        $token   = $session->getData('granify_cart_token');
        if (!$token) {
            $token = $this->_generateToken($this->_getRandomString());
            $session->setData('granify_cart_token', $token);
        }
        return $token;
    }

    /**
     * Generate token by string and Site ID
     *
     * @param string|int $str
     * @return string
     */
    protected function _generateToken($str)
    {
        /** @var $helper Granify_Sales_Helper_Data */
        $helper = $this->_getHelper('granify_sales');
        return md5($str . '-' . $helper->getSiteId());
    }

    /**
     * Get random string (date-time+micro-time+rand-number)
     *
     * @return string
     * @codeCoverageIgnore  It return random value
     */
    protected function _getRandomString()
    {
        return date('YmdHis') . microtime() . rand(0, 10000);
    }

    /**
     * Get cart info in JSON format
     *
     * @return string
     */
    public function getCartJson()
    {
        $cart = $this->_getCart();
        /** @var $session Mage_Catalog_Model_Session */
        $session = $this->_getSingleton('catalog/session');
        if ($cart) {
            if (!$cart->getProductIds()) {
                //no any products in cart
                $session->setData('granify_checkout_cart_json', null);
                return '';
            }
            $json = Zend_Json::encode(
                $this->_getCartInfoData($cart)
            );
            $session->setData('granify_checkout_cart_json', $json);
        } else {
            //cart model not called on other pages then try to get from session
            $json = $session->getData('granify_checkout_cart_json');
        }
        return $json;
    }

    /**
     * Get Granify cart info data
     *
     * @param Mage_Checkout_Model_Cart $cart
     * @return array
     */
    protected function _getCartInfoData(Mage_Checkout_Model_Cart $cart)
    {
        //process cart data to the granify format
        /** @var $cartInfo Granify_Sales_Model_CartInfo */
        $cartInfo = $this->_getModel('granify_sales/cartInfo');
        $cartInfo->setCart($cart)
            ->prepare();
        return $cartInfo->getData();
    }

    /**
     * Get cart model singleton
     *
     * @return Mage_Checkout_Model_Cart|null    Return NULL if it doesn't called before
     */
    protected function _getCart()
    {
        return $this->isCartRegistered()
            ? $this->_getSingleton('checkout/cart') : null;
    }

    /**
     * Check registered cart model as singleton
     *
     * @return bool
     */
    public function isCartRegistered()
    {
        return (bool)$this->registry('_singleton/checkout/cart');
    }

    /**
     * Check registered cart model as singleton
     *
     * @return bool
     */
    public function isCartPage()
    {
        $uri = $this->_getApp()->getRequest()->getRequestUri();
        return (bool)strpos($uri, self::CHECKOUT_CART_URI_PART);
    }
}
