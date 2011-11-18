<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/log/Log.class.php';
require_once LIBRARY_PATH . '/log/Logger.class.php';
require_once LIBRARY_PATH . '/log/TextLogger.class.php';

Log::setConfig();
Log::addLogger(new TextLogger(APPLICATION_PATH . '/ressource/log/app.log'));

Log::debug('Log handler started');