<?php
/**
 * @brief Model base class file
 * @file axBaseModel.class.php 
 */

/**
 * @brief Model Base Class
 * 
 * @todo axBaseModel long description
 * @warning Only tables with strictly one attribute as primary key can be used with this class. For more complex 
 * object types, you should describe your own behavior by implementing axModel.
 * @class axBaseModel
 * @ingroup Model
 * @since 1.2.0
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
abstract class axBaseModel implements axModel {

    /**
     * @brief Database connection object
     * @property PDO $_pdo
     */
    protected $_pdo;
    
    /**
     * @brief Name of the identifying key (the PRIMARY KEY of your table in fact)
     * @property string $_idKey
     */
    protected $_idKey = "id";

    /**
     * @brief Record data
     * @property array $_data
     */
    protected $_data = array();

    /**
     * @brief Statements cache
     * @property array $_statements
     */
    protected $_statements = array();

    /**
     * @brief Initialize a statement
     * 
     * This method is intended to create the `PDOStatement` objects used by @e CRUD methods (create, retrieve, update, 
     * delete).
     * 
     * A statement is identified by its name:
     * @li create   : for record creation
     * @li retrieve : for record retrieving
     * @li update   : for record update
     * @li delete   : for deleting a record
     * 
     * @abstract
     * @param string $statement The statement name
     * @return PDOStatement
     */
    abstract protected function _init ($statement);

    /**
     * @brief Constructor
     * 
     * If @c $id is provided, the axModel::retrieve() method will be called with this parameter.
     * 
     * @param PDO $pdo The database connection object
     * @param mixed $id @optional @default{null}
     * @throws InvalidArgumentException If the @c $pdo parameter is null 
     * @throws RuntimeException If the @c $id parameter did not match an existing record
     */
    public function __construct (PDO $pdo, $id = null) {
        if (!$pdo)
            throw new InvalidArgumentException('First parameters is expected to be a valid PDO instance');
        
        $this->_pdo = $pdo;
            
        if ($id !== null && $id !== false && !$this->retrieve($id))
            throw new RuntimeException("Cannot instanciate model");
    }

    /**
     * @brief __sleep implementation
     * @return array
     */
    public function __sleep () {
        return array('_idKey', '_data');
    }

    /**
     * @brief __get implementation
     * 
     * Retrieves a record data identified by the @c $key parameter.
     * 
     * @param string $key
     * @return mixed
     */
    public function __get ($key) {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * @brief __set implementation
     * 
     * Updates a record data identified by the @c $key parameter with the @c $value parameter.
     * @note no implicit call is done to the axModel::update() method. You will have to update manually.
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        $this->_data[$key] = $value;
    }

    /**
     * @brief __isset implementation
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
     * @breif Get record data
     * 
     * Will return null if the record hasn't been fetched yet (no call to axModel::retrieve() has been done).
     * 
     * @return array
     */
    public function getData () {
        return $this->_data;
    }

    /**
     * @brief Create method (Crud)
     * 
     * Creates the record over the RDBMS using the @c create prepared statement.
     * Will return false in case of error
     * 
     * @param array $data
     * @throws RuntimeException If the @c create statement couldn't be initialized
     * @return axBaseModel
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
     * @brief Retrieve method (cRud)
     * 
     * Reads the record over the RDBMS using the @c retrieve prepared statement.
     * 
     * @param mixed $id
     * @throws RuntimeException If the @c retrieve statement couldn't be initialized
     * @return axBaseModel
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
     * @brief Update method (crUd)
     * 
     * Updates the record over the RDBMS using the @c update prepared statement.
     * The @c $data parameter will be merged with the current record data.
     * 
     * @param array $data @optional @default{array()} The data to add for updating
     * @throws RuntimeException If the @c update statemetn couldn't be initialized
     * @return axBaseModel
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
     * @brief Delete method (cruD)
     * 
     * Destruct the record over the RDBMS using the @c delete prepared statement.
     * 
     * @throws RuntimeException If the @c delete statement coulnd't be initialized
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