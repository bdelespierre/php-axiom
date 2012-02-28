<?php
/**
 * @brief Option helper class file
 * @file axOptionHelper.class.php
 */

/**
 * @brief Option Helper CLass
 *
 * @class axOptionHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axOptionHelper extends axBaseHelper {

    /**
     * @brief Constructor
     * @param string $name
     * @param scalar $value @optional @default{""}
     */
    public function __construct ($name, $value = "") {
        parent::__construct('option', array('value' => $value), $name);
    }

    /**
     * @copydoc axBaseHelper::setValue()
     */
    public function setValue ($value) {
        $this->_attributes['value'] = $value;
        return $this;
    }

    /**
     * @copydoc axBaseHelper::getValue()
     */
    public function getValue () {
        return $this->_attributes['value'];
    }

    /**
     * @copydoc axOptionHelper::__construct()
     * @brief Constructor static alias
     * @return axOptionHelper
     */
    public static function export ($name, $value = "") {
        return new self ($name, $value);
    }
}