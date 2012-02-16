<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Lowercase first character
 * 
 * @package libaxiom
 * @subpackage functions
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
 * Removes all null, false or empty string values from an array
 * 
 * The keys are preserved.
 * 
 * @package libaxiom
 * @subpackage functions
 * @param array $array
 * @param array $exclude [optional] [defualt array()] Additionnal values to exclude
 * @return void
 */
if (!function_exists("array_safe_filter")) {
    function array_safe_filter (array &$array, array $exclude = array()) {
        $exclude = array_merge(array(null, false, ""), $exclude);
        foreach ($array as $key => $value) {
            if (in_array($value, $exclude, true))
                unset($array[$key]);
        }
    }
}

/**
 * Create an annonymous function
 *
 * E.G:
 * * $alpha = callback('function ($a,$b) { return $a+$b+$c+$d; }');
 * 
 * Returns the anonymous function name
 * 
 * @package libaxiom
 * @subpackage functions
 * @param string $fct
 * @return string
 */
function callback ($fct) {
    if (!preg_match('~(function)?\s*\((?P<args>[^\)]*)\)\s*\{(?P<code>.*)\}~', $fct, $matches))
        return false;

    $args = $matches['args'];
    $code = $matches['code'];
    return create_function($args, $code);
}

/**
 * Calculates the cartesian product of any number of array in parameter
 * 
 * @package libaxiom
 * @subpackage functions
 * @param mixed $a
 * @param mixed $b ...
 * @return array
 */
function array_cartesian_product () {
    if (!$c = func_num_args())
        return array();
    
    if ($c == 1) {
        foreach ((array)func_get_arg(0) as $v)
            $r[] = (array)$v;
        return $r;
    }

    $a = func_get_args();
    $f = array_shift($a);
    $s = call_user_func_array(__FUNCTION__, $a);

    foreach ((array)$f as $v) {
        foreach ($s as $w) {
            array_unshift($w, $v);
            $r[] = $w;
        }
    }

    return $r;
}