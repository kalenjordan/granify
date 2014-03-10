<?php
/**
 * API test resource class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Resource_Test_V1 extends Granify_Sales_Model_Api_Resource_Abstract
{
    /**
     * Method get
     *
     * @return array
     */
    public function _get()
    {
        if ($this->_getId() == 'testId') {
            return array(
                'id' => 'testId',
                'name' => 'My test',
                'version' => 'VERSION 1',
            );
        }
        return array();
    }

    /**
     * Method GET for collections
     *
     * @return array
     */
    protected function _getCollection()
    {
        return array(array(
            'id' => 'testId',
            'name' => 'My test',
            'version' => 'VERSION 1',
        ));
    }

    /**
     * Method put
     *
     * @return bool|void
     */
    public function put()
    {
        return array('put_data' => $this->_getPostData());
    }

    /**
     * Method post
     *
     * @return bool|void
     */
    public function post()
    {
        return array('post_data' => $this->_getPostData());
    }
}
