<?php
/**
 * Model for prepare order info data
 *
 * Model for get order info based on invoice model
 *
 * @category    Granify
 * @package     Granify_Sales
 *
 * @method Granify_Sales_Model_OrderInfo setCreatedAt(array $data)
 * @method array getCreatedAt()
 * @method Granify_Sales_Model_OrderInfo setRequestFailure(int $data)
 * @method int getRequestFailure()
 * @method Granify_Sales_Model_OrderInfo setDeferredData(string $data)
 * @method string getDeferredData()
 * @method Granify_Sales_Model_OrderInfo setNormalData(array $data)
 * @method array getNormalData()
 * @method Granify_Sales_Model_Resource_OrderInfo_Collection getCollection()
 * @method Granify_Sales_Model_Resource_OrderInfo_Collection getResourceCollection()
 * @method Granify_Sales_Model_Resource_OrderInfo getResource()
 * @method Granify_Sales_Model_Resource_OrderInfo _getResource()
 * @method Granify_Sales_Model_OrderInfo setStoreId(int $storeId)
 */
class Granify_Sales_Model_OrderInfo extends Mage_Core_Model_Abstract
{
    /**
     * Invoice model of order
     *
     * @var Mage_Sales_Model_Order_Invoice
     * @deprecated Deprecated since API v3
     */
    protected $_invoice;

    /**
     * Internal constructor for init resource model
     */
    protected function _construct()
    {
        $this->_init('granify_sales/orderInfo');
    }

    /**
     * Set invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Granify_Sales_Model_OrderInfo
     * @deprecated Deprecated since API v3
     */
    public function setInvoice(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $this->_invoice = $invoice;
        return $this;
    }

    /**
     * Get store ID from data or from invoice
     */
    public function getStoreId()
    {
        if (!$this->getData('store_id') && $this->getInvoice()) {
            return $this->getInvoice()->getStore()->getId();
        }
        return $this->getData('store_id');
    }

    /**
     * Get invoice
     *
     * @return Mage_Sales_Model_Order_Invoice
     * @deprecated Deprecated since API v3
     */
    public function getInvoice()
    {
        return $this->_invoice;
    }

    /**
     * Get customer model with load by customer ID
     *
     * @param int $id
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer($id)
    {
        return Mage::getModel('customer/customer')->load($id);
    }

    /**
     * Get cart helper
     *
     * @return Granify_Sales_Helper_Cart
     */
    protected function _getHelper()
    {
        return Mage::helper('granify_sales/cart');
    }

    /**
     * Prepare order info data
     *
     * @return Granify_Sales_Model_OrderInfo
     * @throws Exception
     * @deprecated Deprecated since API v3
     */
    public function prepare()
    {
        $invoice = $this->getInvoice();
        if (!$invoice) {
            throw new Exception('Invoice is not set.');
        }

        $order = $invoice->getOrder();
        $customer = $this->_getCustomer($order->getData('customer_id'));

        $orderData = array(
            'buyer_accepts_marketing' => false,
            'cart_token'        => $this->_getHelper()->getCartToken($order->getQuoteId()),
            'created_at'        => $order->getData('created_at'),
            'currency'          => $invoice->getData('base_currency_code'),
            'subtotal_price'    => $invoice->getData('base_grand_total')
                                    - $invoice->getData('base_shipping_amount'),
            'taxes_included'    => false, //false because ever sent subtotal_price without tax
            'total_discounts'   => $invoice->getData('base_discount_amount'),
            'total_line_items_price' => $invoice->getData('base_subtotal'),
            'total_price'       => $invoice->getData('base_subtotal_incl_tax'),
            'total_tax'         => $invoice->getData('base_tax_amount'),
            'order_number'      => $invoice->getData('increment_id'),
            'magento_order_number' => $order->getData('increment_id'),
            'discount_codes'    => null,
            'financial_status'  => 'paid',
            'customer' => array(
                'id'            => $order->getData('customer_id'),
                'created_at'    => $customer->getData('created_at'),
                'email'         => $order->getData('customer_email'),
                'first_name'    => $order->getData('customer_firstname'),
                'last_name'     => $order->getData('customer_lastname'),
            )
        );

        if ($order->getData('coupon_code')) {
            $orderData['discount_codes'] = array(array(
                'code'          => $order->getData('coupon_code'),
                'amount'        => $invoice->getData('base_discount_amount'),
            ));
        }

        //prepare ordered and invoiced items
        $invoiceItemsByOrderItem = array();
        /** @var $item Mage_Sales_Model_Order_Invoice_Item */
        foreach ($invoice->getAllItems() as $item) {
            $invoiceItemsByOrderItem[$item->getData('order_item_id')] = $item;
        }

        $orderData['line_items'] = array();
        /** @var $orderItem Mage_Sales_Model_Order_Item */
        foreach ($order->getItemsCollection() as $orderItem) {
            if ($orderItem->getData('base_price') == 0
                || !isset($invoiceItemsByOrderItem[$orderItem->getId()])
            ) {
                continue;
            }

            $data = $this->_getOrderItemData($orderItem, $invoiceItemsByOrderItem[$orderItem->getId()]);
            $orderData['line_items'][] = $data;
        }

        $this->setNormalData($orderData);
        return $this;
    }

    /**
     * Get order item info
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param Mage_Sales_Model_Order_Invoice_Item $invoiceItem
     * @return array
     */
    protected function _getOrderItemData(Mage_Sales_Model_Order_Item $orderItem,
                                         Mage_Sales_Model_Order_Invoice_Item $invoiceItem)
    {
        $children = $orderItem->getChildrenItems();
        if ($children) {
            /** @var $variantItem Mage_Sales_Model_Order_Item */
            $variantItem = current($children);
            $name = $variantItem->getData('name');
            $variantId = $variantItem->getData('product_id');
        } else {
            $name = $orderItem->getData('name');
            $variantId = null;
        }

        return array(
            'price'      => $orderItem->getData('base_price'),
            'product_id' => $orderItem->getData('product_id'),
            'quantity'   => $invoiceItem->getData('qty'),
            'name'       => $name,
            'variant_id' => $variantId,
        );
    }

    /**
     * Granify_Sales_Model_OrderInfo
     *
     * @return Granify_Sales_Model_OrderInfo|Mage_Core_Model_Abstract
     * @throws Exception
     */
    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setStoreId($this->getInvoice()->getStore()->getId());

            $data = $this->getNormalData();
            if (!$data || !is_array($data)) {
                throw new Exception('Data is not set or is not array.');
            }
            $this->setDeferredData(serialize($data));
        }
        return $this;
    }

    /**
     * Un-serialize data on after load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->setNormalData(unserialize($this->getDeferredData()));
        parent::_afterLoad();
        return $this;
    }
}
