<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * PDO Statement Iterator Class
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage PDOStatementIterator
 */
class PDOStatementIterator extends IteratorIterator implements SeekableIterator, Countable {

    /**
     * Internal statement reference
     * @internal
     * @var PDOStatement
     */
    protected $_statement;
    
    /**
     * Internal counter
     * @internal
     * @var integer
     */
    protected $_count;
    
    /**
     * Default constructor
     * @param PDOStatement $statement
     */
    public function __construct (PDOStatement $statement) {
        parent::__construct($this->_statement = $statement);
    }
    
    /**
     * (non-PHPdoc)
     * @see SeekableIterator::seek()
     */
    public function seek ($position) {
        if ($position > $this->count() || $position < $this->key())
            throw new OutOfBoundsException("Cannot seek to $position", 2008);
            
        for ($i = $this->key(); $i < $position; $i++)
            $this->next();
    }
    
    /**
     * (non-PHPdoc)
     * @see Countable::count()
     */
    public function count () {
        if (!isset($this->_count))
		    return $this->_count = $this->_statement->rowCount();
	    return $this->_count;
    }
    
    public function first () {
        $this->rewind();
        return $this->valid() ? $this->current() : null;
    }
    
    public function last () {
        $this->seek($this->count());
        return $this->valid() ? $this->current() : null;
    }
}