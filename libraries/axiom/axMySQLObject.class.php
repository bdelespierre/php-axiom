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
 * Helper class to map any MySQL row record
 * into a axModel object.
 *
 * Basicaly, this class is just a axModel class
 * capable of understanding the table
 * structure and to translate it to generic
 * CRUD queries.
 *
 * Due to axModel's limitation, only tables
 * with strictly one attribute as primary
 * key can be mapped using this class.
 * For more complex object types, you
 * should create your own behavior by
 * extending directly from axModel.
 *
 * Note: This class is intended for debugging
 * and testing purposes. YOU SHOULD REALLY
 * USE YOUR OWN MODEL CLASS in which you
 * will define explicitely the queries behavior.
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage core
 */
class axMySQLObject extends axModel {
    
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
     * @see axModel::_init()
     */
    protected function _init ($statement) {
        if (isset($this->_statements[$statement]))
            return $this->_statements[$statement];
            
        $pieces  = array();
        $columns = array();
        foreach ($this->_structure as $column => $infs) {
            $columns[] = "`$column`";
            if ($column == $this->_id_key && strpos($infs['EXTRA'], 'auto_increment') !== false)
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
                		 "WHERE `{$this->_id_key}`=:{$this->_id_key}";
                break;
                
            case 'update':
                $query = "UPDATE {$this->_table} SET " . implode(',', $pieces) . " " .
                         "WHERE `{$this->_id_key}`=:{$this->_id_key}";
                break;
                
            case 'delete':
                $query = "DELETE FROM {$this->_table} WHERE `{$this->_id_key}`=:{$this->_id_key}";
                break;
                
            default:
                throw new InvalidArgumentException("Invalid statement $statement");
        }
        
        return $this->_statements[$statement] = axDatabase::prepare($query);
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
            
        if ($stmt = axDatabase::query("DESC $table")) {
            $this->_structure = array();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
                if (isset($column['Key']) && strpos($column['Key'], 'PRI') !== false)
                    $this->_id_key = $column['Field'];
                
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
     * Default constructor.
     *
     * You may pass an $id to get the row
     * data directly so no further calls to
     * axModel::find is necessary.
     *
     * @param string $table
     * @param string $id
     */
    public function __construct ($table, $id = null) {
        if (!$this->_getTableStructure($table))
            throw new RuntimeException("Cannot determine {$table} structure");
            
        $this->_table = self::_sanitizeTablename($table);
        parent::__construct($id);
    }
    
    /**
     * Obtain a collection of MySQL Objects for a given table.
     *
     * You may pass search parameters to restrict the choices length.
     * You may pass the LIMIT, GROUP BY and ORDER BY clauses using
     * the $options parameter.
     *
     * The last parameter $mysql_obj is intended to be
     * used as a custom iterator cursor over the collection.
     * This object will also be used to determine the
     * columns of the table.
     * If the $mysql_obj parameter isn't provided, a new
     * instance of axMySQLObject will be generated.
     *
     * Will return false if the generated query fails.
     *
     * @param string $table
     * @param array $search_params
     * @param array $options
     * @param axMySQLObject $mysql_obj
     * @return axPDOStatementIterator
     */
    public static function all ($table, array $search_params = array(), array $options = array(), axMySQLObject $mysql_obj = null) {
        if (!isset($mysql_obj))
            $mysql_obj = new self($table);
            
        $table = self::_sanitizeTablename($table);
        
        $query = "SELECT `" . implode('`,`', $mysql_obj->getColumnNames()) . "` FROM {$table}";
        
        if (!empty($search_params)) {
            $pieces = array();
            
            foreach ($search_params as $key => $value) {
                if ((($offset = strpos($key, '<' )) !== false) || (($offset = strpos($key, '>' )) !== false)
                 || (($offset = strpos($key, '>=')) !== false) || (($offset = strpos($key, '>=')) !== false)) {
                    $new_key = trim(substr($key, 0, $offset));
                    $pieces[] = "{$key}:{$new_key}";
                    $search_params[$new_key] = $value;
                    unset($search_params[$key]);
                }
                else {
                    $pieces[] = "`{$key}`=:{$key}";
                }
            }
            
            $query .= " WHERE " . implode(' AND ', $pieces);
        }
        
        if (!empty($options['group_by'])) {
            $pieces = array();
            
            foreach($options['group_by'] as $field)
                $pieces[] = "`{$field}`";
            
            $query .= " GROUP BY ".implode(',' ,$pieces);
        }
        
        if (!empty($options['order_by'])) {
            $pieces = array();
            
            foreach($options['order_by'] as $field)
                $pieces[] = "`{$field}`";
            
            $query .= " ORDER BY ".implode(',' ,$pieces);
            
            if (isset($options['order_by_type']) && in_array(strtoupper($options['order_by_type']), array('ASC', 'DESC')))
                $query .= " " . strtoupper($options['order_by_type']);
        }
        
        if (!empty($options['count'])) {
            if (count($options['limit']) == 1) {
                $options['limit'] = (array)$options['limit'];
                $query .= " LIMIT {$options['limit'][0]}";
            }
            
            if (count($options['limit']) == 2) {
                $query .= " LIMIT {$options['limit'][0]},{$options['limit'][1]}";
            }
        }
        
        axLog::debug('Query: '. $query);
        
        $stmt = axDatabase::prepare($query);
        if ($stmt->execute($search_params)) {
            $stmt->setFetchMode(PDO::FETCH_INTO, $mysql_obj);
            
            if (PHP_VERSION_ID < 50200) {
                $cquery = preg_replace('~SELECT.*FROM~', 'SELECT COUNT(*) FROM', $query);
                $cstmt = axDatabase::prepare($query);
                !empty($search_params) ? $cstmt->execute($search_params) : $cstmt->execute();
                $count = (int)$cstmt->fetchColumn();
                $it->setCount($count);
            }
            
            return new axPDOStatementIterator($stmt);
        }
        return false;
    }
    
    /**
     * Get the table columns
     * @return array
     */
    public function getColumnNames () {
        return array_keys($this->_structure);
    }
    
    /**
     * Get the complete table structure
     * @return array
     */
    public function getStructure () {
        return $this->_structure;
    }
}