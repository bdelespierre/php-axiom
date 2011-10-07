<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Base Helper Abstract Class
 *
 * @abstract
 * @author Delespierre
 * @version $Rev$
 * @subpackage BaseHelper
 */
abstract class BaseHelper implements Helper {

    /**
     * Node's name
     * @var string
     */
    protected $_node_name;

    /**
     * Node's value
     * @var mixed
     */
    protected $_node_value;

    /**
     * Node's attributes
     * @var array
     */
    protected $_attributes;

    /**
     * Ndeo's children
     * @var array
     */
    protected $_children;

    /**
     * Default constructor
     * @param string $node_name
     * @param array $attributes = array()
     * @param mixed $node_value = null
     */
    public function __construct ($node_name, $attributes = array(), $node_value = null) {
        $this->_node_name = $node_name;
        $this->_node_value = $node_value;
        $this->_attributes = $attributes;
    }

    /**
     * (non-PHPdoc)
     * @see Helper::setAttributes()
     */
    public function setAttributes ($attributes) {
        foreach ($attributes as $key => $value) {
            $this->_attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Helper::setValue()
     */
    public function setValue ($value) {
        $this->_node_value = $value;
    }

    /**
     * (non-PHPdoc)
     * @see Helper::getValue()
     */
    public function getValue () {
        return $this->_node_value;
    }

    /**
     * __call overloading
     * Enable the use of BaseHelper::setX() and BaseHelper::getX()
     * where X is an attribute of the current node
     * @param string $method
     * @param array $args
     * @return BaseHelper
     */
    public function __call ($method, $args) {
        if (strpos($method, 'set') === 0) {
            $this->_attributes[lcfirst(substr($method, 3))] = $args[0];
        }
        elseif (strpos($method, 'get') === 0) {
            return isset($this->_attributes[lcfirst(substr($method, 3))]) ? $this->_attributes[lcfirst(substr($method, 3))] : null;
        }
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Helper::appendChild()
     */
    public function appendChild ($node) {
        return $this->_children[] = $node;
    }
    
    /**
     * (non-PHPdoc)
     * @see Helper::prependChild()
     */
    public function prependChild ($node) {
        array_unshift($this->_children, $node);
        return $this->_children[0];
    }

    /**
     * (non-PHPdoc)
     * @see Helper::__toString()
     */
    public function __toString () {
        $attr = array();
        foreach ($this->_attributes as $name => $value) {
            $attr[] = "$name=\"$value\"";
        }
        $node = "<{$this->_node_name} " . implode(' ', $attr);

        if (!count($this->_children) && !$this->_node_value)
            return $node . " />";
        else
            $node .= ">";
        	
        if ($this->_node_value)
            $node .= $this->_node_value;

        if (count($this->_children))
            $node .= implode($this->_children);

        return $node . "</{$this->_node_name}>";
    }
}