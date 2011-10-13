<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

date_default_timezone_set("Europe/Paris");

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);

define('MESSAGE_WARNING', 'warnings');
define('MESSAGE_ALERT', 'alerts');
define('XML_HEADER', '<?xml version="1.0" encoding="UTF-8" ?>');

if (!defined('PHP_VERSION_ID')) {
    $php_version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($php_version[0] * 10000 + $php_version[1] * 100 + $php_version[2]));
}