<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

class IndexController extends BaseController {
    
    public static function index () {
        $axiom_version = AXIOM_VERSION;
        return compact('axiom_version');
    }
}