<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Directory Filter Iterator
 *
 * This class is defined as a DirectoryIterator
 * wrapper where each valid elements are folder
 * (excluding .. and .)
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage core
 */
class axDirectoryFilterIterator extends FilterIterator {
    
    protected $_exclude;
    
    /**
     * Default constructor
     * @param DirectoryIterator $iterator
     */
    public function __construct(DirectoryIterator $iterator, array $exclude = array('.', '..')) {
        parent::__construct($iterator);
    }
    
    /**
     * (non-PHPdoc)
     * @see FilterIterator::accept()
     */
    public function accept () {
        if (empty($this->_exclude))
            return true;
        else
            return $this->current()->isFile() && !in_array((string)$this->current(), $this->_exclude);
    }
    
    /**
     * Add on (or many) filenames to exclude from the Iterator
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