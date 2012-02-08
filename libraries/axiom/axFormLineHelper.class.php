<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Form Line Helper Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage helper
 */
class axFormLineHelper extends axBaseHelper {
    
    /**
     * Default constructor
     * @param string $name
     * @param string $display_name = null
     * @param string $type = "text"
     * @param scalar $value = ""
     * @param array $classes = array()
     */
    public function __construct ($name, $display_name = null, $type = "text", $value = "", $class = "") {
        parent::__construct('div');

        if (!$display_name)
            $display_name = $name;

        $this->_children['label'] = axLabelHelper::export($display_name, $name);

        switch (strtolower($type)) {
            case 'text':
            case 'image':
            case 'hidden':
            case 'checkbox':
            case 'radio':
            case 'submit':
            case 'button':
            case 'file':
            case 'password':
                $input = axInputHelper::export($name, $type, $value);
                break;
                
            case 'textarea':
                $input  = axTextareaHelper::export($name, $value);
                break;

            case 'select':
                $input = axSelectHelper::export($name, $value);
                break;

            case 'radio-group':
                $input = axRadioGroupHelper::export($name, $value);
                break;

            case 'checkbox-group':
                $input = axCheckboxGroupHelper::export($name, $value);
                break;

            default:
                throw new LogicException("Given axFormLineHelper type mismatch with available types", 3006);
        }

        $this->_children['input'] = $input;
        
        if ($class)
            $this->getInput()->setClass($class);
    }

    /**
     * (non-PHPdoc)
     * @see axBaseHelper::setValue()
     */
    public function setValue ($value) {
        $this->getInput()->setValue($value);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see axBaseHelper::getValue()
     */
    public function getValue () {
        return $this->getInput()->getValue();
    }
    
    /**
     * Get the form line's input name
     * @return string
     */
    public function getName () {
        return $this->getInput()->getName();
    }
    
    /**
     * Get the inner input type
     * @return string
     */
    public function getType () {
        return $this->getInput()->getType();
    }
    
    /**
     * Set the internal field as checked or not
     * @param string $c
     * @return axFormLineHelper
     */
    public function setChecked ($c) {
        $this->getInput()->setChecked($c);
        return $this;
    }

    /**
     * Get the form line's input
     * @return axBaseHelper
     */
    public function getInput () {
        return $this->_children['input'];
    }

    /**
     * Get the form line's label
     * @return axLabelHelper
     */
    public function getLabel () {
        return $this->_children['label'];
    }

    /**
     * Constructor static helper
     * @param string $name
     * @param string $display_name = null
     * @param string $type = "text"
     * @param scalar $value = ""
     * @return axFormLineHelper
     */
    public static function export ($name, $display_name = null, $type = "text", $value = "", $class = "") {
        return new self ($name, $display_name, $type, $value, $class);
    }
}