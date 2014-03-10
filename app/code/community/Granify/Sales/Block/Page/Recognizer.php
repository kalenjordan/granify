<?php
/**
 * Page head block for render HTML tag with granify JS script
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Block_Page_Recognizer extends Mage_Core_Block_Template
{
    /**
     * Get default granify js
     *
     * @return string
     */
    public function getJsScript()
    {
        /** @var $helper Granify_Sales_Helper_Data */
        $helper = $this->helper('granify_sales');
        if (!$helper->isAble()) {
            return '';
        }
        /** @var $recognizer Granify_Sales_Helper_Page_Recognizer */
        $recognizer = $this->helper('granify_sales/page_recognizer');
        return sprintf(
            '<script type="text/javascript">
                var GRANIFY_MAGE = GRANIFY_MAGE || {};
                GRANIFY_MAGE.pageType = \'%s\';
            </script>' . PHP_EOL,
            $recognizer->recognize()
        );
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
