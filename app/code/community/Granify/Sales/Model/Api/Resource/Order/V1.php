<?php
/**
 * API orders info resource class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Resource_Order_V1 extends Granify_Sales_Model_Api_Resource_Abstract
{
    /**
     * Default page limit
     */
    const PAGE_SIZE = 100;

    /**
     * Get current page
     *
     * @return string
     */
    protected function _getPage()
    {
        return $this->_getQuery('page', 1);
    }

    /**
     * Get page size limit
     *
     * @return string
     */
    protected function _getPageSize()
    {
        return self::PAGE_SIZE;
    }

    /**
     * Get filters
     *
     * @return array
     * @throws Granify_Sales_Model_Api_Exception
     */
    protected function _getFilters()
    {
        //store
        $filters = array('store_id' => array('in' => $this->_getStoreIds()));

        //dates
        $from    = $this->_getQuery('start_date');
        if (!$from) {
            throw new Granify_Sales_Model_Api_Exception(
                'GET parameter start_date (date or datetime) is required.'
            );
        }

        //TODO add date validation
        $filters['created_at']['from'] = $from;

        $to = $this->_getQuery('end_date');
        if ($to) {
            $filters['created_at']['to'] = $to;
        }
        return $filters;
    }

    /**
     * Get store IDs
     *
     * Return store IDs for website.
     * But on set store as get param, then return current store ID.
     *
     * @return array|int
     */
    protected function _getStoreIds()
    {
        if ($this->_getQuery('___store')) {
            return $this->_getApp()->getStore()->getId();
        } else {
            return $this->_getApp()->getWebsite()->getStoreIds();
        }
    }

    /**
     * Method get collection
     *
     * @return array
     */
    protected function _getCollection()
    {
        $data = array();
        $collection = $this->_getInvoiceCollection();
        if ($collection->getSize()) {
            /* @var $invoice Mage_Sales_Model_Order_Invoice */
            foreach ($collection as $invoice) {
                $data['orders'][] = $this->_getOrderInfoData($invoice);
            }
            $data['page']      = $collection->getCurPage();
            $data['page_size'] = $collection->getPageSize();
            $data['total']     = $collection->getSize();
        }
        $this->_setStatusCode(Granify_Sales_Model_Api_Dispatcher::CODE_OK);
        return $data;
    }

    /**
     * Get order info by invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return array
     */
    protected function _getOrderInfoData($invoice)
    {
        /* @var $orderInfo Granify_Sales_Model_OrderInfo */
        $orderInfo = $this->_getModel('granify_sales/orderInfo');
        return $orderInfo->setInvoice($invoice)->prepare()->getNormalData();
    }

    /**
     * Get invoice collection
     *
     * @return Mage_Sales_Model_Mysql4_Order_Invoice_Collection
     */
    protected function _getInvoiceCollection()
    {
        /* @var $collection Mage_Sales_Model_Mysql4_Order_Invoice_Collection */
        $collection = Mage::getResourceModel('sales/order_invoice_collection');
        foreach ($this->_getFilters() as $field => $condition) {
            $collection->addFieldToFilter($field, $condition);
        }

        $collection->getSize(); //load total count of items
        $collection->setPage((int) $this->_getPage(), (int) $this->_getPageSize());
        return $collection;
    }
}
