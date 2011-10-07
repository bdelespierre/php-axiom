<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Missing File Exception
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage MissingFileException
 */
class MissingFileException extends RuntimeException {
    
    /**
     * Default constructor
     * @param string $filename
     * @param integer $code = 0
     * @param Exception $previous = null
     */
    public function __construct ($filename, $code = 0, Exception $previous = null) {
        parent::__construct("File $filename not found", $code, $previous);
    }
}