<?php
/**
 * Notification block about set up Granify Service
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Block_Adminhtml_Notification extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Check ability Granify work
     *
     * @return string
     */
    public function isAble()
    {
        /** @var $helper Granify_Sales_Helper_Data */
        $helper = $this->helper('granify_sales');
        return $helper->isAble();
    }

    /**
     * Get config URL for set up Site ID
     *
     * @return string
     */
    public function getConfigUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit', array('section' => 'granify'));
    }
}
