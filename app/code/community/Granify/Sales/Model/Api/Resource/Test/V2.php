<?php
/**
 * API test resource class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Resource_Test_V2 extends Granify_Sales_Model_Api_Resource_Test_V1
{
    /**
     * Method get
     *
     * @return array|void
     */
    public function get()
    {
        if ($this->_getId()) {
            if ($this->_getId() == 'testId') {
                return array(
                    'id' => 'testId',
                    'name' => 'My test el',
                );
            }
        } else {
            return array(array(
                'id' => 'testId',
                'name' => 'My test',
            ));
        }
        return array();
    }
}
