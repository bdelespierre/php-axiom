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
 * @version $Rev$
 * @subpackage FormHelper
 */
class FormHelper extends BaseHelper {

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
     * @see FormLineHelper::export
     * @param string $name
     * @param string $display_name = null
     * @param string $type = "text"
     * @param scalar $value = ""
     * @return FormHelper
     */
    public function addLine ($name, $display_name = null, $type = "text", $value = "", $class = "") {
        $this->appendChild(FormLineHelper::export($name, $display_name, $type, $value, $class));
        return $this;
    }
    
    /**
     * Add a fieldset to the form
     * @see FieldsetHelper::export
     * @param strign $legend = ""
     * @return FieldsetHelper
     */
    public function addFieldset ($legend = "") {
        return $this->appendChild(FieldsetHelper::export($legend));
    }
    
    /**
     * Fill the form inner inputs and fieldsets
     * automatically with a descriptor.
     * The $desc parameter can be either an array
     * or a Model instance.
     * @param mixed $desc
     * @return FormHelper
     */
    public function autoFill ($desc) {
        foreach ($this->_children as &$node) {
            if ($node instanceof FieldsetHelper) {
                $node->autoFill($desc);
            }
            if ($node instanceof Helper) {
                $name = $node->getName();
                if (is_object($desc) && $desc instanceof Model && isset($desc->$name)) {
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
     * @return FormHelper
     */
    public static function export ($url = '', $method = 'post', $enctype = null) {
        return new self ($url, $method, $enctype);
    }
}