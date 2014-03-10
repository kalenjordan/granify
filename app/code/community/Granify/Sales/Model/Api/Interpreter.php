<?php
/**
 * API Router class
 *
 * @category    Granify
 * @package     Granify_Sales
 */
class Granify_Sales_Model_Api_Interpreter
{
    /**
     * Default type
     *
     * @var string
     */
    protected $_defaultType = 'application/json';

    /**
     * Interpreter types map
     *
     * @var array
     */
    protected $_typesMap = array(
        'application/json' => 'json',
    );

    /**
     * Interpreters class path base name
     *
     * @var string
     */
    protected $_typeClassPathBase = 'granify_sales/api_interpreter_';

    /**
     * Interpret method
     *
     * @param string $contentType
     * @param string $raw
     * @return array
     */
    public function interpret($contentType, $raw)
    {
        $interpreter = $this->_getInterpreter(
            $this->_matchType($contentType)
        );
        return $interpreter->interpret($raw);
    }

    /**
     * Get interpreter model
     *
     * @param string $type
     * @return Granify_Sales_Model_Api_Interpreter_Interface
     * @throws Exception
     */
    protected function _getInterpreter($type)
    {
        /** @var $model Granify_Sales_Model_Api_Interpreter_Interface */
        $model = $this->_getModel($this->_typeClassPathBase . $type);
        if (!$model) {
            throw new Exception('Interpreter not found.');
        }
        return $model;
    }

    /**
     * Match content type
     *
     * @param string $contentType
     * @return string
     * @throws Granify_Sales_Model_Api_Exception
     */
    protected function _matchType($contentType)
    {
        if (!$contentType) {
            return $this->_typesMap[$this->_defaultType];
        }
        $matched = null;
        foreach ($this->_typesMap as $contentTypeIn => $type) {
            if (false !== strpos($contentType, $contentTypeIn)) {
                $matched = $type;
                break;
            }
        }
        if (!$matched) {
            throw new Granify_Sales_Model_Api_Exception(
                sprintf('Unsupported request content type "%s".', $contentType),
                Granify_Sales_Model_Api_Dispatcher::CODE_NOT_ACCEPTABLE
            );
        }
        return $matched;
    }

    /**
     * Get Mage model
     *
     * @param string $classPath
     * @return Mage_Core_Model_Abstract
     */
    protected function _getModel($classPath)
    {
        return Mage::getModel($classPath);
    }
}
