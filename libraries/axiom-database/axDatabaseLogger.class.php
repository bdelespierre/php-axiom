<?php
/**
 * @brief Database logger class
 * @file axDatabaseLogger.class.php
 */

/**
 * @brief Database Logger Class
 *
 * Example of MySQL log table:
 * @code
 * CREATE TABLE IF NOT EXISTS `axiom`.`ax_log` (
 *   `id` INT NOT NULL AUTO_INCREMENT,
 *   `date` DATE NOT NULL,
 *   `severity` VARCHAR(10) NOT NULL,
 *   `message` TEXT NOT NULL,
 *   `log` VARCHAR(20),
 *   PRIMARY KEY (id)
 * ) ENGINE=InnoDB;
 * @endcode
 *
 * Example of query for this table:
 * @code
 * INSERT INTO `axiom`.`ax_log` VALUES (NULL,:date,:severity,:message,:log_id)
 * @endcode
 *
 * @warning An incorrect query will fail silently to prevent the logger from entering in a reccursive loop.
 *
 * @class axDatabaseLogger
 * @author Delespierre
 * @ingroup Log
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axDatabaseLogger extends axLogger {
    
    /**
     * @brief PDO instance
     * @property PDO $_pdo
     */
    protected $_pdo;
    
    /**
     * @brief Query to record log entries
     * @property string $_query
     */
    protected $_query;
    
    /**
     * @brief Prepared statement
     * @property PDOStatement $_stmt
     */
    protected $_stmt;
    
    /**
     * Constructor
     *
     * The @c $query parameter must contains 4 placeholders:
     * @li :date the log entry date
     * @li :log_id the log id
     * @li :severity the log entry severity
     * @li :message the log entry message
     *
     * Example of valid query:
     * @code
     * INSERT INTO `axiom`.`ax_log` VALUES (null,:date,:log_id,:severity,:message)
     * @endcode
     *
     * @param PDO $pdo
     * @param unknown_type $query
     * @param unknown_type $mask
     * @throws InvalidArgumentException If @c $pdo or @c $query are invalid
     * @throws LogicException If @c $query lack a placeholder
     * @throws RuntimeException If the statement for this query cannot be prepared
     */
    public function __construct (PDO $pdo, $query, $mask = false) {
        if (!$pdo)
            throw new InvalidArgumentException("A valid PDO instance is required");
        
        if (empty($query))
            throw new InvalidArgumentException("Invalid query");
        
        // Validating query
        if (strpos($query, ':date') === false)
            throw new LogicException("The query lacks the :date placeholder");
        if (strpos($query, ':log_id') === false)
            throw new LogicException("The query lacks the :log_id placeholder");
        if (strpos($query, ':severity') === false)
            throw new LogicException("The query lacks the :severity placeholder");
        if (strpos($query, ':message') === false)
            throw new LogicException("The query lacks the :message placeholder");
        
        parent::__construct($mask);
        $this->_pdo   = $pdo;
        $this->_query = $query;
        
        try {
            if (!$this->_stmt = $this->_pdo->prepare($query))
                throw new RuntimeException("Cannot prepare statement");
        }
        catch (Exception $e) {
            if (PHP_VERSION_ID >= 50300)
                throw new RuntimeException("Error occured while preparing statement", 0, $e);
            else
                throw new RuntimeException("Error occured while preparing statement: " . $e->getMessage());
        }
    }
    
    /**
     * @copybrief axLogger::writeMessage
     * @copydoc axLogger::writeMessage
     */
    public function writeMessage ($msg, $severity) {
        try {
            // ignore errors
            @$this->_stmt->execute(array(
                'date'     => date('Y-m-d H:i:s'),
                'log_id'   => $this->_loggerId,
                'severity' => $severity,
                'message'  => $msg,
            ));
        }
        catch (Exception $e) {
            // ignore exception
            return;
        }
    }
}