<?php
/**
 * Input-filter model
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Resource_Input
{
    /**
     * Options
     *
     * @var array
     */
    protected $_options;

    /**
     * Set options
     *
     * @param array $options
     * @return Granify_Sales_Model_Api_Resource_Input
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * Process input data
     *
     * @param array $data
     * @return array
     * @throws Mage_Core_Exception
     */
    public function process(array $data)
    {
        if (empty($this->_options['rules'])) {
            //skip filtering without options
            return $data;
        }
        $filtered = array();
        foreach ($this->_options['rules'] as $name => $rule) {
            if (!isset($data[$name])) {
                $data[$name] = null;;
            }

            //typify value
            $value = $this->_typifyValue($rule, $data[$name]);

            //get default if empty
            if (isset($rule['default']) && empty($value)) {
                $value = $rule['default'];
            }

            //filter value by filter helper method
            if (isset($rule['filter']) && !empty($value)) {
                list($helperName, $method) = explode('::', $rule['filter']);
                $helper = $this->_getHelper($helperName);
                if ($helper && is_callable(array($helper, $method))) {
                    $value = $helper->{$method}($value);
                }
            }

            //check required
            if (empty($this->_options['skip_requiring']) && !empty($rule['required']) && empty($value)) {
                throw new Mage_Core_Exception(
                    sprintf('Parameter "%s" is required.', $name),
                    Granify_Sales_Model_Api_Dispatcher::CODE_BAD_REQUEST
                );
            }

            //check in list
            if (!empty($rule['in_list']) && !in_array($value, $rule['in_list'])) {
                throw new Mage_Core_Exception(
                    sprintf(
                        'Parameter "%s" has unknown value. It should be one of list: %s.',
                        $name,
                        implode(', ', $rule['in_list'])
                    ),
                    Granify_Sales_Model_Api_Dispatcher::CODE_BAD_REQUEST
                );
            }

            //map value to internal name
            if (isset($rule['name'])) {
                $name = $rule['name'];
            }

            $filtered[$name] = $value;
        }
        return $filtered;
    }

    /**
     * @param array $options
     * @param mixed $value
     * @return array|bool|int|string
     */
    protected function _typifyValue($options, $value)
    {
        if (!isset($options['type'])) {
            $options['type'] = null;
        }
        switch ($options['type']) {
            case 'array':
                $value = (array)$value;
                break;

            case 'string':
                $value = (string)$value;
                break;

            case 'int': //no break
            case 'integer':
                $value = (int)$value;
                break;

            case 'bool':
                $value = (bool)$value;
                break;

            //no default
        }
        return $value;
    }

    /**
     * Get Mage helper
     *
     * @param string $classPath
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper($classPath)
    {
        return Mage::helper($classPath);
    }
}
