<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Model Base Class
 * 
 * TODO long description
 * 
 * IMPORTANT: only tables with strictly one attribute as primary key can be used with this class. For more complex 
 * object types, you should describe your own behavior by implementing `axModel`.
 *
 * @abstract
 * @author Delespierre
 * @package libaxiom
 * @subpackage model
 */
abstract class axBaseModel implements axModel {

    /**
     * Database connection
     * @var PDO
     */
    protected $_pdo;
    
    /**
     * Name of the identifying key (the PRIMARY KEY of your table in fact)
     * @var string
     */
    protected $_idKey = "id";

    /**
     * Record data
     * @var array
     */
    protected $_data = array();

    /**
     * Statements cache
     * @var array
     */
    protected $_statements = array();

    /**
     * Initialize a statement
     * 
     * This method is intended to create the `PDOStatement` objects used by CRUD methods (create, retrieve, update, 
     * delete). A statement is identified by its name:
     * * create   : for record creation
     * * retrieve : for record retrieving
     * * update   : for record update
     * * delete   : for deleting a record
     * 
     * @abstract
     * @param string $statement The statement name
     * @return PDOStatement
     */
    abstract protected function _init ($statement);

    /**
     * Default constructor
     * 
     * If `$id` is provided, the `retrive` method will be called with this parameter.
     * Will throw a RuntimeException if the `$id` parameter did not match an existing record.
     * 
     * @param mixed $id
     * @throws RuntimeException
     */
    public function __construct (PDO $pdo, $id = null) {
        if (!$pdo)
            throw new InvalidArgumentException('First parameters is expected to be a valid PDO instance');
        
        $this->_pdo = $pdo;
            
        if ($id !== null && $id !== false && !$this->retrieve($id))
            throw new RuntimeException("Cannot instanciate model");
    }

    /**
     * __sleep implementation
     * @return array
     */
    public function __sleep () {
        return array('_idKey', '_data');
    }

    /**
     * __get implementation
     * 
     * Retrieves a record data identified by the `$key` parameter.
     * 
     * @param string $key
     * @return mixed
     */
    public function __get ($key) {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * __set implementation
     * 
     * Updates a record data identified by the `$key` parameter with the  `$value` parameter.
     * NOTE: no implicit call is done to the `update` method. You will have to update manually.
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        $this->_data[$key] = $value;
    }

    /**
     * __isset implementation
     * 
     * Tells if a record data exists.
     * 
     * @param string $key
     * @return boolean
     */
    public function __isset ($key) {
        return isset($this->_data[$key]);
    }

    /**
     * Get record data
     * 
     * Will retur null if the record hasn't been fetched yet (no call to `retrieve` has been done).
     * 
     * @internal
     * @return array
     */
    public function getData () {
        return $this->_data;
    }

    /**
     * Create method (Crud)
     * 
     * Creates the record over the RDBMS using the 'create' prepared statement.
     * Will return false in case of error
     * 
     * @param array $data
     * @throws RuntimeException
     * @return axModel
     */
    public function create (array $data) {
        if (!$this->_init("create"))
            throw new RuntimeException("Cannot initialize " . __METHOD__, 2011);
         
        if ($this->_statements['create']->execute($data)) {
            $id = $this->_pdo->lastInsertId();
            return $this->retrieve($id);
        }
        return false;
    }
    
	/**
     * Retrieve method (cRud)
     * 
     * Reads the record over the RDBMS using the 'retrieve' prepared statement.
     * 
     * @param mixed $id
     * @return axModel
     */
    public function retrieve ($id) {
        if (!$this->_init("retrieve"))
            throw new RuntimeException("Cannot initialize " . __METHOD__, 2010);
         
        if ($this->_statements['retrieve']->execute(array(":{$this->_idKey}" => $id))) {
            if ($this->_statements['retrieve']->rowCount()) {
                $this->_data = $this->_statements['retrieve']->fetch(PDO::FETCH_ASSOC);
                return $this;
            }
        }
        return false;
    }

    /**
     * Update method (crUd)
     * 
     * Updates the record over the RDBMS using the 'update' prepared statement.
     * 
     * @throws RuntimeException
     * @return boolean
     */
    public function update (array $data = array()) {
        if (!$this->_init("update"))
            throw new RuntimeException("Cannot initialize " . __METHOD__, 2012);
         
        if (!empty($this->_data)) {
            $inputs = array_merge($this->_data, array_intersect_key($data, $this->_data));
            return $this->_statements['update']->execute($inputs) ? $this : false;
        }
        return false;
    }

    /**
     * Delete method (cruD)
     * 
     * Destruct the record over the RDBMS using the 'delete' prepared statement.
     * 
     * @throws RuntimeException
     * @return boolean
     */
    public function delete () {
        if (!$this->_init("delete"))
            throw new RuntimeException("Cannot initialize " . __METHOD__, 2013);
         
        if (!empty($this->_data))
            return $this->_statements['delete']->execute(array(":{$this->_idKey}" => $this->_data[$this->_idKey]));
        return false;
    }
}