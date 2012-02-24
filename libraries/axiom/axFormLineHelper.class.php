<?php
/**
 * @brief Form line helper class file
 * @file axFormLineHelper.class.php
 */

/**
 * @brief Form Line Helper Class
 * 
 * @todo axFormLineHelper long description
 * @class axFormLineHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axFormLineHelper extends axBaseHelper {
    
    /**
     * @brief Constructor
     * 
     * The type parameter can be one of (the class used for the input field is in parenthesis):
     * @li text (axInputHelper)
     * @li image (axInputHelper)
     * @li hidden (axInputHelper)
     * @li checkbox (axInputHelper)
     * @li radio (axInputHelper)
     * @li submit (axInputHelper)
     * @li button (axInputHelper)
     * @li file (axInputHelper)
     * @li password (axInputHelper)
     * @li textarea (axTextareaHelper)
     * @li select (axSelectHelper)
     * @li radio-group (axRadioGroupHelper)
     * @li checkbox-group (axCheckboxGroupHelper)
     * 
     * @param string $name The line's name
     * @param string $display_name @optional @default{null} The display name
     * @param string $type @optional @default{"text"} The type of line
     * @param scalar $value @optional @default{""} The input's values
     * @param string $classes @optional @default{array()} The CSS classe(s)
     * @throws LogicException If the type doesn't correspond to a valid input type
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
     * @copydoc axBaseHelper::setValue()
     */
    public function setValue ($value) {
        $this->getInput()->setValue($value);
        return $this;
    }

    /**
     * @copydoc axBaseHelper::getValue()
     */
    public function getValue () {
        return $this->getInput()->getValue();
    }
    
    /**
     * @brief Get the form line's input name
     * 
     * @return string
     */
    public function getName () {
        return $this->getInput()->getName();
    }
    
    /**
     * @brief Get the inner input type
     * 
     * @return string
     */
    public function getType () {
        return $this->getInput()->getType();
    }
    
    /**
     * @brief Set the internal field as checked or not
     * 
     * @param string $c
     * @return axFormLineHelper
     */
    public function setChecked ($c) {
        $this->getInput()->setChecked($c);
        return $this;
    }

    /**
     * @brief Get the form line's input
     * 
     * @return axBaseHelper
     */
    public function getInput () {
        return $this->_children['input'];
    }

    /**
     * @brief Get the form line's label
     * @return axLabelHelper
     */
    public function getLabel () {
        return $this->_children['label'];
    }

    /**
     * @copydoc axFormLineHelper::__construct()
     * @brief Constuctor static alias
     * @return axFormLineHelper
     */
    public static function export ($name, $display_name = null, $type = "text", $value = "", $class = "") {
        return new self ($name, $display_name, $type, $value, $class);
    }
}