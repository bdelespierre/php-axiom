<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Configuration Item Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage configuration
 */
final class axConfigurationItem {

    /**
     * Parameter value
     * @var mixed
     */
    protected $_v;

    /**
     * Children parameters
     * @var array
     */
    protected $_c;

    /**
     * Value setter
     * @param mixed $v
     * @return void
     */
    public function setValue ($v) {
        $this->_v = $v;
    }

    /**
     * Valxue getter
     * @return mixed
     */
    public function getValue () {
        return isset($this->_v) ? $this->_v : null;
    }

    /**
     * Child getter
     *
     * Note: if the child doesn't exists, an
     * empty child will be created and returned.
     * Thus you will never face an error accessing
     * an inexisting entry.
     *
     * @param string $k
     * @return axConfigurationItem
     */
    public function __get ($k) {
        if (isset($this->_c[$k]))
            return $this->_c[$k];
        return $this->_c[$k] = new self;
    }

    /**
     * toString
     * @return string
     */
    public function __toString () {
        return (string)$this->getValue();
    }
}