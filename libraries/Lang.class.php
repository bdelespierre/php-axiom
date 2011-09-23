<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Lang Managemnt Class
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage Lang
 */
class Lang {

    /**
     * Configuration
     * @var array
     */
    protected static $_config = array();
    
    /**
     * Translation cache
     * @var array
     */
    protected static $_translations = array();
    
    /**
     * Accepted language cache
     * @var array
     */
    protected static $_accepted_languages_cache;
    
    /**
     * Set configuration.
     * If the $load_language parameter is set to true,
     * this method will initialize translations directly.
     * @param array $config = array()
     * @param boolean $load_loanguage = true
     * @return void
     */
    public static function setConfig ($config = array()) {
        $default = array(
            'locale' => 'fr',
            'locales' => array('fr'),
            'date_format' => 'd/m/y h:i:s',
            'lang_dir' => dirname(dirname(__FILE__)) . '/application/locale/langs',
            'lang_file' => 'fr.ini',
            'base_url' => '/',
        );
        
        self::$_config = array_merge($default, $config);
        
        if (isset(self::$_config['locale']) && self::$_config['locale'] === 'auto') {
            $lngs = self::getAcceptedLanguages();
            $found = false;
            if (!empty($lngs)) {
                while ($lng = array_shift($lngs)) {
                    if (in_array($lng, self::$_config['locales'])) {
                        self::$_config['locale'] = $lng;
                        self::$_config['lang_file'] = "$lng.ini";
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Load language
     * @param string $lang_file = null
     * @throws MissingFileException
     * @throws RuntimeException
     * @return void
     */
    public static function loadLanguage ($lang_file = null) {
        if (!isset($lang_file))
            $lang_file = self::$_config['lang_dir'] . '/' . self::$_config['lang_file'];
        
        if (!file_exists($lang_file)) {
            throw new MissingFileException($lang_file, 2029);
        }
        
        if (!$desc = parse_ini_file($lang_file, true)) {
            throw new RuntimeException("Could not parse $lang_file", 2030);
        }
        
        self::$_config = array_merge(self::$_config, $desc['settings']);
        self::$_translations = array_merge(self::$_translations, $desc['translations']);
    }
    
    /**
     * Get current locale
     * @return string
     */
    public static function getLocale () {
        return self::$_config['locale'];
    }
    
    /**
     * Set current locale
     * @param string $lang
     * @param boolean $reload_translations = true
     * @return string
     */
    public static function setLocale ($lang, $reload_translations = true) {
        if ($lang !== self::getLocale() && in_array($lang, self::$_config['locales'])) {
            self::$_config['locale'] = $lang;
            self::$_config['lang_file'] = "$lang.ini";
            
            if ($reload_translations)
                self::loadLanguage();
        }
        
        return self::$_config['locale'];
    }
    
    /**
     * Get avaialable locales
     * @return array
     */
    public static function getLocales () {
        return self::$_config['locales'];
    }
    
    /**
     * Get date format
     * @return string
     */
    public static function getDateFormat () {
        return self::$_config['date_format'];
    }
    
    /**
     * Get lang file
     * @return string
     */
    public static function getLangFile () {
        return self::$_config['lang_file'];
    }
    
    /**
     * Get translation table
     * @return array
     */
    public static function getTranslations () {
        return self::$_translations;
    }
    
    /**
     * Get accepeted languages using browser capabilities
     * @return array
     */
    public static function getAcceptedLanguages() {
        if (isset(self::$_accepted_languages_cache))
            return self::$_accepted_languages_cache;
        
        $httplanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $languages = array();
        if (empty($httplanguages)) {
            return $languages;
        }
        
        foreach (explode(',', $httplanguages) as $accept) {
            $result = preg_match('/^([a-z]{1,8}(?:[-_][a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accept, $match);

            if (!$result) {
                continue;
            }
            if (isset($match[2])) {
                $quality = (float)$match[2];
            }
            else {
                $quality = 1.0;
            }
            
            $countries = explode('-', $match[1]);
            $region = array_shift($countries);
            $country_sub = explode('_', $region);
            $region = array_shift($country_sub);
            
            foreach($countries as $country)
                $languages[$region . '_' . strtoupper($country)] = $quality;
            
            foreach($country_sub as $country)
                $languages[$region . '_' . strtoupper($country)] = $quality;

            $languages[$region] = $quality;
        }
        
        return self::$_accepted_languages_cache = $languages;
    }
    
    /**
     * Translate
     * @param string $key
     * @return string
     */
    public static function i18n ($key) {
        if (empty(self::$_translations))
            self::loadLanguage();
        
        if (!isset(self::$_translations[$key])) {
            return "<!-- UNDEFINED TRANSLATION: $key -->";
        }
        
        $args = func_get_args();
        $key = array_shift($args);
        $msg = (string)self::$_translations[$key];
        switch (count($args)) {
            case 0: return $msg; break;
            case 1: return sprintf($msg, $args[0]); break;
            case 2: return sprintf($msg, $args[0], $args[1]); break;
            case 3: return sprintf($msg, $args[0], $args[1], $args[2]); break;
            case 4: return sprintf($msg, $args[0], $args[1], $args[2], $args[3]); break;
            case 5: return sprintf($msg, $args[0], $args[1], $args[2], $args[3], $args[4]); break;
            default:
                array_unshift($msg, $args);
                return call_user_func_array('sprintf', $args);
                break;
        }
    }
    
    public static function getBaseUrl () {
        return self::$_config['base_url'];
    }
    
    /**
     * Format URL
     * @param string $route
     * @param string $action = ""
     * @param string $lang = false
     * @return string
     */
    public static function url ($url, $lang = false) {
        if (!$lang)
            $lang = self::getLocale();
            
        return self::$_config['base_url'] . "$lang/$url";
    }
    
    /**
     * Format src
     * @param string $ressource
     * @return string
     */
    public static function src ($ressource) {
        if (strpos($ressource, '/') === 0)
            $ressource = substr($ressource, 1);
            
        return self::$_config['base_url'] . $ressource;
    }
}

/**
 * (Non PHP-doc)
 * @see Lang::i18n
 */
function i18n ($key) {
    $args = func_get_args();
    switch (count($args)) {
        case 1: return Lang::i18n($args[0]); break;
        case 2: return Lang::i18n($args[0], $args[1]); break;
        case 3: return Lang::i18n($args[0], $args[1], $args[2]); break;
        case 4: return Lang::i18n($args[0], $args[1], $args[2], $args[3]); break;
        case 5: return Lang::i18n($args[0], $args[1], $args[2], $args[3], $args[4]); break;
        default: return call_user_func_array(array('Lang', 'i18n'), $args); break;
    }
}

/**
 * (Non PHP-doc)
 * @see Lang::url
 */
function url ($url, $lang = false) {
    return Lang::url($url, $lang);
}

/**
 * (Non PHP-doc)
 * @see Lang::src
 */
function src ($ressource) {
    return Lang::src($ressource);
}

/**
 * Parse a date to return an human readable date
 * @param mixed $date
 * @return string
 */
function date2str ($date) {
    if (is_int($date)) {
        $date = date('ymdHi', $date);
    }
    
    $minusdate = date('ymdHi') - $date;
    
    if($minusdate > 88697640 && $minusdate < 100000000) {
        $minusdate = $minusdate - 88697640;
    }
    
    switch ($minusdate) {
        case ($minusdate < 99):
            if($minusdate == 1) {
                $date_string = i18n('date.minutes_ago', 1);
            }
            elseif($minusdate > 59) {
                $date_string =  i18n('date.minutes_ago', $minusdate - 40);
            }
            elseif($minusdate > 1 && $minusdate < 59) {
                $date_string = i18n('date.minutes_ago', $minusdate);
            }
            break;
        
        case ($minusdate > 99 && $minusdate < 2359):
            $flr = floor($minusdate * .01);
            if($flr == 1) {
                $date_string = i18n('date.hours_ago', 1);;
            }
            else {
                $date_string = i18n('date.hours_ago', $flr);
            }
            break;
        
        case ($minusdate > 2359 && $minusdate < 310000):
            $flr = floor($minusdate * .0001);
            if($flr == 1) {
                $date_string = i18n('date.days_ago', 1);
            }
            else {
                $date_string = i18n('date.days_ago', $flr);
            }
            break;
        
        case ($minusdate > 310001 && $minusdate < 12320000):
            $flr = floor($minusdate * .000001);
            if($flr == 1) {
                $date_string = i18n('date.months_ago', 1);
            }
            else {
                $date_string = i18n('date.months_ago', $flr);
            }
            break;
        
        case ($minusdate > 100000000):
        default:
            $flr = floor($minusdate * .00000001);
            if($flr == 1) {
                $date_string = i18n('date.years_ago', 1);
            }
            else {
                $date_string = i18n('date.years_ago', $flr);
            }
    }

    return $date_string;
}