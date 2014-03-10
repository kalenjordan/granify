<?php
/**
 * Model for prepare cart info data
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_CartInfo extends Varien_Object
{
    /**
     * Cart model
     *
     * @var Mage_Checkout_Model_Cart
     */
    protected $_cart;

    /**
     * Set cart model
     *
     * @param Mage_Checkout_Model_Cart $cart
     * @return Granify_Sales_Model_CartInfo
     */
    public function setCart(Mage_Checkout_Model_Cart $cart)
    {
        $this->_cart = $cart;
        return $this;
    }

    /**
     * Get data helper
     *
     * @return Granify_Sales_Helper_Cart
     */
    public function _getHelper()
    {
        return Mage::helper('granify_sales/cart');
    }

    /**
     * Get cart model
     *
     * @return Mage_Checkout_Model_Cart
     */
    public function getCart()
    {
        return $this->_cart;
    }

    /**
     * Prepare cart info data
     *
     * @return Granify_Sales_Model_CartInfo
     * @throws Exception
     */
    public function prepare()
    {
        $this->_serDefaultData();

        $cartItems = array();

        /** @var $collection Mage_Sales_Model_Mysql4_Quote_Item_Collection */
        $collection = $this->getCart()->getQuote()->getItemsCollection();
        /** @var $item Mage_Sales_Model_Quote_Item */
        foreach ($collection as $item) {
            if ($item->getData('base_price') == 0) {
                /**
                 * Skip free products
                 */
                continue;
            }

            $this->_collectTotals($item);

            $cartItems[] = $this->_collectItem($item);
        }
        $this->setData('items', $cartItems);
        return $this;
    }

    /**
     * Get quote item option product for configurable products
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return Mage_Catalog_Model_Product|null
     */
    protected function _getQuoteOptionProduct(Mage_Sales_Model_Quote_Item $item)
    {
        $qtyOptions = $item->getQtyOptions();
        if ($qtyOptions) {
            /** @var $quoteItemOption Mage_Sales_Model_Quote_Item_Option */
            $quoteItemOption = current($qtyOptions);
            return $quoteItemOption->getProduct();
        }
        return null;
    }

    /**
     * Collect data about cart info item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return array
     */
    protected function _collectItem(Mage_Sales_Model_Quote_Item $item)
    {
        $quoteProduct = $this->_getQuoteOptionProduct($item);
        $cartItem     = array(
            'id'         => $item->getData('product_id'),
            'quantity'   => $item->getData('qty'),
            'price'      => $item->getData('base_price'),
            'line_price' => $item->getData('base_price') * $item->getData('qty'),
            'title'      => $quoteProduct ? $quoteProduct->getName() : $item->getData('name'),
            'variant_id' => $quoteProduct ? $quoteProduct->getId() : null,
        );
        return $cartItem;
    }

    /**
     * Collect cart info totals
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return $this
     */
    protected function _collectTotals(Mage_Sales_Model_Quote_Item $item)
    {
        $this->setData('total_price', $this->getData('total_price') + $item->getData('base_price'));
        $this->setData('item_count', $this->getData('item_count') + $item->getData('qty'));
        return $this;
    }

    /**
     * Set default cart info data
     *
     * @return $this
     */
    protected function _serDefaultData()
    {
        $this->setData('item_count', 0);
        $this->setData('total_price', 0);
        $this->setData(
            'cart_token',
            $this->_getHelper()->getCartToken(
                $this->getCart()->getQuote()->getId()
            )
        );
        return $this;
    }
}
