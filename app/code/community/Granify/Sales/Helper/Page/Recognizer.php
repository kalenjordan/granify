<?php
/**
 * Granify Sales page type recognizer helper
 *
 * This class designed for recognize catalog page types and another on the frontend
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Helper_Page_Recognizer extends Granify_Sales_Helper_BaseAbstract
{
    /**#@+
     * Page types
     */
    const PAGE_HOME       = 'home';
    const PAGE_COLLECTION = 'collection';
    const PAGE_PRODUCT    = 'product';
    const PAGE_CART       = 'cart';
    const PAGE_CHECKOUT   = 'checkout';
    const PAGE_OTHER      = 'other';
    /**#@-*/

    /**
     * Recognize the current page
     */
    public function recognize()
    {
        if ($this->_isHomePage()) {
            $type = self::PAGE_HOME;
        } elseif ($this->_isCategoryPage()) {
            $type = self::PAGE_COLLECTION;
        } elseif ($this->_isProductPage()) {
            $type = self::PAGE_PRODUCT;
        } elseif ($this->_isCartPage()) {
            $type = self::PAGE_CART;
        } elseif ($this->_isCheckoutPage()) {
            $type = self::PAGE_CHECKOUT;
        } else {
            $type = self::PAGE_OTHER;
        }
        return $type;
    }

    /**
     * Get status of "is product page"
     *
     * @return bool
     */
    protected function _isProductPage()
    {
        return $this->registry('current_product')
            && $this->_getRequest()->getModuleName() == 'catalog'
            && $this->_getRequest()->getControllerName() == 'product'
            && $this->_getRequest()->getActionName() == 'view';
    }

    /**
     * Get status "is products list" page on the category page, collection page
     *
     * @return bool
     */
    protected function _isCategoryPage()
    {
        return $this->registry('current_category')
            && $this->_getRequest()->getModuleName() == 'catalog'
            && $this->_getRequest()->getControllerName() == 'category'
            && $this->_getRequest()->getActionName() == 'view';
    }

    /**
     * Get status of "is home page"
     *
     * @return bool
     */
    protected function _isHomePage()
    {
        return $this->_getRequest()->getModuleName() == 'cms'
            && $this->_getRequest()->getControllerName() == 'index';
    }

    /**
     * Get status of "is cart page"
     *
     * @return bool
     */
    protected function _isCartPage()
    {
        /** @var Granify_Sales_Helper_Cart $cart */
        $cart = $this->_getHelper('granify_sales/cart');
        return $cart->isCartPage();
    }

    /**
     * Get status of "is checkout page"
     *
     * @return bool
     */
    protected function _isCheckoutPage()
    {
        return $this->_getRequest()->getModuleName() == 'checkout'
            && $this->_getRequest()->getControllerName() != 'cart';
    }
}
