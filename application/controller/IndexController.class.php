<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

class IndexController extends axBaseController {
    
    public static function index () {
        $axversion = Axiom::VERSION;
        return compact('axversion');
    }
}