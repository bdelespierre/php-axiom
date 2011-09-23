<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Option Helper CLass
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage OptionHelper
 */
class OptionHelper extends BaseHelper {

    /**
     * Default constructor
     * @param string $name
     * @param scalar $value
     */
    public function __construct ($name, $value = "") {
        parent::__construct('option', array('value' => $value), $name);
    }

    /**
     * (non-PHPdoc)
     * @see BaseHelper::setValue()
     */
    public function setValue ($value) {
        $this->_attributes['value'] = $value;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BaseHelper::getValue()
     */
    public function getValue () {
        return $this->_attributes['value'];
    }

    /**
     * Constructor static alias
     * @param strign $name
     * @param scalar $value = ""
     * @return OptionHelper
     */
    public static function export ($name, $value = "") {
        return new self ($name, $value);
    }
}