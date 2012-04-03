<?php
/**
 * @brief Log class file
 * @file axLog.class.php
 */

/**
 * @brief Log Class
 *
 * This class is capable of capturing PHP errors and exception. This class acts as a chain of responsibilities where
 * commands are instance of axLogger.
 *
 * @todo finish axLog long description
 * @class axLog
 * @author Delespierre
 * @ingroup Log
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axLog {
    
    /**
     * @brief Configuration
     * @property array $_options
     */
    protected $_options;
    
    /**
     * @brief First logger in the chain
     * @property axLogger $_first
     */
    protected $_first;
    
    /**
     * @brief Last logger in the chain
     * @property axLogger $_last
     */
    protected $_last;
    
    /**
     * @brief Log messages history (per request)
     * @property array $_message_history
     */
    protected $_message_history = array();
    
    /**
     * @brief Constructor
     *
     * The @c $options parameters is described as follow:
     * @code
     * array(
     *		'ignore_repeated_messages' => true,
     *		'log_errors' => true,
     *		'log_exception' => true,
     *	);
     * @endcode
     *
     * @param array $options @optional @default{array()} The log options
     */
    public function __construct (array $options = array()) {
    	$default = array(
			'ignore_repeated_messages' => true,
    		'log_errors'               => true,
    		'log_exception'            => true,
    	);
    	
    	$this->_options = $options;
    	
    	if ($this->_options['log_errors'])
            $this->registerErrorHandler();
            
        if ($this->_options['log_exception'])
            $this->registerExceptionHandler();
    }
    
    /**
     * @brief Push a message onto the chain
     * @warning If no logger is attached to axLog, will silently return the current instance and no operation is
     * performed
     * @param string $msg The message
     * @param integer $priority The priority (see axLogger constants for priorities)
     * @return axLog
     */
    public function message ($msg, $priority) {
        if (!isset($this->_first))
            return $this;
        
        if ($this->_options['ignore_repeated_messages'] && array_search($msg, $this->_message_history) !== false)
			return $this;
        
        $this->_first->message($this->_message_history[] = $msg, $priority);
        return $this;
    }
    
    /**
     * @brief Push an error message onto the chain
     * @see axLog::message()
     * @param string $msg The message
     * @return axLog
     */
    public function error ($msg) {
        return $this->message($msg, axLogger::ERR);
    }
    
    /**
     * @brief Push a notice message onto the chain
     * @see axLog::message()
     * @param string $msg The message
     * @return axLog
     */
    public function notice ($msg) {
        return $this->message($msg, axLogger::NOTICE);
    }
    
    /**
     * @brief Push a warning message onto the chain
     * @see axLog::message()
     * @param string $msg The message
     * @return axLog
     */
    public function warning ($msg) {
        return $this->message($msg, axLogger::WARNING);
    }
    
    /**
     * @brief Push a debug message onto the chain.
     * @see axLog::message()
     * @note You may additionnaly pass a backtrace infromation for debugging purposes.
     * @param string $msg The message
     * @param array $bt The backtrace (result of PHP's debug_backtrace())
     * @return axLog
     */
    public function debug ($msg, array $bt = array()) {
        if ($bt)
            $msg .= " in {$bt[0]['file']} on line {$bt[0]['line']}";
        
        return $this->message($msg, axLogger::DEBUG);
    }
    
    /**
     * @brief Attach a logger to the chain
     *
     * This method accepts several forms:
     * @li axLog::addLogger( axLogger $logger );
     * @li axLog::addLogger( string $log_class, $param1, $param2 ... );
     *
     * The following calls are equivalents:
     * @code
     * $log->addLogger(new axTextLogger('error.log', axLogger::ERR + axLogger::WARNING));
     * $log->addLogger('axTextLogger', 'error.log', axLogger::ERR + axLogger::WARNING));
     * @endcode
     *
     * @param axLogger $logger The logger instance to attach
     * @return axLog
     */
    public function addLogger () {
        $argc = func_num_args();
        $args = func_get_args();
        
        if (!$argc)
            throw new RuntimeException("At least one argument is madatory");
        
        if ($argc == 1 && $args[0] instanceof axLogger) {
            $logger = $args[0];
        }
        else {
            $class = array_shift($args);
            
            if (!class_exists($class, true))
                throw new axClassNotFoundException($class);
            
            if (!in_array('axLogger', class_parents($class)))
                throw new LogicException("Class $class does not extend axLogger");
            
            try {
                switch (count($args)) {
                    case 0: $logger = new $class; break;
                    case 1: $logger = new $class($args[0]); break;
                    case 2: $logger = new $class($args[0], $args[1]); break;
                    case 3: $logger = new $class($args[0], $args[1], $args[2]); break;
                    case 4: $logger = new $class($args[0], $args[1], $args[2], $args[3]); break;
                    case 5: $logger = new $class($args[0], $args[1], $args[2], $args[3], $args[4]); break;
                    default:
                        $reflect = new ReflectionClass($class);
                        $logger  = $reflect->newInstanceArgs($args);
                        break;
                }
            }
            catch (Exception $e) {
                if (PHP_VERSION_ID >= 50300)
                    throw new RuntimeException("Cannot instanciate $class", 0, $e);
                else
                    throw new RuntimeException("Cannot instanciate $class: " . $e->getMessage());
            }
        }
        
        if (!isset($this->_first))
            $this->_first = $this->_last = $logger;
        else
            $this->_last->setNext($this->_last = $logger);
        return $this;
    }
    
    /**
     * @brief Register instance as PHP error handler
     * @param integer $error_types @optional @default{-1} The errors to catch (all errors by default)
     * @return string
     */
    public function registerErrorHandler ($error_types = -1) {
        return set_error_handler(array($this, 'handleError'));
    }
    
    /**
     * @breif Unregister instance as PHP error handler
     * @return boolean
     */
    public function restoreErrorHandler () {
        return restore_error_handler();
    }
    
    /**
     * @breif Error handler
     * @param integer $errno Error number
     * @param string $errstr Error message
     * @param string $errfile Error file
     * @param integer $errline Error line
     * @throws ErrorException If the error is a E_RECOVERABLE_ERROR (so you can still catch it)
     * @return void
     */
    public function handleError ($errno, $errstr, $errfile, $errline) {
        $error = "(PHP Error) $errstr in $errfile on line $errline";
        switch ($errno) {
            case E_STRICT:
            case E_WARNING:
            case E_USER_WARNING:
                $this->warning($error);
                break;
                
            case E_NOTICE:
            case E_USER_NOTICE:
                $this->notice($error);
                break;
            
            case E_USER_ERROR:
                $this->error($error);
                break;
                
            default:
            case E_RECOVERABLE_ERROR:
                throw new ErrorException($errstr, 2048, $errno, $errfile, $errline);
                break;
        }
    }
    
    /**
     * @brief Register Log as PHP exception handler
     * @return boolean
     */
    public function registerExceptionHandler () {
        return set_exception_handler(array($this, 'handleException'));
    }
    
    /**
     * @brief Unregister Log as PHP exception handler
     * @return boolean
     */
    public function restoreExceptionHandler () {
        return restore_exception_handler();
    }
    
    /**
     * @brief Handle an exception.
     *
     * When called directly by PHP in case of uncatched error, the runtime will fall after this call.
     * @note You may use this method to log manually any exception.
     * @note If you're running PHP >= 5.3, the previous exceptions (if any) will be registered as well.
     * @param Exception $exception The exception to be handled
     * @retur void
     */
    public function handleException (Exception $exception) {
        if (PHP_VERSION_ID >= 50300) {
            if ($previous = $exception->getPrevious()) {
                $this->handleException($previous);
            }
        }
        
        $error = "(PHP Exception) " . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' .
            $exception->getLine();
        $this->error($error);
    }
    
    /**
     * @brief Get messages history
     * @return array
     */
    public function getHistory () {
        return $this->_message_history;
    }
}

/**
 * @brief Log Module
 *
 * This module contains all classes necessary for logging facilities.
 *
 * @defgroup Log
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */