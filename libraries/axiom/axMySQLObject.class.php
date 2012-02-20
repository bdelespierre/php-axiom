<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * MySQL Object
 *
 * Helper class to map a MySQL row record into an `axModel` object. Basicaly, this class is just an `axModel` class 
 * capable of understanding a MySQL table structure and to translate it to generic CRUD queries.
 *
 * IMPORTANT: only tables with strictly one attribute as primary key can be used with this class. For more complex 
 * object types, you should describe your own behavior by implementing `axModel`.
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage model
 */
class axMySQLObject extends axBaseModel {
    
    /**
     * Table name
     * @var string
     */
    protected $_table;
    
    /**
     * Table structure (describe result)
     * @var array
     */
    protected $_structure;
    
    /**
     * (non-PHPdoc)
     * @see axBaseModel::_init()
     */
    protected function _init ($statement) {
        if (isset($this->_statements[$statement]))
            return $this->_statements[$statement];
            
        $pieces  = array();
        $columns = array();
        foreach ($this->_structure as $column => $infs) {
            $columns[] = "`$column`";
            if ($column == $this->_idKey && strpos($infs['EXTRA'], 'auto_increment') !== false)
                continue;
            $pieces[]  = "`{$column}`=:{$column}";
        }
        
        switch ($statement) {
            case 'create':
                $query = "INSERT INTO {$this->_table} SET " . implode(',', $pieces);
                break;
                
            case 'retrieve':
                $query = "SELECT " . implode(',', $columns) . " " .
                		 "FROM {$this->_table} " .
                		 "WHERE `{$this->_idKey}`=:{$this->_idKey}";
                break;
                
            case 'update':
                $query = "UPDATE {$this->_table} SET " . implode(',', $pieces) . " " .
                         "WHERE `{$this->_idKey}`=:{$this->_idKey}";
                break;
                
            case 'delete':
                $query = "DELETE FROM {$this->_table} WHERE `{$this->_idKey}`=:{$this->_idKey}";
                break;
                
            default:
                throw new InvalidArgumentException("Invalid statement $statement");
        }
        
        return $this->_statements[$statement] = $this->_pdo->prepare($query);
    }
    
    /**
     * Default constructor
     *
     * You may pass an $id to get the row data directly so no further calls to `axModel::retrieve` is necessary.
     * 
     * @param PDO $pdo The database connection instance
     * @param string $tablename The MySQL table name
     * @param string $id [optional] [default `null`] The ID of the mysql row to match
     */
    public function __construct (PDO $pdo, $id = null) {
        $args = func_get_args();
        if (empty($args))
            throw new InvalidArgumentException('Missing parameters: `$pdo`, `$table`');
            
        if (count($args) === 1)
            throw new InvalidArgumentException('Missing parameter: `$table`');
            
        if (!$args[0] instanceof PDO)
            throw new InvalidArgumentException('`$pdo` parameter must be a valid PDO instance');
            
        if (!is_string($args[1]))
            throw new InvalidArgumentException('`$tablename` parameter must be string');
            
        if (empty($args[1]))
            throw new InvalidArgumentException('`$tablename` parameter cannot be empty');
            
        list($pdo,$tablename,$id) = $args + array(null,'',null);
        
        if (!$this->_getTableStructure($tablename))
            throw new RuntimeException("Cannot determine {$tablename} structure");
            
        $this->_table = self::_sanitizeTablename($tablename);
        parent::__construct($pdo, $id);
    }
    
    /**
     * Obtain a collection of MySQL Objects optionaly filtered by `$search_params` and `$options` parameters
     * 
     * The last `$object` parameter can be either an axModel instance or a valid table name. `axMySQLOjbect::all` will
     * generate a generic SQL `SELECT` query to fetch any row that match the `$search_params` and `$options` conditions
     * (if any). An `axPDOStatement` instance is returned in case of success, which lets you iterate over the 
     * collection. Each collection item is a valid instance of axModel you can obviously perform any CRUD operation on.
     * 
     * E.G.
     * * // Retrive all items in the `mydb`.`users` table
     * * axMySQLObject::all($pdo, array(), array(), 'mydb.users');
     * *
     * * // Retrive all items in the `mydb`.`users` table having the `mail` field set to `foo@bar.com`
     * * axMySQLObject::all($pdo, array('mail' => 'foo@bar.com'), array(), 'mydb.users');
     * *
     * * // Retrieve all items in the `mydb`.`users` and order them by login name
     * * axMySQLObject::all($pdo, array(), array('order by' => 'login'), 'mydb.users');
     * *
     * * // Retrieve all items in the `mydb`.`users` having a `privilege_level` higher than 5
     * * axMySQLObject::all($pdo, array('privilege_level >=' => 5), array(), 'mydb.users');
     * *
     * * // Retrive all users using a valid user instance
     * * $user = new User($pdo, $id); // User class implements axModel
     * * axMySQLObject::add($pdo, array(), array(), $user);
     * 
     * IMPORTANT: a field listed in `$search_params` cannot be listed twice, even if you specify the operator.
     * E.G.
     * * // The following call will result as a query error
     * * axMySQLObject::all($pdo, array('privilege_level >=' => 5, 'privilege_level <=' => 10), array(), 'mydb.users');
     * 
     * NOTE: you may use the `BETWEEN` operator to restrict results in a given range.
     * E.G.
     * * // Retrieve all items in the `mydb`.`users` having a `privilege_level` higher than 5 and lower than 10
     * * axMySQLObject::all($pdo, array('privilege_level BETWEEN' => array(5,10)), array(), 'mydb.users');
     * 
     * NOTE: the `WHERE` clause generation engine will produce prepared statements compliant string (see 
     * http://php.net/manual/en/class.pdostatement.php). You should not use invalid replacement values like sub-queries
     * or string containing SQL keywords like 'xxx AND yyy'.
     * 
     * The `$options` parameters may have the following parameters (in any order):
     * * group by : any string or array of strings value describing a field or a list of fields
     * * limit : an integer or an array containing 2 integers describing the limit bounds
     * * order by : any string or array of strings value describing a field or a list of fields
     * * order by type : `ASC` or `DESC`
     * Any other key for the `$option` parameter will be ignored. Any incorrect option parameter will also be ignored.
     * 
     * Will return false if the generated query execution fails.
     * 
     * IMPORTANT: all parameters are mandatory.
     *
     * @param PDO $pdo
     * @param array $search_params
     * @param array $options
     * @param mixed $object Either a tablename or a valid axObject instance
     * @return axPDOStatementIterator
     */
    public static function all (PDO $pdo, array $search_params = array(), array $options = array()) {    
        if (func_num_args() < 4)
            throw new InvalidArgumentException('Missing fourth parameter');
        
        $arg = func_get_arg(3);
        
        if ($arg instanceof axModel) {
            $mysql_obj = $arg;
        }
        elseif (is_string($arg)) {
            try  {
                $mysql_obj = new self($pdo, $arg);
            }
            catch (Exception $e) {
                trigger_error("Unable to create `axMySQLObject` instance: " . $e->getMessage(), E_USER_WARNING);
                return false;
            }
        }
        else {
            throw new InvalidArgumentException("Fourth argument is expected to be a valid `axModel` instance or ".
                "string, " . gettype($arg) . " given");
        }
            
        if (!isset($mysql_obj))
            $mysql_obj = new self($table);
            
        $query  = "SELECT `" . implode('`,`', $mysql_obj->getColumns()) . "` FROM " . $mysql_obj->getTable();
        $query .= self::_generateWhereClause($search_params);
        $query .= self::_generateOptionClause($options);
        
        $stmt = $pdo->prepare($query);
        if ($stmt->execute($search_params)) {
            $stmt->setFetchMode(PDO::FETCH_INTO, $mysql_obj);
            
            if (PHP_VERSION_ID < 50200) {
                $cquery = preg_replace('~SELECT.*FROM~', 'SELECT COUNT(*) FROM', $query);
                $cstmt = $pdo->prepare($query);
                !empty($search_params) ? $cstmt->execute($search_params) : $cstmt->execute();
                $count = (int)$cstmt->fetchColumn();
                $it->setCount($count);
            }
            
            return new axPDOStatementIterator($stmt);
        }
        return false;
    }
    
    /**
     * Get the table name of the record
     * @return string
     */
    public function getTable () {
        return $this->_table;
    }
    
    /**
     * Get the table columns
     * @return array
     */
    public function getColumns () {
        return array_keys($this->_structure);
    }
    
    /**
     * Get the complete table structure
     * @return array
     */
    public function getStructure () {
        return $this->_structure;
    }
    
	/**
     * Get the structure from any table name
     *
     * The structure is parsed from the MySQL
     * DESCRIBE query result.
     *
     * Will throw an InvalidArgumentException
     * if $table parameter is invalid or empty.
     *
     * Will return true in case of success,
     * false otherwise.
     *
     * @internal
     * @param string $table
     * @throws InvalidArgumentException
     * @return boolean
     */
    protected function _getTableStructure ($table) {
        if (!is_string($table) || empty($table))
            throw new InvalidArgumentException("First parameter is expected to be valid string");
            
        $table = self::_sanitizeTablename($table);
            
        if ($stmt = Axiom::database()->query("DESC $table")) {
            $this->_structure = array();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
                if (isset($column['Key']) && strpos($column['Key'], 'PRI') !== false)
                    $this->_idKey = $column['Field'];
                
                $this->_structure[$column['Field']] = array_change_key_case($column, CASE_UPPER);
            }
            return true;
        }
        return false;
    }
    
	/**
     * Sanitize the tablename
     * @param string $table
     * @return string
     */
    protected static function _sanitizeTablename ($table) {
        return '`' . implode('`.`', explode('.', str_replace(array('`', ' '), '', $table))) . '`';
    }
    
    /**
     * Generates a `SELECT` query according to the given parameters
     * 
     * See `axMySQLObject::all` for more details about the query generation.
     * 
     * @param string $tablename
     * @param array $columns [optional] [default `array()`]
     * @param array $search_params [optional] [default `array()`]
     * @param array $options [optional] [default `array()`]
     * @throws InvalidArgumentException If the `$tablename` parameter is empty
     * @return string
     */
    protected static function _generateSelectQuery ($tablename,
                                                    array $columns = array(), 
                                                    array $search_params = array(),
                                                    array $options = array()) {
        if (!$tablename)
            throw new InvalidArgumentException('`$tablename` cannot be empty');
                                                        
        if (empty($columns))
            $columns = '*';
        else
            $columns = '`' . implode('`,`', $columns) .  '`';
                                                       
        $query  = "SELECT {$columns} FROM " . self::_sanitizeTablename($tablename);
        $query .= self::_generateWhereClause($search_params);
        $query .= self::_generateOptionClause($options);
        
        return $query;
    }
    
    protected static function _generateInsertQuery ($tablename, array $columns) {
        // TODO
    }
    
    protected static function _generateUpdateQuery ($tablename, array $columns, array $search_params = array()) {
        // TODO
    }
    
    protected static function _generateDeleteQuery ($tablename, array $search_params = array()) {
        // TODO
    }
    
    /**
     * Generates a query `WHERE` clause
     * 
     * See `axMySQLObject::all` description for more details about the `$search_params` structure.
     * Will return an empty string if the `$search_params` parameter is empty.
     * 
     * @param array $search_params
     * @return string
     */
    protected static function _generateWhereClause (array $search_params) {
        if (!empty($search_params)) {
            $pieces = array();
            
            foreach ($search_params as $key => $value) {
                if (preg_match('~\s*(?<field>\w+)\s*(?<operator>.*)\s*~', $key, $matches)) {
                    $field    = $matches['field'];
                    $operator = $matches['operator'];
                    unset($search_params[$key]);
                    $search_params['field'] = $value;
                    $pieces[] = "`{$field}`{$operator}:{$field}";
                }
                else {
                    $pieces[] = "`{$key}`=:{$key}";
                }
            }
            
            return " WHERE " . implode(' AND ', $pieces);
        }
        return "";
    }
    
    /**
     * Generates a query `GROUP BY`, `ORDER BY` and `LIMIT` clauses
     * 
     * See `axMySQLObject::all` description for more details about the `$options` structure.
     * Will return an empty string if the `$options` parameter is empty.
     * 
     * @param array $search_params
     * @return string
     */
    protected static function _generateOptionClause (array $options) {
        $query = "";
        
        if (!empty($options['group by'])) {
            $pieces = array();
            
            foreach((array)$options['group_by'] as $field)
                $pieces[] = "`{$field}`";
            
            $query .= " GROUP BY ".implode(',' ,$pieces);
        }
        
        if (!empty($options['order by'])) {
            $pieces = array();
            
            foreach($options['order by'] as $field)
                $pieces[] = "`{$field}`";
            
            $query .= " ORDER BY ".implode(',' ,$pieces);
            
            if (isset($options['order by type']) 
             && in_array(strtoupper($options['order by type']), array('ASC', 'DESC')))
                $query .= " " . strtoupper($options['order by type']);
        }
        
        if (!empty($options['limit'])) {
            if (count($options['limit']) == 1) {
                $options['limit'] = (array)$options['limit'];
                $query .= " LIMIT {$options['limit'][0]}";
            }
            
            if (count($options['limit']) == 2) {
                $query .= " LIMIT {$options['limit'][0]},{$options['limit'][1]}";
            }
        }
        
        return $query;
    }
}