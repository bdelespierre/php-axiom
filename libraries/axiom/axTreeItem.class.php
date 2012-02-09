<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Tree Item Class
 * 
 * This class implements a tree.
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage configuration
 */
class axTreeItem implements ArrayAccess, Iterator {

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
     * Child setter
     * @param string $k
     * @param mixed $v
     * @return void
     */
    public function __set ($k,$v) {
    	$this->__get($k)->setValue($v);
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($k) {
    	return isset($this->_c[$k]);
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet ($k) {
    	return $this->__get($k);
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet ($k,$v) {
    	$this->__get($k)->setValue($v);
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset ($k) {
    	unset($this->_c[$k]);
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current () {
    	return current($this->_c);
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    public function key () {
    	return key($this->_c);
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    public function next () {
    	next($this->_c);
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    public function rewind () {
    	reset($this->_c);
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    public function valid () {
    	return (bool)current($this->_c);
    }
	
    /**
     * toString
     * @return string
     */
    public function __toString () {
        return (string)$this->getValue();
    }
    
    /**
     * set state
     * @param array $props
     * @return axTreeItem
     */
    public static function __set_state ($props) {
    	$tree = new self;
    	$tree->_v = $props['_v'];
    	$tree->_c = $props['_c'];
    	return tree;
    }
}