<?php
/**
 * Block for render cart data bypass Full Page Cache
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Cache_Container_Cart extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Get identifier from cookies
     *
     * @return string
     */
    protected function _getIdentifier()
    {
        return $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, '');
    }

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        $str = 'GRANIFY_CART_INFO_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
        return $str;
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $blockClass = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        /** @var $block Mage_Core_Block_Template */
        $block = new $blockClass;
        $block->setTemplate($template);
        return $block->toHtml();
    }

    /**
     * Disable caching
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param null $lifetime
     * @return $this|bool
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }
}
