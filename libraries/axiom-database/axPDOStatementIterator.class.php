<?php
/**
 * @brief PDOStatement iterator class file
 * @file axPDOStatementIterator.class.php
 */

/**
 * @brief PDO Statement Iterator Class
 * 
 * This class is a PDOStatement decorator to enable its use as an iterator instead of a traversable instance.
 *
 * @class axPDOStatementIterator
 * @author Delespierre
 * @ingroup Model
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axPDOStatementIterator extends IteratorIterator implements SeekableIterator, Countable {

    /**
     * @brief Internal statement reference
     * @property PDOStatement $_statement
     */
    protected $_statement;
    
    /**
     * @brief Internal counter
     * @property integer $_count
     */
    protected $_count;
    
    /**
     * @brief Constructor
     * @param PDOStatement $statement
     */
    public function __construct (PDOStatement $statement) {
        parent::__construct($this->_statement = $statement);
    }
    
    /**
     * @brief SeekableIterator::seek() Implementation
     * @param integer $position The position to seek
     * @return void
     */
    public function seek ($position) {
        if ($position > $this->count() || $position < $this->key())
            throw new OutOfBoundsException("Cannot seek to $position", 2008);
            
        for ($i = $this->key(); $i < $position; $i++)
            $this->next();
    }
    
    /**
     * @breif Countable::count() implementation
     * 
     * Get the number of items in the iterator.
     * 
     * @return integer
     */
    public function count () {
        if (!isset($this->_count))
		    return $this->_count = $this->_statement->rowCount();
	    return $this->_count;
    }
    
    /**
     * @brief Get the first item
     * @return mixed
     */
    public function first () {
        $this->rewind();
        return $this->valid() ? $this->current() : null;
    }
    
    /**
     * @brief Get the last item
     * @return mixed
     */
    public function last () {
        $this->seek($this->count());
        return $this->valid() ? $this->current() : null;
    }
}