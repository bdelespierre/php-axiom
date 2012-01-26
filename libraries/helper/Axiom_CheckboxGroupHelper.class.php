<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Checkbox Group Helper Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage helper
 */
class Axiom_CheckboxGroupHelper extends Axiom_BaseHelper {

    /**
     * Inner input's name
     * @var string
     */
    protected $_name;

    /**
     * Current position
     * @internal
     * @var integer
     */
    protected static $_count = 1;

    /**
     * Default constructor
     * @param string $name
     * @param array $values = array()
     */
    public function __construct ($name, $values = array()) {
        parent::__construct('span');
        $this->_name = $name;

        if (!empty($values))
            $this->addOptions($values);
    }

    /**
     * Add an option (a checkbox) to the group
     * @param string $label
     * @param scalar $value
     * @return Axiom_CheckboxGroupHelper
     */
    public function addOption ($label, $value) {
        $this->_children[] = Axiom_InputHelper::export($this->_name, 'checkbox', $value)->setId($id = 'checkbox' . (self::$_count ++));
        $this->_children[] = Axiom_LabelHelper::export($label, $id);
        return $this;
    }
    
    /**
     * Add multiple options at once.
     * The $values parameters must be formatted
     * as follow: { [key: value, ...] }
     * @param array $values
     * @return Axiom_CheckboxGroupHelper
     */
    public function addOptions ($values) {
        foreach ($values as $key => $value)
            $this->addOption($key, $value);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Axiom_BaseHelper::setValue()
     */
    public function setValue ($value) {
        foreach ($this->_children as &$node) {
            if (($node instanceof Axiom_InputHelper) && $node->getValue() == $value) {
                $node->setChecked('checked');
            }
        }

        return $this;
    }
    
    /**
     * Get group's name
     * @return string
     */
    public function getName () {
        return $this->_name;
    }
    
    /**
     * Constructor static alias
     * @param string $name
     * @param array $values = array()
     * @return Axiom_CheckboxGroupHelper
     */
    public static function export ($name, $values = array()) {
        return new self ($name, $values);
    }
}