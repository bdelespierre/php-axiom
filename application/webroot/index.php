<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Initialize the application
 */
require dirname(dirname(dirname(__FILE__))) . "/application/config/bootstrap.php";

/**
 * Let's get started !
 */
axRouter::run();