<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Column Helper Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage helper
 */
class axColHelper extends axBaseHelper {
    
    /**
     * Default constructor
     */
    public function __construct () {
        parent::__construct('col');
    }
    
    /**
     * (non-PHPdoc)
     * @see axBaseHelper::setValue()
     */
    public function setValue ($value) {
        throw new BadMethodCallException("Col tag cannot have value", 3007);
    }
    
    /**
     * (non-PHPdoc)
     * @see axBaseHelper::appendChild()
     */
    public function appendChild ($node) {
        throw new BadMethodCallException("Col tag cannot have children", 3008);
    }
    
    /**
     * (non-PHPdoc)
     * @see axBaseHelper::prependChild()
     */
    public function prependChild ($node) {
        throw new BadMethodCallException("Col tag cannot have children", 3008);
    }
    
    /**
     * Constructor static alias
     * @return axColHelper
     */
    public static function export () {
        return new self;
    }
}