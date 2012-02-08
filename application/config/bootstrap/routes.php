<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/core/axRouter.class.php';

axRouter::setConfig();

// News Routes
axRouter::connect('/{:lang::?}/news/{:id:\d+}', 'NewsController::view', array('module' => 'news'));
axRouter::connect('/{:lang::?}/news/{:action::?}', 'NewsController', array('module' => 'news'));
axRouter::connect('/{:lang::?}/admin/news/{:action::?}', 'AdminNewsController', array('module' => 'news'));

// Admin Routes
axRouter::connect('/{:lang::?}/{:action:(login|logout)}', 'AdminController', array('module' => 'admin'));
axRouter::connect('/{:lang::?}/admin/{:controller}/{:action::?}', array(), array('module' => 'admin'));
axRouter::connect('/{:lang::?}/admin/{:action::?}', 'AdminController', array('module' => 'admin'));

// Default Routes
axRouter::connect('/{:lang::?}/{:controller}/{:action::?}');
axRouter::connect('/{:lang::?}', 'IndexController::index');
axRouter::connect('/', 'IndexController::index');
