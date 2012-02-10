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
 * where commands are instance of axLogger.
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage log
 */
class axLog {
    
    /**
     * Configuration
     * @var array
     */
    protected $_options;
    
    /**
     * First logger in the chain
     * @var axLogger
     */
    protected $_first;
    
    /**
     * Last logger in the chain
     * @var axLogger
     */
    protected $_last;
    
    /**
     * Log messages history (per request)
     * @var array
     */
    protected $_message_history = array();
    
    public function __construct (array $options = array()) {
    	$default = array(
    		'ignore_repeated_messages' => true,
    		'log_errors' => true,
    		'log_exception' => true,
    	);
    	
    	$this->_options = $options;
    	
    	if ($this->_options['log_errors'])
            $this->registerErrorHandler();
            
        if ($this->_options['log_exception'])
            $this->registerExceptionHandler();
    }
    
    /**
     * Push a message onto the chain
     * @param string $msg
     * @param integer $priority
     * @return void
     */
    public function message ($msg, $priority) {
        if (!isset($this->_first))
            return;
        
        if ($this->_options['ignore_repeated_messages'] && array_search($msg, $this->_message_history) !== false)
			return;
        
        $this->_first->message($this->_message_history[] = $msg, $priority);
    }
    
    /**
     * Push an error message onto the chain
     * @param string $msg
     * @return void
     */
    public function error ($msg) {
        $this->message($msg, axLogger::ERR);
    }
    
    /**
     * Push a notice message onto the chain
     * @param string $msg
     * @return void
     */
    public function notice ($msg) {
        $this->message($msg, axLogger::NOTICE);
    }
    
    /**
     * Push a warning message onto the chain
     * @param string $msg
     * @return void
     */
    public function warning ($msg) {
        $this->message($msg, axLogger::WARNING);
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
    public function debug ($msg, array $bt = array()) {
        if ($bt)
            $msg .= " in {$bt[0]['file']} on line {$bt[0]['line']}";
        
        $this->message($msg, axLogger::DEBUG);
    }
    
    /**
     * Attach a logger to the chain
     * @param axLogger $logger
     * @return void
     */
    public function addLogger (axLogger $logger) {
        if (!isset($this->_first))
            $this->_first = $this->_last = $logger;
        else
            $this->_last->setNext($this->_last = $logger);
    }
    
    /**
     * Register Log as PHP error handler
     * @param integer $error_types
     * @return string
     */
    public function registerErrorHandler ($error_types = -1) {
        return set_error_handler(array($this, 'handleError'));
    }
    
    /**
     * Unregister Log as PHP error handler
     * @return boolean
     */
    public function restoreErrorHandler () {
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
     * Register Log as PHP exception handler
     * @return boolean
     */
    public function registerExceptionHandler () {
        return set_exception_handler(array($this, 'handleException'));
    }
    
    /**
     * Unregister Log as PHP exception handler
     * @return boolean
     */
    public function restoreExceptionHandler () {
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
    public function handleException (Exception $exception) {
        if (PHP_VERSION_ID >= 50300) {
            if ($previous = $exception->getPrevious()) {
                $this->handleException($previous);
            }
        }
        
        $error = "(PHP Exception) " . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();
        $this->error($error);
    }
    
    /**
     * Get messages history
     * @return array
     */
    public function getHistory () {
        return $this->_message_history;
    }
}