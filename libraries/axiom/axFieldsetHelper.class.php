<?php
/**
 * @brief Fieldset helper class file
 * @file axFieldsetHelper.class.php
 */

/**
 * @brief HTML Fieldset Helper Class
 * 
 * This class creates and manages a form fieldset.
 * 
 * @class axFieldsetHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axFieldsetHelper extends axBaseHelper {

    /**
     * @brief An associative map of the lines added to the fieldset
     * @property array $_fieldset_lines
     */
    protected $_fieldset_lines = array();
    
    /**
     * @brief Constructor
     * @param string $legend @optional @default{""} The fieldset legend (if any)
     */
    public function __construct ($legend = "") {
        parent::__construct('fieldset');
        if ($legend)
            $this->appendChild("<legend>$legend</legend>");
    }

    /**
     * @brief Add a form-line to the fieldset.
     * 
     * @see axFormLineHelper::export()
     * @param string $name Input name
     * @param string $display_name @optional @default{null} The name to display
     * @param string $type @optional @default{"text"} The type of input to display
     * @param scalar $value @optional @default{""} The value of input to display
     * @param string $class @optional @default{""} The CSS classe(s) to apply
     * @return axFieldsetHelper
     */
    public function addLine ($name, $display_name = null, $type = "text", $value = "", $class = "") {
        $this->appendChild($this->_fieldset_lines[$name] = axFormLineHelper::export($name, $display_name, $type, $value, $class));
        return $this;
    }
    
	/**
     * @brief Get a given line attached to the fieldset helper
     * 
     * @param string $name
     * @return axFormLineHelper
     */
    public function getLine ($name) {
        return isset($this->_fieldset_lines[$name]) ? $this->_fieldset_lines[$name] : null;
    }
    
    /**
     * @brief Mark the @c $names line as error (adding the CSS 'error' class)
     * 
     * @param array $names
     * @return axFieldsetHelper
     */
    public function setErrors (array $names) {
        foreach ($names as $name) {
            if ($line = $this->getLine($name))
                $line->setClass($line->getClass() ? $line->getClass() . ' error' : 'error');
        }
        return $this;
    }
    
    /**
     * @breif Fill the fieldset inner inputs automatically with a descriptor.
     * 
     * The @c $desc parameter can be either an array or a axModel instance.
     * 
     * @param mixed $desc
     * @return axFieldsetHelper
     */
    public function autoFill ($desc) {
        foreach ($this->_children as &$node) {
            if ($node instanceof Helper) {
                $name = $node->getName();
                if (is_object($desc) && $desc instanceof axModel && isset($desc->$name)) {
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
     * @copydoc axFieldsetHelper::__construct()
     * @brief Constructor static alias
     * @return axFieldsetHelper
     */
    public static function export ($legend = "") {
        return new self ($legend);
    }
}