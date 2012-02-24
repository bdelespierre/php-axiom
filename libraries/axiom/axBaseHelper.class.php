<?php
/**
 * @brief Base helper class file
 * @file axBaseController.class.php
 */

/**
 * @brief Base class for helpers
 * 
 * This class provides a default implementation for most axHelper methods.
 * 
 * @class axBaseHelper
 * @ingroup Helper
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @copyright http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
abstract class axBaseHelper implements axHelper {

    /**
     * @brief Node name
     * @property string $_node_name;
     */
    protected $_node_name;

    /**
     * @brief Node value
     * @property mixed $_node_value
     */
    protected $_node_value;

    /**
     * @brief Node attributes
     * @property array $_attributes
     */
    protected $_attributes;

    /**
     * @brief Node children
     * @property array $_children
     */
    protected $_children;

    /**
     * @brief Constructor
     * 
     * @param string $node_name The node name
     * @param array $attributes @optional @default{array()} The node attributes 
     * @param mixed $node_value @optional @default{null} The node value
     */
    public function __construct ($node_name, $attributes = array(), $node_value = null) {
        $this->_node_name  = $node_name;
        $this->_node_value = $node_value;
        $this->_attributes = $attributes;
    }

    /**
     * @copybrief axHelper::setAttributes
     * @see Helper::setAttributes
     */
    public function setAttributes ($attributes) {
        foreach ($attributes as $key => $value) {
            $this->_attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * @copydoc axHelper::setValue()
     */
    public function setValue ($value) {
        $this->_node_value = $value;
    }

    /**
     * @copydoc axHelper::getValue()
     */
    public function getValue () {
        return $this->_node_value;
    }

    /**
     * @brief __call implementation
     * 
     * Enable the use of axBaseHelper::setX() and axBaseHelper::getX() where X is an attribute of the current node.
     * Will return the current instance for chaining purposes.
     * 
     * @param string $method
     * @param array $args
     * @return axBaseHelper
     */
    public function __call ($method, $args) {
        if (strpos($method, 'set') === 0) {
            $this->_attributes[lcfirst(substr($method, 3))] = $args[0];
        }
        elseif (strpos($method, 'get') === 0) {
            return isset($this->_attributes[lcfirst(substr($method, 3))]) ?
                $this->_attributes[lcfirst(substr($method, 3))] : null;
        }
        return $this;
    }

    /**
     * @copydoc axHelper::appendChild()
     */
    public function appendChild ($node) {
        return $this->_children[] = $node;
    }

    /**
     * @copydoc axHelper::prependChild()
     */
    public function prependChild ($node) {
        array_unshift($this->_children, $node);
        return $this->_children[0];
    }

    /**
     * @copydoc axHelper::__toString()
     */
    public function __toString () {
        $attr = array();
        foreach ($this->_attributes as $name => $value) {
            $attr[] = "$name=\"$value\"";
        }
        $node = "<{$this->_node_name} " . implode(' ', $attr);

        if (!count($this->_children) && ($this->_node_value === null))
            return $node . " />";
        else
            $node .= ">";

        if ($this->_node_value !== null)
            $node .= $this->_node_value;

        if (count($this->_children))
            $node .= implode($this->_children);

        return $node . "</{$this->_node_name}>";
    }
}

if (!function_exists("lcfirst")) {
    /**
     * @fn string lcfirst (string $string)
     * @brief lcfirst function implementation (PHP < 5.1)
     * @link http://php.net/manual/fr/function.lcfirst.php
     * @param string $string The input string
     * @return string
     */
    function lcfirst ($string) {
        $string{0} = strtolower($string{0});
        return $string;
    }
}