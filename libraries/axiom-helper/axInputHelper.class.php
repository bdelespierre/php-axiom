<?php
/**
 * @brief Input helper class file
 * @file axInputHelper.class.php
 */

/**
 * @brief Input Helper Class
 * 
 * @class axInputHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axInputHelper extends axBaseHelper {

    /**
     * @brief Constructor
     * @param string $name The input's name attribute value
     * @param string $type @optional @default{"text"} The input's type attribute value
     * @param scalar $value @optional @default{""} The input's value attribute value
     */
    public function __construct ($name, $type = "text", $value = "") {
        parent::__construct('input', array('name' => $name, 'value' => $value, 'type' => $type));
    }

    /**
     * @copydoc axBaseHelper::getValue()
     */
    public function getValue () {
        return $this->_attributes['value'];
    }

    /**
     * @copydoc axBaseHelper::setValue()
     */
    public function setValue ($value) {
        $this->_attributes['value'] = $value;
        return $this;
    }

    /**
     * @copydoc axBaseHelper::appendChild()
     */
    public function appendChild ($node) {
        throw new LogicException("Cannot append nodes in input tags", 3005);
    }
    
	/**
	 * @copydoc axInputHelper::__construct()
	 * @static
     * @brief Constructor static alias
     * @return axInputHelper
     */
    public static function export ($name, $type = "text", $value = "") {
        return new self($name, $type, $value);
    }
}