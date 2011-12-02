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
class DirectoryFilterIterator extends FilterIterator {
    
    /**
     * Default constructor
     *
     * TODO add exclusion pattern here
     *
     * @param DirectoryIterator $iterator
     */
    public function __construct(DirectoryIterator $iterator) {
        parent::__construct($iterator);
    }
    
    /**
     * (non-PHPdoc)
     * @see FilterIterator::accept()
     */
    public function accept () {
        return $this->current()->isDir() &&
              !$this->current()->isDot() &&
              !($this->current()->getFilename() == '.svn') &&
              !($this->current()->getFilename() == 'admin');
    }
}