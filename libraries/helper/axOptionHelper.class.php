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
 * @package libaxiom
 * @subpackage helper
 */
class axOptionHelper extends axBaseHelper {

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
     * @see axBaseHelper::setValue()
     */
    public function setValue ($value) {
        $this->_attributes['value'] = $value;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see axBaseHelper::getValue()
     */
    public function getValue () {
        return $this->_attributes['value'];
    }

    /**
     * Constructor static alias
     * @param strign $name
     * @param scalar $value = ""
     * @return axOptionHelper
     */
    public static function export ($name, $value = "") {
        return new self ($name, $value);
    }
}