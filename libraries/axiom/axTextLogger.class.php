<?php
/**
 * @brief Text logger class file
 * @file axTextLogger.class.php
 */

/**
 * @brief Text Logger Class
 *
 * @class axTextLogger
 * @author Delespierre
 * @ingroup Log
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axTextLogger extends axLogger {
    
    /**
     * @brief File handle
     * @property SplFileObject $_file
     */
    protected $_file;
    
    /**
     * Log lines format.
     *
     * 3 placeholder may be placed in this string. In order:
     * @li 1- date (ISO 2822)
     * @li 2- error severity ("Error", "Warning"...)
     * @li 3- error message
     *
     * Example:
     * @code
     * "(Date %s) [%s] %s" will display as
     * "(Date Thu, 08 Sep 2011 15:26:45 +0200) [Warning] message..."
     * @endcode
     *
     * @property string
     */
    public $format;
    
    /**
     * @brief Constructor.
     *
     * If $format parameter is not specified, the default format will be used (which is "[%s] %s: %s\n"). If
     * @c $open_mode parameter is not specified, the 'a' (Write + Append) open mode will be used.
     * The format string parameter are (in order):
     * @li date
     * @li logger id
     * @li severity
     * @li message
     *
     * @param string $filename
     * @param interger $mask @optional @default{false} If false, will handle all priorities
     * @param string $format @optional @default{false} If false, will use "[%s] [%s] %s: %s\n" as format
     * @param char $open_mode @optional @default{'a'}
     */
    public function __construct ($filename, $mask = false, $format = false, $open_mode = 'a') {
        if (!is_file($filename))
            throw new axMissingFileException($filename);
        
        parent::__construct($mask);
        try {
            $this->_file = new SplFileObject($filename, $open_mode);
        }
        catch (RuntimeException $e) {
            if (PHP_VERSION_ID >= 50300)
                throw new RuntimeException("Error occured while opening file", 0, $e);
            else
                throw new RuntimeException("Error occured while opening file: " . $e->getMessage());
        }
        $this->format = $format === false ? "[%s] [%s] %s: %s\n" : $format;
    }
    
    /**
     * @copydoc axLogger::writeMessage()
     */
    public function writeMessage ($msg, $severity) {
        @$this->_file->fwrite(sprintf($this->format, date('r'), $this->_loggerId, $severity, $msg));
    }
}