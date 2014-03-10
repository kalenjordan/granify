<?php
/**
 * Page head block for render HTML tag with granify JS script
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Block_Page_Head extends Mage_Core_Block_Template
{
    /**
     * Get default granify js
     *
     * @return string
     */
    public function getDefaultGranifyJsLink()
    {
        /** @var $helper Granify_Sales_Helper_Data */
        $helper = $this->helper('granify_sales');
        if (!$helper->isAble()) {
            return '';
        }
        return $helper->getDefaultGranifyJsLink();
    }

    /**
     * Get JavaScript with injected link
     *
     * @return string
     */
    public function getJsScript()
    {
        $link = $this->getDefaultGranifyJsLink();
        if (!$link) {
            return '';
        }
        return sprintf(
            '<script type="text/javascript">
                (function (c, a) {
                    window.Granify = a;
                    var b, d, h, e;
                    b = c.createElement("script");
                    b.type = "text/javascript";
                    b.async = !0;
                    b.src = ("https:" === c.location.protocol ? "https:" : "http:") + "%s";
                    d = c.getElementsByTagName("script")[0];
                    d.parentNode.insertBefore(b, d);
                })(document, window.Granify || []);
            </script>',
            $link
        ) . PHP_EOL;
    }

    /**
     * Render HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getJsScript();
    }
}
