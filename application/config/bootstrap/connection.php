<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . "/core/Axiom_Database.class.php";

Axiom_Database::setConfig(array(
    'host'     => 'localhost',
    'database' => 'axiom',
    'username' => 'axiom',
    'password' => 'mysql*axiom',
    'type'     => 'mysql',
));

Axiom_Database::open();