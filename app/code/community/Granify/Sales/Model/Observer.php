<?php
/**
 * Observer
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Observer
{
    /**
     * Max resending failure requests to Granify server
     */
    const MAX_COLLECTION_REQUESTS = 4;

    /**
     * Post info about order to Granify server
     *
     * @param Varien_Event_Observer $observer
     * @return Granify_Sales_Model_Observer
     */
    public function postOrderInfo(Varien_Event_Observer $observer)
    {
        $this->_debug('Post Order Info');

        /** @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getInvoice();

        /** @var $helper Granify_Sales_Helper_Data */
        $helper = Mage::helper('granify_sales');
        $helper->setStoreId($invoice->getStore()->getId());

        if (!$helper->isAble()) {
            $this->_debug('Skip for store #' . $invoice->getStore()->getId());
            return $this;
        }


        /** @var $orderInfo Granify_Sales_Model_OrderInfo */
        $orderInfo = Mage::getModel('granify_sales/orderInfo');

        try {
            $orderInfo->setInvoice($invoice)
                ->prepare();
            if (!$this->_sendRequest($orderInfo)) {
                $this->_debug('Failure request. Save for postpone.');
                $this->_saveOrderInfoOnFailure($orderInfo);
            } else {
                $this->_debug('Success request.');
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Send request with order info to Granify service
     *
     * @param Granify_Sales_Model_OrderInfo $orderInfo
     * @return bool
     */
    protected function _sendRequest(Granify_Sales_Model_OrderInfo $orderInfo)
    {
        //TODO optimize init store ID in helper
        /* @var $helper Granify_Sales_Helper_Data */
        $helper = Mage::helper('granify_sales');
        $helper->setStoreId($orderInfo->getStoreId());

        /** @var $sender Granify_Sales_Model_Sender */
        $sender = Mage::getModel('granify_sales/sender');
        $sender->setData($orderInfo->getNormalData());
        $sender->send();
        return $sender->isSuccessful();
    }

    /**
     * Save order info on request failure
     *
     * @param Granify_Sales_Model_OrderInfo $orderInfo
     * @return Granify_Sales_Model_Observer
     */
    protected function _saveOrderInfoOnFailure(Granify_Sales_Model_OrderInfo $orderInfo)
    {
        if ($orderInfo->getId()) {
            $orderInfo->setRequestFailure($orderInfo->getRequestFailure() + 1);
        } else {
            $orderInfo->setRequestFailure(1);
        }
        $orderInfo->save();
        return $this;
    }

    /**
     * Retrying post order info
     *
     * @return Granify_Sales_Model_Observer
     */
    public function retryingPostOrderInfo()
    {
        $this->_debug('Retrying Post Order Info');
        /** @var $collectionOrderInfo Granify_Sales_Model_Resource_OrderInfo_Collection */
        $collectionOrderInfo = Mage::getModel('granify_sales/orderInfo')->getCollection();
        $collectionOrderInfo->setPageSize(self::MAX_COLLECTION_REQUESTS);

        try {
            /** @var $orderInfo Granify_Sales_Model_OrderInfo */
            foreach ($collectionOrderInfo as $orderInfo) {
                //to wait longer than the previous
                if (1 < rand(1, $orderInfo->getRequestFailure())) {
                    continue;
                }
                if (!$this->_sendRequest($orderInfo)) {
                    $this->_debug('Retrying. Failure request. Save for postpone.');
                    $this->_saveOrderInfoOnFailure($orderInfo);
                } else {
                    $this->_debug('Retrying. Success request.');
                    $orderInfo->delete();
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Make debug messages
     *
     * @param string $message
     * @return $this
     */
    protected function _debug($message)
    {
        Mage::log($message, null, 'granify.log');
        return $this;
    }
}
