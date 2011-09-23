<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/Router.class.php';

Router::setConfig();

/**
 * Default Routes
 */
Router::connect('/{:lang}/{:controller}/{:action}');
Router::connect('/{:lang}/{:controller}');
Router::connect('/{:lang}', 'IndexController::index');
Router::connect('/', 'IndexController::index');