<?php
/**
 * @brief Form helper class file
 * @file axFormHelper.class.php
 */

/**
 * @brief HTML Form Helper Class
 *
 * @todo axFormHelper long description
 * @class axFormHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axFormHelper extends axBaseHelper {

    /**
     * @brief An associative map of the lines added to the form
     * @property array $_form_lines
     */
    protected $_form_lines = array();
    
    /**
     * @brief A list of fieldsets added to the form
     * @property array $_form_fieldsets
     */
    protected $_form_fieldsets = array();
    
    /**
     * @brief Constructor
     * 
     * @param string $url @optional @default{''} Form url attribute
     * @param string $method @optional @default{'post'} Form method attribute
     * @param string $enctype @optional @default{null} Form enctyppe attribute
     */
    public function __construct ($url = '', $method = 'post', $enctype = null) {
        parent::__construct ('form', array('action' => $url, 'method' => $method));
        if ($enctype)
            $this->setEnctype($enctype);
    }

    /**
     * @brief Add a form-line to the form
     * 
     * @see axFormLineHelper::export()
     * @param string $name The line's name
     * @param string $display_name @optional @default{null} The display name
     * @param string $type @optional @default{"text"} The form line's type
     * @param scalar $value @optional @default{""} The input's value
     * @param string $class @optional @default{""} The CSS class
     * @return axFormHelper
     */
    public function addLine ($name, $display_name = null, $type = "text", $value = "", $class = "") {
        $this->appendChild($this->_form_lines[$name] = axFormLineHelper::export($name, $display_name, $type, $value, $class));
        return $this;
    }
    
    /**
     * @brief Get a given line attached to the form helper
     * 
     * @param string $name
     * @return axFormLineHelper
     */
    public function getLine ($name) {
        return isset($this->_form_lines[$name]) ? $this->_form_lines[$name] : null;
    }
    
    /**
     * @breif Mark the given lines as error (adding the CSS error class)
     * 
     * @param array $names
     * @return axFormHelper
     */
    public function setErrors (array $names) {
        foreach ($names as $index => $name) {
            if ($line = $this->getLine($name)) {
                $line->setClass($line->getClass() ? $line->getClass() . ' error' : 'error');
                unset($names[$index]);
            }
        }
        
        if (empty($names))
            return $this;
        
        foreach ($this->_fieldsets as $fieldset)
            $fieldset->setErrors($names);
        
        return $this;
    }
    
    /**
     * @brief Add a fieldset to the form
     * 
     * @see axFieldsetHelper::__construct()
     * @param strign $legend @optional @default{""}
     * @return axFieldsetHelper
     */
    public function addFieldset ($legend = "") {
        return $this->appendChild($this->_fieldsets[] = axFieldsetHelper::export($legend));
    }
    
    /**
     * @brief Fill the form inner inputs and fieldsets automatically with a descriptor.
     * 
     * The @c $desc parameter can be either an array or a axModel instance.
     * 
     * @param mixed $desc
     * @return axFormHelper
     */
    public function autoFill ($desc) {
        foreach ($this->_children as &$node) {
            if ($node instanceof axFieldsetHelper) {
                $node->autoFill($desc);
            }
            if ($node instanceof Helper) {
                $name = $node->getName();
                if ($desc instanceof axModel && isset($desc->$name)) {
                    $value = $desc->$name;
                }
                elseif (is_array($desc) && isset($desc[$name])) {
                    $value = $desc[$name];
                }
                else {
                    continue;
                }
                
                if ($node->getType() == 'radio' || $node->getType() == 'checkbox') {
                    if ($node->getValue() == $value)
                        $node->setChecked("checked");
                }
                else {
                    $node->setValue($value);
                }
                
                unset($name);
            }
        }
        return $this;
    }
    
    /**
     * @copydoc axFormHelper::__construct()
     * @brief Constructor static alias
     * @return axFormHelper
     */
    public static function export ($url = '', $method = 'post', $enctype = null) {
        return new self ($url, $method, $enctype);
    }
}