<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Log Class
 *
 * This class is capable of capturing PHP errors and
 * exception.
 * This class acts as a chain of responsibilities
 * where commands are instance of Logger.
 *
 * @author Delespierre
 * @package log
 * @subpackage Log
 */
class Log {
    
    /**
     * Configuration
     * @var array
     */
    protected static $_config;
    
    /**
     * First logger in the chain
     * @var Logger
     */
    protected static $_first;
    
    /**
     * Last logger in the chain
     * @var Logger
     */
    protected static $_last;
    
    /**
     * Log messages history (per request)
     * @var array
     */
    protected static $_message_history = array();
    
    /**
     * Configure
     * @param array $config
     * @return void
     */
    public static function setConfig (array $config = array()) {
        $defaults = array(
            'ignore_repeated_messages' => true,
            'log_errors' => true,
            'log_exception' => true,
        );
        
        self::$_config = $config + $defaults;
        
        if (self::$_config['log_errors'])
            self::registerErrorHandler();
            
        if (self::$_config['log_exception'])
            self::registerExceptionHandler();
    }
    
    /**
     * Push a message onto the chain
     * @param string $msg
     * @param integer $priority
     * @return void
     */
    public static function message ($msg, $priority) {
        if (!isset(self::$_first))
            return;
        
        if (self::$_config['ignore_repeated_messages']) {
            if (array_search($msg, self::$_message_history) !== false)
                return;
        }
        
        self::$_first->message(self::$_message_history[] = $msg, $priority);
    }
    
    /**
     * Push an error message onto the chain
     * @param string $msg
     * @return void
     */
    public static function error ($msg) {
        self::message($msg, Logger::ERR);
    }
    
    /**
     * Push a notice message onto the chain
     * @param string $msg
     * @return void
     */
    public static function notice ($msg) {
        self::message($msg, Logger::NOTICE);
    }
    
    /**
     * Push a warning message onto the chain
     * @param string $msg
     * @return void
     */
    public static function warning ($msg) {
        self::message($msg, Logger::WARNING);
    }
    
    /**
     * Push a debug message onto the chain.
     *
     * You may additionnaly pass a backtrace infromation
     * for debugging purposes.
     *
     * @param string $msg
     * @param array $bt
     * @return void
     */
    public static function debug ($msg, array $bt = array()) {
        if ($bt)
            $msg .= " in {$bt[0]['file']} on line {$bt[0]['line']}";
        
        self::message($msg, Logger::DEBUG);
    }
    
    /**
     * Attach a logger to the chain
     * @param Logger $logger
     * @return void
     */
    public static function addLogger (Logger $logger) {
        if (!isset(self::$_first))
            self::$_first = self::$_last = $logger;
        else
            self::$_last->setNext(self::$_last = $logger);
    }
    
    /**
     * Register Log as PHP error handler
     * @param integer $error_types
     * @return string
     */
    public static function registerErrorHandler ($error_types = -1) {
        return set_error_handler(array(__CLASS__, 'handleError'));
    }
    
    /**
     * Unregister Log as PHP error handler
     * @return boolean
     */
    public static function restoreErrorHandler () {
        return restore_error_handler();
    }
    
    /**
     * PHP error handler
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     * @throws ErrorException
     * @return void
     */
    public static function handleError ($errno, $errstr, $errfile, $errline) {
        $error = "(PHP Error) $errstr in $errfile on line $errline";
        switch ($errno) {
            case E_STRICT:
            case E_WARNING:
            case E_USER_WARNING:
                self::warning($error);
                break;
                
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_NOTICE:
            case E_USER_NOTICE:
                self::notice($error);
                break;
            
            case E_USER_ERROR:
                self::error($error);
                break;
                
            default:
            case E_RECOVERABLE_ERROR:
                throw new ErrorException($errstr, 2048, $errno, $errfile, $errline);
                break;
        }
    }
    
    /**
     * Register Log as PHP exception handler
     * @return boolean
     */
    public static function registerExceptionHandler () {
        return set_exception_handler(array(__CLASS__, 'handleException'));
    }
    
    /**
     * Unregister Log as PHP exception handler
     * @return boolean
     */
    public static function restoreExceptionHandler () {
        return restore_exception_handler();
    }
    
    /**
     * Handle an exception.
     *
     * When called directly by PHP in case
     * of uncatched error, the runtime will
     * fall after this call.
     *
     * You may use this method to log
     * manually any exception.
     *
     * @param Exception $exception
     * @retur void
     */
    public static function handleException (Exception $exception) {
        if (PHP_VERSION_ID >= 50300) {
            if ($previous = $exception->getPrevious()) {
                self::handleException($previous);
            }
        }
        
        $error = "(PHP Exception) " . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();
        self::error($error);
    }
    
    /**
     * Get messages history
     * @return array
     */
    public static function getHistory () {
        return self::$_message_history;
    }
}