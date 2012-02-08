<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/log/axLog.class.php';
require_once LIBRARY_PATH . '/log/axLogger.class.php';
require_once LIBRARY_PATH . '/log/axTextLogger.class.php';

axLog::setConfig();
axLog::addLogger(new axTextLogger(APPLICATION_PATH . '/ressource/log/app.log'));

axLog::debug('Log handler started');