<?php
/**
 * API stores info resource class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Resource_Store_V1 extends Granify_Sales_Model_Api_Resource_Abstract
{
    /**
     * Method get collection
     *
     * @return array
     */
    protected function _getCollection()
    {
        /** @var $helper Granify_Sales_Helper_Data */
        $helper = $this->_getHelper('granify_sales');
        $currentStoreId = $this->_store->getId();
        $groups = array();
        /** @var $group Mage_Core_Model_Store_Group */
        foreach ($this->_getApp()->getWebsite()->getGroups() as $group) {
            $stores = array();
            $defaultStoreId = $group->getDefaultStoreId();
            /** @var $store Mage_Core_Model_Store */
            foreach ($group->getStores() as $store) {
                if (!$store->getIsActive()) {
                    continue;
                }
                $installed = $helper->setStoreId($store->getId())->isAble();
                $stores[] = array(
                    'direct_url'  => $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK),
                    'code'        => $store->getCode(),
                    'name'        => $store->getName(),
                    'default'     => $store->getId() == $defaultStoreId,
                    'current'     => $store->getId() == $currentStoreId,
                    'installed'   => $installed,
                    'site_id'     => $installed ? $helper->getSiteId() : '',
                );
            }

            $groups[] = array(
                'name'          => $group->getData('name'),
                'stores'        => $stores,
            );
        }

        $this->_setStatusCode(Granify_Sales_Model_Api_Dispatcher::CODE_OK);

        return array('store_groups' => $groups);
    }
}
