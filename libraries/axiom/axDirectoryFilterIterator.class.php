<?php
/**
 * @brief Directory filter iterator class file
 * @file axDirectoryFilterIterator.class.ph
 */

/**
 * @brief Directory Filter Iterator
 *
 * This class is defined as a DirectoryIterator wrapper where each valid elements are folder (excluding .. and .).
 * Since Axiom 1.2.0, you may now add your own exclude names (for instance to ommit .git directory).
 * 
 * @class axDirectoryFilterIterator
 * @author Delespierre
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axDirectoryFilterIterator extends FilterIterator {
    
    /**
     * @brief Exclusion names
     * @property array $_exclude
     */
    protected $_exclude;
    
    /**
     * @brief Constructor
     * @param DirectoryIterator $iterator
     * @param array $exclude @optional @default{array('.', '..')}
     */
    public function __construct(DirectoryIterator $iterator, array $exclude = array('.', '..')) {
        parent::__construct($iterator);
    }
    
    /**
     * @brief FilterIterator::accept() implementation
     * @link http://www.php.net/manual/en/filteriterator.accept.php
     * @return boolean
     */
    public function accept () {
        if (empty($this->_exclude))
            return true;
        else
            return $this->current()->isFile() && !in_array((string)$this->current(), $this->_exclude);
    }
    
    /**
     * @brief Add on (or many) filenames to exclude from the Iterator
     * 
     * You may pass as many parameter as filenames you want to exclude.
     * 
     * @param string $filename [...]
     * @return axDirectoryFilterIterator
     */
    public function exclude ($filename) {
        if (!func_num_args())
            return $this;
        
        $this->_exclude = array_merge($this->_exclude, func_get_args());
        return $this;
    }
}