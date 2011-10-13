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
 * Converts any value to its declaration value.
 *
 * This function is useful when you need to
 * generate pieces of codes, more specificly
 * when you create a callback using the
 * callback() function.
 *
 * @internal
 * @param mixed $value
 * @return string
 */
function native2str ($value) {
    if (is_string($value))
        $str = "'{$value}'";
    elseif (is_int($value))
        $str = "{$value}";
    elseif (is_bool($value))
        $str = $value ? "true" : "false";
    elseif (is_float($value))
        $str = strpos($value, '.') === false ? "{$value}.0" : $value;
    elseif (is_object($value))
        $str = "Object";
    elseif (is_array($value)) {
        $arr = array();
        foreach ($value as $key => $svalue)
            $arr[] = native2str($key) . '=>' . native2str($svalue);
        $arr = implode(',', $arr);
        $str = "array({$arr})";
    }
    else
        $str = "null";
    return $str;
}

/**
 * Create an annonymous function according
 * to its declaration.
 *
 * The declaration works the same way the
 * PHP 5.3 closures does and may have an
 * use statement in it.
 *
 * E.G:
 * * $alpha = callback('($a,$b) use ($c,&$d) { return $a+$b+$c+$d; }');
 *
 * @param unknown_type $fct
 */
function callback ($fct) {
    if (!preg_match('~^\(\s*(?P<args>(&?\s*\$\w+\s*,?\s*)*)\s*\)\s*(use\s*\(\s*(?P<use>(&?\s*\$\w+\s*,?\s*)+)\s*\))?\s*\{(?P<code>.*)\}$~',
        $fct, $matches))
        return false;
	
    $args = $matches['args'];
    $use  = $matches['use'];
    $code = $matches['code'];
    if (!empty($use)) {
        foreach (explode(',', $use) as $var) {
			trim($var);
            $value = isset($GLOBALS[substr($var,1)]) ? $GLOBALS[substr($var,1)] : null;
			
            if (($offset = strpos($var, '&') !== false) || is_object($value))
                $code = 'global ' . substr($var, $offset+1) . ";" . $code;
            else
                $code = "{$var}=" . native2str($value) . ";" . $code;
        }
    }
	
    return create_function($args, $code);
}