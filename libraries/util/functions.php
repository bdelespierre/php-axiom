<?php
/**
 * @brief Functions Module
 *
 * This module contains some helper functions you may find useful.
 *
 * @defgroup Function
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * @briefRemoves all null, false or empty string values from an array
 *
 * The keys are preserved.
 *
 * @ingroup Functions
 * @param array $array
 * @param array $exclude @optional @default{array()} Additionnal values to exclude
 * @return void
 */
function array_safe_filter (array &$array, array $exclude = array()) {
    $exclude = array_merge(array(null, false, ""), $exclude);
    foreach ($array as $key => $value) {
        if (in_array($value, $exclude, true))
            unset($array[$key]);
    }
}

/**
 * @brief Create an annonymous function
 *
 * Usage:
 * @code
 * $alpha = callback('function ($a,$b) { return $a+$b+$c+$d; }');
 * @endcode
 *
 * Returns the anonymous function name
 *
 * @ingroup Functions
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
 * @brief Calculates the cartesian product of any number of array in parameter
 *
 * @ingroup Functions
 * @param mixed $a
 * @param mixed $b [...]
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

/**
 * @brief Find the closest item in a list
 *
 * Return the closest item from @c $haystack that match @c $needle (with less than @c $approx different characters) or
 * false if no match is found.
 *
 * @param string $needle The item you're looking for
 * @param array $haystack The list (of strings) you're looking in
 * @param integer $approx @optional @default{3} The tolerance threshold
 * @return string
 */
function array_find_closest ($needle, $haystack, $approx = 3) {
    if (($offset = array_search($needle, $haystack)) !== false)
        return $haystack[$offset];

    $distances = array();
    foreach ($haystack as $item) {
        $lev = levenshtein($needle, $item);

        // if needle is  not completely differen and not 'too' different
        if ($lev <= strlen($item) && $lev <= $approx)
            $distances[$item] = $lev;
    }
    asort($distances);
    return !empty($distances) ? key($distances) : false;
}

/**
 * @brief Sanitize a string to change every accented chars to unaccented ones
 * @param string $str The string to be sanitized
 * @return string
 */
function unaccent_chars ($str) {
    $search  = explode(",","ç,æ ,œ ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
    $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u"); 
    return str_replace($search, $replace, $str);
}
