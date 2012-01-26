<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Column Group Helper Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage helper
 */
class Axiom_ColGroupHelper extends Axiom_BaseHelper {
    
    /**
     * Default constructor
     */
    public function __construct () {
        parent::__construct('colgroup');
    }
    
    /**
     * Add a column to the colgroup and
     * return it
     * @return Axiom_ColHelper
     */
    public function addCol () {
        return $this->appendChild(Axiom_ColHelper::export());
    }
    
    /**
     * Constructor static alias
     * @return Axiom_ColGroupHelper
     */
    public static function export () {
        return new self ();
    }
}