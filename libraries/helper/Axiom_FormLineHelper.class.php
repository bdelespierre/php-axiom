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
class Axiom_FormLineHelper extends Axiom_BaseHelper {
    
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

        $this->_children['label'] = Axiom_LabelHelper::export($display_name, $name);

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
                $input = Axiom_InputHelper::export($name, $type, $value);
                break;
                
            case 'textarea':
                $input  = Axiom_TextareaHelper::export($name, $value);
                break;

            case 'select':
                $input = Axiom_SelectHelper::export($name, $value);
                break;

            case 'radio-group':
                $input = Axiom_RadioGroupHelper::export($name, $value);
                break;

            case 'checkbox-group':
                $input = Axiom_CheckboxGroupHelper::export($name, $value);
                break;

            default:
                throw new LogicException("Given Axiom_FormLineHelper type mismatch with available types", 3006);
        }

        $this->_children['input'] = $input;
        
        if ($class)
            $this->getInput()->setClass($class);
    }

    /**
     * (non-PHPdoc)
     * @see Axiom_BaseHelper::setValue()
     */
    public function setValue ($value) {
        $this->getInput()->setValue($value);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Axiom_BaseHelper::getValue()
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
     * @return Axiom_FormLineHelper
     */
    public function setChecked ($c) {
        $this->getInput()->setChecked($c);
        return $this;
    }

    /**
     * Get the form line's input
     * @return Axiom_BaseHelper
     */
    public function getInput () {
        return $this->_children['input'];
    }

    /**
     * Get the form line's label
     * @return Axiom_LabelHelper
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
     * @return Axiom_FormLineHelper
     */
    public static function export ($name, $display_name = null, $type = "text", $value = "", $class = "") {
        return new self ($name, $display_name, $type, $value, $class);
    }
}