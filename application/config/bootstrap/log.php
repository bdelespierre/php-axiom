<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/log/Axiom_Log.class.php';
require_once LIBRARY_PATH . '/log/Axiom_Logger.class.php';
require_once LIBRARY_PATH . '/log/Axiom_TextLogger.class.php';

Axiom_Log::setConfig();
Axiom_Log::addLogger(new Axiom_TextLogger(APPLICATION_PATH . '/ressource/log/app.log'));

Axiom_Log::debug('Log handler started');