<?php
/**
 * Model for prepare order full info data
 *
 * Model for get order info based on order model
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_OrderFullInfo extends Granify_Sales_Model_OrderInfo
{
    /**
     * Order model
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Set order
     *
     * @param Mage_Sales_Model_Order $order
     * @return Granify_Sales_Model_OrderInfo
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Get store ID from data or from invoice
     */
    public function getStoreId()
    {
        if (!$this->getData('store_id') && $this->getOrder()) {
            return $this->getOrder()->getStore()->getId();
        }
        return $this->getData('store_id');
    }

    /**
     * Get order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
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
     * Prepare order info data
     *
     * @return Granify_Sales_Model_OrderInfo
     * @throws Exception
     */
    public function prepare()
    {
        $order = $this->getOrder();

        $orderData = $this->_getOrderData($order);

        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoiceData = $this->_getInvoiceData($order, $invoice);
            $orderData['invoices'][$invoice->getData('increment_id')] = $invoiceData;
        }
        if ($order->getData('coupon_code')) {
            $orderData['discount_codes'] = array(
                array(
                    'code' => $order->getData('coupon_code'),
                ));
        }

        $this->setNormalData($orderData);
        return $this;
    }

    /**
     * Get order data
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _getOrderData($order)
    {
        $customer = $this->_getCustomer($order->getData('customer_id'));
        $orderData = array(
            'created_at'              => $order->getData('created_at'),
            'currency'                => $order->getData('base_currency_code'),
            'subtotal_price'          => $order->getData('base_subtotal') + $order->getData('base_discount_amount'),
            'taxes_included'          => false,
            'total_discounts'         =>  (-1 * $order->getData('base_discount_amount')),
            'total_line_items_price'  => $order->getData('base_subtotal'),
            'total_price'             => $order->getData('base_grand_total'),
            'total_tax'               => $order->getData('base_tax_amount') + $order->getData('base_shipping_amount'),
            'order_number'            => $order->getData('increment_id'),
            'discount_codes'          => null,
            'financial_status'        => 'paid',
            'customer' => array(
                'id'            => $order->getData('customer_id'),
                'created_at'    => $customer->getData('created_at'),
                'email'         => $order->getData('customer_email'),
                'first_name'    => $order->getData('customer_firstname'),
                'last_name'     => $order->getData('customer_lastname'),
            ),
            'invoices'                => array()
        );

        # Add discount code
        if ($order->getData('coupon_code')) {
            $orderData['discount_codes'] = array(array(
                'code'          => $order->getData('coupon_code'),
                'amount'        => $order->getData('base_discount_amount'),
            ));
        }

        # Add line items
        $orderData['line_items'] = array();

        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ( $orderItem->getData('base_price') == 0 ) {
                 continue;
            }
            
            $orderData['line_items'][] = array(
            'price'      => $orderItem->getData('base_price'),
            'product_id' => $orderItem->getSku(),
            'quantity'   => $orderItem->getQtyOrdered(),
            'name'       => $orderItem->getName()
        );
        }

        return $orderData;
    }

    /**
     * Get invoice data
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return array
     */
    protected function _getInvoiceData($order, $invoice)
    {
        $invoiceData = array(
            'created_at'             => $order->getData('created_at'),
            'currency'               => $invoice->getData('base_currency_code'),
            'subtotal_price'         => $invoice->getData('base_subtotal')
            + $invoice->getData('base_discount_amount'),
            'total_discounts'        => (-1 * $invoice->getData('base_discount_amount')),
            'total_line_items_price' => $invoice->getData('base_subtotal'),
            'total_price'            => $invoice->getData('base_grand_total'),
            'total_tax'              => $invoice->getData('base_tax_amount') + $invoice->getData('base_shipping_amount'),
            'invoice_number'         => $invoice->getData('increment_id'),
            'discount_codes'         => null,
            'financial_status'       => 'paid',
        );
        if ($order->getData('coupon_code')) {
            $invoiceData['discount_codes'] = array(
                array(
                    'amount' => $invoice->getData('base_discount_amount'),
                ));
        }

        //prepare ordered and invoiced items
        $lineItems = $this->_getInvoiceItems($order, $invoice);
        $invoiceData['line_items'] = $lineItems;
        return $invoiceData;
    }

    /**
     * Prepare ordered and invoiced items
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return array
     */
    protected function _getInvoiceItems($order, $invoice)
    {
        $invoiceItemsByOrderItem = array();
        /** @var $item Mage_Sales_Model_Order_Invoice_Item */
        foreach ($invoice->getAllItems() as $item) {
            $invoiceItemsByOrderItem[$item->getData('order_item_id')] = $item;
        }

        $lineItems = array();
        /** @var $orderItem Mage_Sales_Model_Order_Item */
        foreach ($order->getItemsCollection() as $orderItem) {
            if ($orderItem->getData('base_price') == 0
                || !isset($invoiceItemsByOrderItem[$orderItem->getId()])
            ) {
                continue;
            }

            $data        = $this->_getOrderItemData(
                $orderItem, $invoiceItemsByOrderItem[$orderItem->getId()]
            );
            $lineItems[] = $data;
        }
        return $lineItems;
    }
}
