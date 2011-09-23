<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Database Class
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage Database
 */
class Database {
    
    protected static $_config = array();
    
    /**
     * Inner PDO instance
     * @var PDO
     */
    protected static $_pdo_instance;
    
    /**
     * The Database class cannot be instanciated
     * @internal
     */
    private function __construct () {
        throw new RuntimeException(__CLASS__ . " cannot be instanciated", 2031);
    }
    
    /**
     * Set database configuration
     * @param array $config = array
     * @return void
     */
    public static function setConfig ($config = array()) {
        $defaults = array(
            'type' => 'mysql',
            'database' => 'axiom',
            'username' => 'root',
            'password' => '',
            'host' => 'localhost',
            'dsn' => null,
            'options' => array(),
        );
        
        self::$_config = $config + $defaults;
    }
    
    /**
     * Opens the database connection.
     *
     * It the ignore error is set to true
     * the connection failure will be
     * ignored.
     *
     * @param boolean $ignore_error = false
     * @return boolean
     */
    public static function open ($ignore_error = false) {
        extract(self::$_config);
        if (!isset($dsn))
            $dsn = "{$type}:dbname={$database};host={$host}";

        try {
            self::$_pdo_instance = new PDO($dsn, $username, $password, $options);
            return true;
        }
        catch (PDOException $e) {
            if (!$ignore_error) {
                if (PHP_VERSION_ID >= 50300)
                    throw new RuntimeException("Database connection error", 2032, $e);
                else
                    throw new RuntimeException("Database connection error", 2032);
            }
            return false;
        }
    }
    
    /**
     * Get internal PDO instance
     * @return PDO
     */
    public static function getInstance () {
        return self::$_pdo_instance;
    }
    
    /**
     * Begin a transaction
     * @see PDO::beginTransaction
     * @return boolean
     */
    public static function beginTransaction () {
        return self::$_pdo_instance->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * @see PDO::commit
     * @return boolean
     */
    public static function commit () {
        return self::$_pdo_instance->commit();
    }
    
    /**
     * Get error code
     * @see PDO::errorCode
     * @return mixed
     */
    public static function errorCode () {
        return self::$_pdo_instance->errorCode();
    }
    
    /**
     * Get error info
     * @see PDO::errorInfo
     * @return array
     */
    public static function errorInfo () {
        return self::$_pdo_instance->errorInfo();
    }
    
    /**
     * Exec an SQL statement
     * @see PDO::exec
     * @param string $statement
     * @return integer
     */
    public static function exec ($statement) {
        return self::$_pdo_instance->exec($statement);
    }
    
    /**
     * Get attribute
     * @see PDO::getAttribute
     * @param integer $attribute
     * @return mixed
     */
    public static function getAttribute ($attribute) {
        return self::$_pdo_instance->getAttribute($attribute);
    }
    
    /**
     * Get available drivers
     * @see PDO::getAvailableDrivers
     * @return array
     */
    public static function getAvailableDrivers () {
        return self::$_pdo_instance->getAvailableDrivers();
    }
    
    /**
     * Get last inserted id
     * @see PDO::lastInsertId
     * @param string $name = null
     * @return string
     */
    public static function lastInsertId ($name = null) {
        return self::$_pdo_instance->lastInsertId($name);
    }
    
    /**
     * Prepare a statement
     * @see PDO::prepare
     * @param string $statement
     * @param array $driver_options = array()
     * @return PDOStatement
     */
    public static function prepare ($statement, $driver_options = array()) {
        return self::$_pdo_instance->prepare($statement, $driver_options);
    }
    
    /**
     * Execute a query
     * @see PDO::query
     * @param string $statment
     * @return PDOStatement
     */
    public static function query ($statment) {
        return self::$_pdo_instance->query($statment);
    }
    
    /**
     * Quote string
     * @see PDO::quote
     * @param string $string
     * @param integer $parameter_type = PDO::PARAM_STR
     * @return string
     */
    public static function quote ($string, $parameter_type = PDO::PARAM_STR) {
        return self::$_pdo_instance->quote($string, $parameter_type);
    }
    
    /**
     * Rollback a transaction
     * @see PDO::rollBack
     * @return boolean
     */
    public static function rollBack () {
        return self::$_pdo_instance->rollBack();
    }
    
    /**
     * Sets an attribute
     * @see PDO::setAttribute
     * @param integer $attribute
     * @param mixed $value
     * @return boolean
     */
    public static function setAttribute ($attribute, $value) {
        return self::$_pdo_instance->setAttribute($attribute, $value);
    }
}