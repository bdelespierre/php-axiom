<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Form Helper Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage helper
 */
class axFormHelper extends axBaseHelper {

    /**
     * An associative map of the lines added to the form
     * @var array
     */
    protected $_form_lines = array();
    
    /**
     * A list of fieldsets added to the form
     * @var array
     */
    protected $_form_fieldsets = array();
    
    /**
     * Default constructor
     * @param string $url = ''
     * @param string $method = 'post'
     * @param string $enctype = null
     */
    public function __construct ($url = '', $method = 'post', $enctype = null) {
        parent::__construct ('form', array('action' => $url, 'method' => $method));
        if ($enctype)
            $this->setEnctype($enctype);
    }

    /**
     * Add a form-line to the form
     * @see axFormLineHelper::export
     * @param string $name
     * @param string $display_name = null
     * @param string $type = "text"
     * @param scalar $value = ""
     * @return axFormHelper
     */
    public function addLine ($name, $display_name = null, $type = "text", $value = "", $class = "") {
        $this->appendChild($this->_form_lines[$name] = axFormLineHelper::export($name, $display_name, $type, $value, $class));
        return $this;
    }
    
    /**
     * Get a given line attached to the form helper
     * @param string $name
     * @return axFormLineHelper
     */
    public function getLine ($name) {
        return isset($this->_form_lines[$name]) ? $this->_form_lines[$name] : null;
    }
    
    /**
     * Mark the given lines as error (adding the CSS error class)
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
     * Add a fieldset to the form
     * @see axFieldsetHelper::export
     * @param strign $legend = ""
     * @return axFieldsetHelper
     */
    public function addFieldset ($legend = "") {
        return $this->appendChild($this->_fieldsets[] = axFieldsetHelper::export($legend));
    }
    
    /**
     * Fill the form inner inputs and fieldsets
     * automatically with a descriptor.
     * The $desc parameter can be either an array
     * or a axModel instance.
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
     * Constructor static alias
     * @param strign $url  = ''
     * @param strign $method = 'post'
     * @param string $enctype = null
     * @return axFormHelper
     */
    public static function export ($url = '', $method = 'post', $enctype = null) {
        return new self ($url, $method, $enctype);
    }
}