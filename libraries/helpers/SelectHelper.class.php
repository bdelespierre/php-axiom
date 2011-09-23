<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Select Helper Class
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage SelectHelper
 */
class SelectHelper extends BaseHelper {

    /**
     * Default constructor
     * @param string $name
     * @param array $values = array()
     * @param boolean $multiple = false
     */
    public function __construct ($name, $values = array(), $multiple = false) {
        parent::__construct('select', array('name' => $name));

        $this->addOption("--", "");
        if (!empty($values))
            $this->addOptions($values);

        if ($multiple)
            $this->setMultiple('multiple');
    }

    /**
     * (non-PHPdoc)
     * @see BaseHelper::setValue()
     */
    public function setValue ($value) {
        if (is_scalar($value)) {
            if (isset($this->_children[$value]))
                $this->_children[$value]->setSelected("selected");
        }
        elseif (is_array($value)) {
            foreach ($value as $val)
                $this->setValue($val);
        }
        else
            throw new RuntimException("First parameter is expected to be scalar or array, " . gettype($value) . " given", 3004);
        	
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BaseHelper::getValue()
     */
    public function getValue () {
        return null;
    }

    /**
     * Add an option to the select
     * @param strign $name
     * @param scalar $value
     */
    public function addOption ($name, $value) {
        $this->_children[$value] = OptionHelper::export($name,$value);
        return $this;
    }

    /**
     * Add multiple options at once.
     * The $values parameters must be formatted
     * as follow: { [key: value, ...] }
     * @param array $values
     * @return SelectHelper
     */
    public function addOptions ($array) {
        foreach ($array as $key => $value) {
            $this->addOption($key, $value);
        }
        return $this;
    }

    /**
     * Constructor static alias
     * @param string $name
     * @param array $values = array()
     * @param boolean $multiple = false
     * @return SelectHelper
     */
    public static function export ($name, $values = array(), $multiple = false) {
        return new self ($name, $values, $multiple);
    }

}