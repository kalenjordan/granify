<?php
/**
 * Notification block about set up Granify Service
 *
 * @category    Granify
 * @package     Granify_Sales
 * @method string getFieldsetLabel()
 * @method $this setFieldsetLabel(string $label)
 */
class Granify_Sales_Block_Adminhtml_System_Config_Fieldset_Notice
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Render custom fieldSet
     *
     * @param Varien_Data_Form_Element_Abstract $fieldSet
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $fieldSet)
    {
        $originalData = $fieldSet->getOriginalData();
        $this->setTemplate($originalData['template']);

        $this->addData(array(
            'fieldset_label' => $fieldSet->getLegend(),
        ));
        return $this->toHtml();
    }
}
