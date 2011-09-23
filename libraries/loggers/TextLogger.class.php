<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

class TextLogger extends Logger {
    
    /**
     * File handle
     * @var SplFileObject
     */
    protected $_file;
    
    /**
     * Log lines format.
     *
     * Note: 3 placeholder may be placed in
     * this string. In order:
     * 1- date (ISO 2822)
     * 2- error severity ("Error", "Warning"...)
     * 3- error message
     *
     * EG:
     * "(Date %s) [%s] %s" will display as
     * "(Date Thu, 08 Sep 2011 15:26:45 +0200) [Warning] message..."
     *
     * @var string
     */
    public $format;
    
    /**
     * Default constructor.
     *
     * If $format parameter is not specified, the
     * default format will be used (which is "[%s] %s: %s\n")
     *
     * If $open_mode parameter is not specified, the 'a'
     * open mode will be used
     *
     * @param string $filename
     * @param interger $mask
     * @param string $format = fasle
     * @param unknown_type $open_mode
     */
    public function __construct ($filename, $mask, $format = false, $open_mode = 'a') {
        parent::__construct($mask);
        try {
            $this->_file = new SplFileObject($filename, $open_mode);
        }
        catch (RuntimeException $e) {
            return;
        }
        if ($format)
            $this->format = $format;
    }
    
    /**
     * (non-PHPdoc)
     * @see Logger::writeMessage()
     */
    public function writeMessage ($msg, $severity) {
        $format = isset($this->format) ? $this->format : "[%s] %s: %s\n";
        $this->_file->fwrite(sprintf($format, date('r'), $severity, $msg));
    }
}