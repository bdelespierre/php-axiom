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
 * into a Model object.
 *
 * Basicaly, this class is just a Model class
 * capable of understanding the table
 * structure and to translate it to generic
 * CRUD queries.
 *
 * Due to Model's limitation, only tables
 * with strictly one attribute as primary
 * key can be mapped using this class.
 * For more complex object types, you
 * should create your own behavior by
 * extending directly from Model.
 *
 * Note: This class is intended for debugging
 * and testing purposes. YOU SHOULD REALLY
 * USE YOUR OWN MODEL CLASS in which you
 * will define explicitely the queries behavior.
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage MySQLObject
 */
class MySQLObject extends Model {
    
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
     * @see Model::_init()
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
                $query = "INSERT INTO `{$this->_table}` SET " . implode(',', $pieces);
                break;
                
            case 'retrieve':
                $query = "SELECT " . implode(',', $columns) . " " .
                		 "FROM `{$this->_table}` " .
                		 "WHERE `{$this->_id_key}`=:{$this->_id_key}";
                break;
                
            case 'update':
                $query = "UPDATE `{$this->_table}` SET " . implode(',', $pieces) . " " .
                         "WHERE `{$this->_id_key}`=:{$this->_id_key}";
                break;
                
            case 'delete':
                $query = "DELETE FROM `{$this->_table}` WHERE `{$this->_id_key}`=:{$this->_id_key}";
                break;
                
            default:
                throw new InvalidArgumentException("Invalid statement $statement");
        }
        
        return $this->_statements[$statement] = Database::prepare($query);
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
            
        if ($stmt = Database::query("DESC `$table`")) {
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
     * Default constructor.
     *
     * You may pass an $id to get the row
     * data directly so no further calls to
     * MySQLModel::find is necessray.
     *
     * @param string $table
     * @param string $id
     */
    public function __construct ($table, $id = null) {
        if (!$this->_getTableStructure($table))
            throw new RuntimeException("Cannot determine {$table} structure");
            
        $this->_table = $table;
        parent::__construct($id);
    }
    
    public static function all ($table, array $search_params = array(), array $limit = array(), MySQLObject $mysql_obj = null) {
        if (!isset($mysql_obj))
            $mysql_obj = new self;
        
        $query = "SELECT `" . implode('`,`', $mysql_obj->getColumnNames) . " FROM `{$table}`";
        
        if (!empty($search_params)) {
            $pieces = array();
            foreach ($search_params as $key => $value)
                $pieces[] = "`{$key}`=:{$key}";
            // TODO FINISH THIS STUPID METHOD !!
        }
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