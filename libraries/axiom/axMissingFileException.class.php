<?php
/**
 * @brief Missing file exception class file
 * @file axMissingFileException.class.php
 */

/**
 * @brief Missing File Exception
 *
 * This class extends PHP's RuntimeException
 *
 * @class axMissingFileException
 * @author Delespierre
 * @ingroup Exception
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axMissingFileException extends RuntimeException {
    
    /**
     * @brief Constructor
     * @param string $filename The file path or name
     * @param integer $code @optional @default{0}
     */
    public function __construct ($filename, $code = 0) {
        parent::__construct("File $filename not found", $code);
    }
}