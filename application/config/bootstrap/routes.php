<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/core/Axiom_Router.class.php';

Axiom_Router::setConfig();

// News Routes
Axiom_Router::connect('/{:lang::?}/news/{:id:\d+}', 'NewsController::view', array('module' => 'news'));
Axiom_Router::connect('/{:lang::?}/news/{:action::?}', 'NewsController', array('module' => 'news'));
Axiom_Router::connect('/{:lang::?}/admin/news/{:action::?}', 'AdminNewsController', array('module' => 'news'));

// Admin Routes
Axiom_Router::connect('/{:lang::?}/{:action:(login|logout)}', 'AdminController', array('module' => 'admin'));
Axiom_Router::connect('/{:lang::?}/admin/{:controller}/{:action::?}', array(), array('module' => 'admin'));
Axiom_Router::connect('/{:lang::?}/admin/{:action::?}', 'AdminController', array('module' => 'admin'));

// Default Routes
Axiom_Router::connect('/{:lang::?}/{:controller}/{:action::?}');
Axiom_Router::connect('/{:lang::?}', 'IndexController::index');
Axiom_Router::connect('/', 'IndexController::index');
