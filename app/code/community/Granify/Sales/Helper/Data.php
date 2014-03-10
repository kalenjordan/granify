<?php
/**
 * Granify Sales data helper
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Helper_Data extends Granify_Sales_Helper_BaseAbstract
{
    /**
     * XPath to default Granify JS link
     */
    const XML_PATH_DEFAULT_GRANIFY_JS_LINK = 'granify/general/default_granify_js_link';

    /**
     * XPath to URI for post order
     */
    const XML_PATH_POST_ORDER_URI          = 'granify/post_options/post_order_url';

    /**
     * XPath to URI for get shop info from Granify side
     */
    const XML_PATH_GRANIFY_SHOP_INFO_URI   = 'granify/post_options/shop_info_uri';

    /**
     * XPath to timeout option for post order
     */
    const XML_PATH_POST_ORDER_TIMEOUT      = 'granify/post_options/post_order_timeout';

    /**
     * XPath to timeout option for post order
     */
    const XML_PATH_API_SECRET              = 'granify/general/api_secret';

    /**
     * XPath to Site ID
     */
    const XML_PATH_SITE_ID                 = 'granify/general/site_id';

    /**
     * XPath to Site ID
     */
    const XML_PATH_PACKAGE_VERSION         = 'modules/Granify_Sales/package_version';

    /**
     * Default store ID
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Default store
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Get store model
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        if (null === $this->_store) {
            $this->_store = $this->_getApp()->getStore($this->_storeId);
        }
        return $this->_store;
    }

    /**
     * Get app model
     *
     * @return Mage_Core_Model_App
     * @codeCoverageIgnore
     */
    protected function _getApp()
    {
        return Mage::app();
    }

    /**
     * Set default store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->resetStore();
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Reset store
     *
     * @return $this
     */
    public function resetStore()
    {
        $this->_store   = null;
        $this->_storeId = null;
        return $this;
    }

    /**
     * Get default Granify JS link
     *
     * @return string
     */
    public function getDefaultGranifyJsLink()
    {
        return $this->_getStore()->getConfig(self::XML_PATH_DEFAULT_GRANIFY_JS_LINK)
            . '?id=' . $this->getSiteId();
    }

    /**
     * Get URI for post order data
     *
     * @return string
     */
    public function getPostOrderUri()
    {
        return $this->_getStore()->getConfig(self::XML_PATH_POST_ORDER_URI)
            . '?site_id=' . $this->getSiteId();
    }

    /**
     * Get timeout value for post order data
     *
     * @return string
     */
    public function getPostOrderTimeout()
    {
        return $this->_getStore()->getConfig(self::XML_PATH_POST_ORDER_TIMEOUT);
    }

    /**
     * Get URI for get shop info from Granify side
     *
     * @return string
     */
    public function getGranifyShopInfoResourceUri()
    {
        return $this->_getStore()->getConfig(self::XML_PATH_GRANIFY_SHOP_INFO_URI);
    }

    /**
     * Get site ID
     *
     * ID which provided Granify service
     *
     * @return mixed
     */
    public function getSiteId()
    {
        return $this->_getStore($this->_storeId)->getConfig(self::XML_PATH_SITE_ID);
    }

    /**
     * Get API secret
     *
     * @return string
     */
    public function getApiSecret()
    {
        /** @var $helper Mage_Core_Helper_Data */
        $helper = $this->_getHelper('core');
        return $helper->decrypt(
            $this->_getStore($this->_storeId)->getConfig(self::XML_PATH_API_SECRET)
        );
    }

    /**
     * Check ability work with Granify service
     *
     * @return bool
     */
    public function isAble()
    {
        return $this->getSiteId() && $this->getApiSecret();
    }

    /**
     * Get version of package from config
     *
     * @return string
     */
    public function getPackageVersion()
    {
        return (string) $this->_getApp()->getConfig()->getNode(self::XML_PATH_PACKAGE_VERSION);
    }

    /**
     * Get Magento version
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        $name = $this->isModuleEnabled('Enterprise_Enterprise') ? 'Enterprise' : 'Community';
        return $name . ' ' . Mage::getVersion();
    }

    /**
     * Get logger
     *
     * @return Granify_Sales_Model_Logger_HoneyBadger
     */
    public function getLogger()
    {
        return $this->_getModel('granify_sales/logger_honeyBadger');
    }
}
