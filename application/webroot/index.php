<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require dirname(dirname(dirname(__FILE__))) . "/application/config/bootstrap.php";

// TODO axRouter refactoring :)
axRouter::run();