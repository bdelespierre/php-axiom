<?php
/**
 * @brief Logger class file
 * @file axLogger.class.php
 */

/**
 * @brief Logger Abstract Class
 * 
 * This class is the base implementation for any logger class. When extending this class, you just have to implement the
 * @c writeMessage method and optionaly to override the constructor to build your own custom loggers.
 * Logger are designed in a way they will always forward the message to the next logger (see axLogger::setNext()) after
 * writing it (if the priority match its mask).
 * 
 * @class axLogger
 * @author Delespierre
 * @ingroup Log
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
abstract class axLogger {
    
    /**
     * Constants
     * @var string
     */
    const ERR = 1;
    const NOTICE = 2;
    const WARNING = 4;
    const DEBUG = 8;
    
    /**
     * @breif axLogger's mask
     * @property integer $_mask
     */
    protected $_mask;
    
    /**
     * @brief Next logger in chain
     * @property axLogger
     */
    protected $_next;
    
    /**
     * @brief Constructor
     * @param integer $mask The mask of priorities to match (or false to match all priorities)
     */
    public function __construct ($mask = false) {
        if (!$mask)
            $mask = 15;
        $this->_mask = $mask;
    }
    
    /**
     * @brief Set next logger in chain and return the attached logger
     * @param axLogger $logger The logger to attach as next in the chain
     * @return axLogger
     */
    public function setNext (axLogger $logger) {
        return $this->_next = $logger;
    }
    
    /**
     * @brief Send a message through the chain
     * 
     * Will write the message using the writeMessage method if the @c $priority parameter match the mask (defined in
     * constructor). In any case, will send the message and its priority to the next logger (if any).
     * The the @c $priority parameter doesn't match any of axLogger constant, then the axLogger::ERR will be used.
     * 
     * @param string $msg The message
     * @param integer $priority @optional @default{axLogger::NOTICE} The message's priority
     * @return axLogger
     */
    public function message ($msg, $priority = self::NOTICE) {
        switch ($priority) {
            default:
            case self::ERR:     $severity = "Error";   break;
            case self::NOTICE:  $severity = "Notice";  break;
            case self::WARNING: $severity = "Warning"; break;
            case self::DEBUG:   $severity = "Debug";   break;
        }
        
        if ($priority & $this->_mask)
            $this->writeMessage($msg, $severity);
            
        if (isset($this->_next))
            $this->_next->message($msg, $priority);
            
        return $this;
    }
    
    /**
     * @brief Write the message
     * @abstract
     * @param string $msg The message to write
     * @param string $severity (one of 'Error','Notice','Warning', or 'Debug')
     * @return axLogger
     */
    abstract public function writeMessage ($msg, $severity);
}