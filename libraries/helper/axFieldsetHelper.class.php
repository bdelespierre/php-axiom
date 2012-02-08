<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Fieldset Helper Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage helper
 */
class axFieldsetHelper extends axBaseHelper {

    /**
     * An associative map of the lines added to the fieldset
     * @var array
     */
    protected $_fieldset_lines = array();
    
    /**
     * Default constructor
     * @param string $legend = ""
     */
    public function __construct ($legend = "") {
        parent::__construct('fieldset');
        if ($legend)
            $this->appendChild("<legend>$legend</legend>");
    }

    /**
     * Add a form-line to the fieldset.
     * @see axFormLineHelper::export
     * @param string $name
     * @param string $display_name = null
     * @param string $type = "text"
     * @param scalar $value = ""
     * @return axFieldsetHelper
     */
    public function addLine ($name, $display_name = null, $type = "text", $value = "", $class = "") {
        $this->appendChild($this->_fieldset_lines[$name] = axFormLineHelper::export($name, $display_name, $type, $value, $class));
        return $this;
    }
    
	/**
     * Get a given line attached to the fieldset helper
     * @param string $name
     * @return axFormLineHelper
     */
    public function getLine ($name) {
        return isset($this->_fieldset_lines[$name]) ? $this->_fieldset_lines[$name] : null;
    }
    
    /**
     * Mark the $names line as error (adding the CSS error class)
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
     * Fill the fieldset inner inputs automatically
     * with a descriptor.
     * The $desc parameter can be either an array
     * or a axModel instance.
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
     * Constructor static alias
     * @param string $legend = ""
     * @return axFieldsetHelper
     */
    public static function export ($legend = "") {
        return new self ($legend);
    }
}