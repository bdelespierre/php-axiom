<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

date_default_timezone_set("Europe/Paris");

error_reporting(-1);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

define('MESSAGE_WARNING', 'warnings');
define('MESSAGE_ALERT', 'alerts');
define('XML_HEADER', '<?xml version="1.0" encoding="UTF-8" ?>');

if (!defined('PHP_VERSION_ID')) {
    $php_version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($php_version[0] * 10000 + $php_version[1] * 100 + $php_version[2]));
}

/**
 * User functions
 */

/**
 * lcfirst doesn't exists in PHP 5.1,
 * Lowercase first character.
 * @param string
 * @return string
 */
if (!function_exists("lcfirst")) {
    function lcfirst ($string) {
        $string{0} = strtolower($string{0});
        return $string;
    }
}

/**
 * Prefix every key of the array with
 * the given prefix.
 * @param array $array
 * @param string $prefix
 * @return array
 */
if (!function_exists("array_keys_prefix")) {
    function array_keys_prefix (array $array, $prefix) {
        if (empty($array))
            return array();
        
        $keys = array_keys($array);
        foreach ($keys as $key => $value)
            $keys[$key] = $prefix . $value;
            
        return array_combine($keys, $array);
    }
}

/**
 * Filter all null, false, empty string or --
 * values of the given array
 * @param array $array
 * @return array
 */
if (!function_exists("array_safe_filter")) {
    function array_safe_filter (array $array) {
        foreach ($array as $key => $value) {
            if ($value === null || $value === false || $value === "" || $value === "--")
                unset($array[$key]);
        }
        return $array;
    }
}