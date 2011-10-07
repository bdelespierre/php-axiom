<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

abstract class Logger {
    
    const ERR = 1;
    const NOTICE = 2;
    const WARNING = 4;
    const DEBUG = 8;
    
    /**
     * Logger's mask
     * @var integer
     */
    protected $_mask;
    
    /**
     * Next logger in chain
     * @var Logger
     */
    protected $_next;
    
    /**
     * Default constructor
     * @param integer $mask
     */
    public function __construct ($mask = false) {
        if (!$mask)
            $mask = 15;
        $this->_mask = $mask;
    }
    
    /**
     * Set next logger in chain
     * and return the attached logger
     * @param Logger $logger
     * @return Logger
     */
    public function setNext (Logger $logger) {
        return $this->_next = $logger;
    }
    
    /**
     * Send a message through the chain
     * @param string $msg
     * @param integer $priority = 5
     */
    public function message ($msg, $priority = self::NOTICE) {
        switch ($priority) {
            default:
            case self::ERR: $severity = "Error"; break;
            case self::NOTICE: $severity = "Notice"; break;
            case self::WARNING: $severity = "Warning"; break;
            case self::DEBUG: $severity = "Debug"; break;
        }
        
        if ($priority & $this->_mask)
            $this->writeMessage($msg, $severity);
            
        if (isset($this->_next))
            $this->_next->message($msg, $priority);
    }
    
    /**
     * Write the message
     * @abstract
     * @param string $msg
     */
    abstract public function writeMessage ($msg, $severity);
}