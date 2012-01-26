<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Table Caption Helper Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage helper
 */
class Axiom_CaptionHelper extends Axiom_BaseHelper {
    
    /**
     * Default constructor
     * @param string $value
     */
    public function __construct ($value) {
        parent::__construct('caption', array(), $value);
    }
    
    /**
     * Constructor static helper
     * @param string $value
     */
    public static function export ($value) {
        return new self ($value);
    }
}