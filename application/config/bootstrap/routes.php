<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/core/Router.class.php';

Router::setConfig();

// Project Routes
Router::connect('/foo/bar', 'FooController::bar');
Router::connect('/{:lang::?}/test', 'TestController', array('module' => 'tests'));

// Default Routes

Router::connect('/{:lang::?}/{:controller}/{:action::?}');
Router::connect('/{:lang::?}', 'IndexController::index');
Router::connect('/', 'IndexController::index');
