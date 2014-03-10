<?php
/**
 * API coupons resource class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Resource_PriceRule_V1 extends Granify_Sales_Model_Api_Resource_Abstract
{
    /**
     * Init filters
     */
    public function _init()
    {
        parent::_init();

        $this->_outputOptions = array(
            '__map' => array(
                'code' => 'coupon_code',
                'simple_action' => 'discount_type'
            ),
            'rule_id',
            'name',
            'description',
            'is_active',
            'simple_action',
            'coupon_code',
            'code',
            'discount_amount',
            'discount_qty',
        );

        $this->_inputOptions = array(
            'name' => array(
                'type' => 'string',
                'filter' => 'core::stripTags',
                'required' => true,
            ),
            'description' => array(
                'type' => 'string',
                'filter' => 'core::stripTags',
                'required' => false,
            ),
            'discount_type' => array(
                'name'      => 'simple_action', //magento name, mapping
                'type'      => 'string',
                'required'  => true,
                'default'   => 'by_percent', //used when empty param
                'in_list'   => array(
                    Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION,
                    Mage_SalesRule_Model_Rule::BY_FIXED_ACTION,
                    Mage_SalesRule_Model_Rule::CART_FIXED_ACTION,
                    Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION,
                ),
            ),
            'discount_amount' => array(
                'type' => 'float',
                'required' => true,
            ),
            'discount_qty' => array( //Maximum qty discount is applied to
                'type' => 'float',
                'required' => false,
            ),
            'is_active' => array(
                'type' => 'bool',
                'required' => true,
            ),
            'coupon_code' => array(
                'type' => 'string',
                'required' => true, //required by Granify
            ),
        );
    }

    /**
     * Method get
     *
     * @return array
     * @throws Granify_Sales_Model_Api_Exception
     */
    protected function _get()
    {
        $model = $this->_getDataModel();
        if (!$model->getId()) {
            throw $this->_getNotFoundException();
        }
        $this->_setStatusCode(Granify_Sales_Model_Api_Dispatcher::CODE_OK);
        return $this->_filterOutput($model->getData());
    }

    /**
     * Method get collection
     *
     * @return array
     */
    protected function _getCollection()
    {
        $collection = $this->_getDataModel()->getCollection();
        $return = array();
        /** @var $el Mage_SalesRule_Model_Rule */
        foreach ($collection as $el) {
            $return[] = $this->_filterOutput($el->getData());
        }
        $this->_setStatusCode(Granify_Sales_Model_Api_Dispatcher::CODE_OK);
        return $return;
    }

    /**
     * Method put
     *
     * @return bool
     */
    public function post()
    {
        $model = $this->_getDataModel(false);
        $this->_save($this->_getPostData(), $model);
        $this->_setStatusCode(Granify_Sales_Model_Api_Dispatcher::CODE_CREATED);
        return $this->_getPostReturn($model);
    }

    /**
     * Method post
     *
     * @return bool|string
     * @throws Granify_Sales_Model_Api_Exception
     */
    public function put()
    {
        $model = $this->_getDataModel();
        if (!$model->getId()) {
            throw new Granify_Sales_Model_Api_Exception(
                'Resource for requested URI not found and cannot be created.',
                Granify_Sales_Model_Api_Dispatcher::CODE_NOT_FOUND
            );
        }
        $this->_save($this->_getPostData(), $model);
        $this->_setStatusCode(Granify_Sales_Model_Api_Dispatcher::CODE_OK);
        return 'OK';
    }

    /**
     * Get POST return data
     *
     * @param Mage_SalesRule_Model_Rule $model
     * @return array
     */
    protected function _getPostReturn($model)
    {
        return array('id' => $model->getId());
    }

    /**
     * Get data model
     *
     * @param bool $load
     * @return Mage_SalesRule_Model_Rule
     */
    protected function _getDataModel($load = true)
    {
        $model = $this->_getModel('salesrule/rule');
        if ($load && $this->_getId()) {
            $model->load($this->_getId());
        }
        return $model;
    }

    /**
     * Save to model
     *
     * @param array $data
     * @param Mage_SalesRule_Model_Rule $model
     * @return Granify_Sales_Model_Api_Resource_Abstract
     * @throws Granify_Sales_Model_Api_Exception
     */
    protected function _save(array $data, $model)
    {
        if (!$data) {
            throw new Granify_Sales_Model_Api_Exception('Data is not set.');
        }
        $data = $this->_filterInput($data, (bool)$model->getId());
        $data = $this->_mergeModelData($data, $model);
        $data = $this->_prepareData($data);
        $this->_processModelSave($model, $data);
        return $this;
    }

    /**
     * Merge with model data
     *
     * @param array $data
     * @param Mage_SalesRule_Model_Rule $model
     * @return array
     */
    protected function _mergeModelData(array $data, $model)
    {
        if ($model->getId()) {
            $data = array_merge($model->getData(), $data);
        }
        return $data;
    }

    /**
     * Process save data by model
     *
     * @param Mage_SalesRule_Model_Rule $model
     * @param array $data
     * @return Granify_Sales_Model_Api_Resource_PriceRule_V1
     */
    protected function _processModelSave($model, array $data)
    {
        $this->_validateByModel($model, $data);
        $model->loadPost($data);
        $model->save();
        return $this;
    }

    /**
     * Validate data by model validate method
     *
     * @param Mage_SalesRule_Model_Rule $model
     * @param array $data
     * @throws Mage_Core_Exception
     */
    protected function _validateByModel(Mage_SalesRule_Model_Rule $model, array $data)
    {
        $result = $model->validateData(new Varien_Object($data));
        if ($result !== true) {
            foreach ($result as $errorMessage) {
                throw new Mage_Core_Exception($errorMessage, Granify_Sales_Model_Api_Dispatcher::CODE_BAD_REQUEST);
            }
        }
    }

    /**
     * Prepare data
     *
     * @param array $data
     * @return mixed
     */
    protected function _prepareData(array $data)
    {
        $data['discount_amount']    = $this->_getDiscountAmount($data);
        $data['coupon_type']        = $this->_getCouponType($data);
        $data['customer_group_ids'] = $this->_getAllCustomerGroupIds();
        return $data;
    }

    /**
     * Get discount amount
     *
     * @param array $data
     * @return mixed|null
     */
    protected function _getDiscountAmount($data)
    {
        if (isset($data['simple_action'])
            && $data['simple_action'] == Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION
            && isset($data['discount_amount'])
        ) {
            return min(100, $data['discount_amount']);
        }
        return null;
    }

    /**
     * Get rule coupon type
     *
     * @param string $data
     * @return int
     */
    protected function _getCouponType($data)
    {
        if (empty($data['coupon_code'])) {
            return Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON;
        } else {
            return Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC;
        }
    }

    /**
     * Get all customer group IDs
     *
     * @return array
     */
    protected function _getAllCustomerGroupIds()
    {
        /** @var $collection Mage_Customer_Model_Entity_Group_Collection */
        $collection = $this->_getModel('customer/group')->getCollection();
        return array_keys($collection->load()->toOptionHash());
    }

    /**
     * Method delete
     *
     * @return bool|void
     */
    public function delete()
    {
        $model = $this->_getDataModel();
        if ($model->getId()) {
            $model->delete();
        }
        $this->_setStatusCode(Granify_Sales_Model_Api_Dispatcher::CODE_OK);
        return 'OK';
    }
}
