<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Utilities Functions
 * @package utilities
 */

/**
 * Lowercase first character.
 * lcfirst doesn't exists in PHP 5.1,
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
 * Removes all null, false or empty string values from an array.
 * The keys are preserved.
 * @param array $array
 * @param array $exclude Additionnal values to exclude
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
 * Create an annonymous function according
 * to its declaration.
 *
 * The declaration works the same way the
 * PHP 5.3 closures does and may have an
 * use statement in it.
 *
 * Because the use statement is impossible
 * to emulate properly, it was simply removed.
 *
 * E.G:
 * * $alpha = callback('function ($a,$b) { return $a+$b+$c+$d; }');
 *
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
 * Calculates the cartesian product of
 * any number of array in parameters.
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