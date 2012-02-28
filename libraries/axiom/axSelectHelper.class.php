<?php
/**
 * @brief Select helper class file
 * @file axSelectHelper.class.php
 */

/**
 * @brief Select Helper Class
 *
 * @class axSelectHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axSelectHelper extends axBaseHelper {

    /**
     * @brief Constructor
     * @param string $name
     * @param array $values @optional @default{array()}
     * @param boolean $multiple @optional @default{false}
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
     * @copydoc axBaseHelper::setValue()
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
            throw new RuntimeException("First parameter is expected to be scalar or array, " . gettype($value) . " given", 3004);
        	
        return $this;
    }

    /**
     * @copydoc axBaseHelper::getValue()
     */
    public function getValue () {
        return null;
    }

    /**
     * @brief Add an option to the select
     * @param strign $name
     * @param scalar $value
     */
    public function addOption ($name, $value) {
        $this->_children[$value] = axOptionHelper::export($name,$value);
        return $this;
    }

    /**
     * @brief Add multiple options at once.
     * 
     * @param array $values
     * @return axSelectHelper
     */
    public function addOptions ($array) {
        foreach ($array as $key => $value) {
            $this->addOption($key, $value);
        }
        return $this;
    }

    /**
     * @copydoc axSelectHelper::__construct()
     * @brief Constructor static alias
     * @return axSelectHelper
     */
    public static function export ($name, $values = array(), $multiple = false) {
        return new self ($name, $values, $multiple);
    }

}