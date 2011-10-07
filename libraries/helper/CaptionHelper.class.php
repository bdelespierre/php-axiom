<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

class CaptionHelper extends BaseHelper {
    
    public function __construct ($value) {
        parent::__construct('caption', array(), $value);
    }
    
    public static function export ($value) {
        return new self ($value);
    }
}