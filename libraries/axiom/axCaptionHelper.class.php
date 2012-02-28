<?php
/**
 * @brief Caption helper class file
 * @file axCaptionHelper.class.php
 */

/**
 * @brief HTML table caption helper
 *
 * @class axCaptionHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axCaptionHelper extends axBaseHelper {
    
    /**
     * @biref Constructor
     * @param string $value The caption value
     */
    public function __construct ($value) {
        parent::__construct('caption', array(), $value);
    }
    
    /**
     * @brief Constructor static helper
     * @param string $value The caption value
     */
    public static function export ($value) {
        return new self ($value);
    }
}