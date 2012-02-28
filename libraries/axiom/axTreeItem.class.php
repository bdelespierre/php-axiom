<?php
/**
 * @brief Tree item class file
 * @file axTreeItem.class.php
 */

/**
 * @brief Tree Item Class
 * 
 * This class implements a tree on which branches can also have a value.
 *
 * @class axTreeItem
 * @author Delespierre
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axTreeItem implements ArrayAccess, Iterator {

    /**
     * @brief Value
     * @property mixed $_v
     */
    protected $_v;

    /**
     * @brief Children
     * @property array $_c
     */
    protected $_c;

    /**
     * @brief Value setter
     * @param mixed $v
     * @return axTreeItem
     */
    public function setValue ($v) {
        $this->_v = $v;
        return $this;
    }

    /**
     * @brief Valxue getter
     * @return mixed
     */
    public function getValue () {
        return isset($this->_v) ? $this->_v : null;
    }
    
    /**
     * @brief Tells if a child exists
     * @param scalar $k
     * @return boolean
     */
    public function __isset ($k) {
        return isset($this->_c[$k]);
    }
    
	/**
     * @brief Child getter
     *
     * @note If the child doesn't exists, an empty child will be created and returned. Thus you will never face an 
     * error accessing an inexisting tree item (but you'll get empty leaf/branches).
     *
     * @param string $k The branche/leaf key
     * @return axConfigurationItem
     */
    public function __get ($k) {
        if (isset($this->_c[$k]))
            return $this->_c[$k];
        return $this->_c[$k] = new self;
    }
	
    /**
     * @brief Child setter
     * @param string $k
     * @param mixed $v
     * @return void
     */
    public function __set ($k,$v) {
    	$this->__get($k)->setValue($v);
    }
    
    /**
     * @brief Unset a child
     * @param scalar $k
     * @return void
     */
    public function __unset ($k) {
        unset($this->_c[$k]);
    }
    
    /**
     * @brief offsetExist implementation, alias of axTreeItem::__isset()
     * @see ArrayAccess::offsetExists()
     * @param scalar $k The offset
     * @return boolean
     */
    public function offsetExists($k) {
    	return $this->__isset($k);
    }
    
    /**
     * @brief offsetGet implementation, alias of axTreeItem::__get()
     * @see ArrayAccess::offsetGet()
     * @param scalar $k The offset
     * @return mixed
     */
    public function offsetGet ($k) {
    	return $this->__get($k);
    }
    
    /**
     * @brief offsetSet implementation, alias of axTreeItem::__set()
     * @see ArrayAccess::offsetSet()
     * @param scalar $k The offset
     * @param mixed $v The value
     * @retun void
     */
    public function offsetSet ($k,$v) {
    	$this->__set($k,$v);
    }
    
    /**
     * @brief offsetUnset implementation, alias of axTreeItem::__unset()
     * @see ArrayAccess::offsetUnset()
     * @param scalar $k The offset
     * @return void
     */
    public function offsetUnset ($k) {
    	$this->__unset($k);
    }
    
    /**
     * @brief current implementation, returns the current child
     * @see Iterator::current()
     * @return axTreeItem
     */
    public function current () {
    	return current($this->_c);
    }
    
    /**
     * @brief key implementation, returns the current key
     * @see Iterator::key()
     * @return scalar
     */
    public function key () {
    	return key($this->_c);
    }
    
    /**
     * @brief next implementation
     * @see Iterator::next()
     * @return axTreeItem
     */
    public function next () {
    	next($this->_c);
    }
    
    /**
     * @brief rewind implementation
     * @see Iterator::rewind()
     * @return void
     */
    public function rewind () {
    	reset($this->_c);
    }
    
    /**
     * @brief valid implementation
     * @see Iterator::valid()
     * @retun boolean
     */
    public function valid () {
    	return (bool)current($this->_c);
    }
	
    /**
     * @brief __toString implementation
     * 
     * Get the string representation of current instance's value.
     * 
     * @return string
     */
    public function __toString () {
        return (string)$this->getValue();
    }
    
    /**
     * @brief __set_state implementation
     * @internal
     * @static
     * @param array $props
     * @return axTreeItem
     */
    public static function __set_state ($props) {
    	$tree = new self;
    	$tree->_v = $props['_v'];
    	$tree->_c = $props['_c'];
    	return $tree;
    }
}