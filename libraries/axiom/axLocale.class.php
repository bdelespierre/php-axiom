<?php
/**
 * @brief Locale class file
 * @file axLocale.class.php
 */

/**
 * @brief Locale class
 * 
 * @todo Locale class description
 * @author Delespierre
 * @since 1.1.4
 * @package libaxiom
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axLocale implements IteratorAggregate {
	
	/**
	 * @brief Cache file name
	 * @var string
	 */
	const CACHE_FILE = 'locale.cache.php';
	
	/**
	 * @brief Accepted language cache
	 * @property array $_accepted_languages_cache
	 */
	protected static $_accepted_languages_cache;
	
	/**
	 * @brief Dictionnary file
	 * @property string $_file
	 */
	protected $_file;
	
	/**
	 * @brief Current lang used
	 * @property string $_lang
	 */
	protected $_lang;
	
	/**
	 * @brief Cache directory (false if cache is disabled)
	 * @property string $_cache_dir
	 */
	protected $_cache_dir;
	
	/**
	 * @brief The translations tree
	 * @property axTreeItem $tree
	 */
	protected $tree;
	
	/**
	 * @brief Constructor
	 * @param string $lang_file The langs file path, this file MUST be an INI file
	 * @param string $lang @optional @default{"auto"} The language to be used or @c 'auto' to determine it from the 
	 * browser
	 * @param string $default_lang @optional @default{"en"} The default language to use if the @c $lang isn't found in 
	 * the dictionnary
	 * @param string $cache_dir @optional @default{false} The cache directory (or false if cache is disabled)
	 * @throws axMissingFileException If the lang file could not be found
	 */
	public function __construct ($lang_file, $lang = "auto", $default_lang = "en", $cache_dir = false) {
		$this->_lang      = strtolower($lang);
		$this->_cache_dir = $cache_dir !== false ? realpath($cache_dir) : false;
		$this->_tree      = array();
		
		if (!$this->_file = realpath($lang_file)) {
			throw new axMissingFileException($lang_file);
		}
		
		if ($this->_lang !== 'auto' && !setlocale(LC_ALL^LC_MESSAGES, $this->_lang)) {
			throw new RuntimeException("Cannot set locale to {$this->_lang}");
		}
	}
	
	/**
	 * @brief  Get a translation key
	 * @param string $key
	 * @return axTreeItem
	 */
	public function __get ($key) {
		return $this->getIterator()->$key;
	}
	
	/**
	 * @brief Get the internal iterator
	 * @see IteratorAggregate::getIterator()
	 * @return axTreeItem
	 */
	public function getIterator () {
		if (empty($this->_tree)) {
			if ($this->_cache_dir && is_readable($c = $this->_cache_dir . '/' . self::CACHE_FILE)) {
				require $c;
				$this->_tree = $tree;
			}
			else {
				$this->_generateTree();
				$this->_cache();
			}
		}
		
		if ($this->_lang === 'auto' && !($this->_lang = $this->_determineLanguage()))
			$this->_lang = $this->_default_lang;
		
		if (!isset($this->_tree[$this->_lang]))
			throw new RuntimeException("Lang {$this->_lang} not available");
		
		return $this->_tree[$this->_lang];
	}
	
	/**
	 * @brief Set the current lang
	 * 
	 * Returns the current axLocale instance in case of success or false in case of failure.
	 * 
	 * @note You may specify "auto" to determine the language automatically
	 * @param string $lang The lang to be used
	 * @return axLocale
	 */
	public function setLang ($lang) {
		if ($lang === "auto" && !($this->_lang = $this->_determineLanguage()))
			return false;
		
		if (!setlocale(LC_ALL, $this->_lang = $lang))
			return false;
		
		return $this;
	}
	
	/**
	 * @brief Get the current lang
	 * @return string
	 */
	public function getLang () {
	    return $this->_lang;
	}
	
	/**
	 * @brief Get the localized formats
	 * @link http://php.net/manual/en/function.localeconv.php
	 * @return array
	 */
	public function conv () {
		return localeconv();
	}
	
	/**
	 * @brief Get date
	 * 
	 * Will use the current date format to generate a date. The date format must be defined as a string in the
	 * dictionnary file (key is @c date.format).
	 * 
	 * @param integer $time @optional @default{null}
	 * @return string
	 */
	public function date ($time = null) {
		if ($time === null)
			return date($this->date->format);
		else
			return date($this->date->format, $time);
	}
	
	/**
	 * @brief Get date human representation
	 * 
	 * For instance "X hours ago" where X is a number determined by the @c $date parameter against the current 
	 * timestamp.
	 * 
	 * @warning You must use the ymdHi format for the $date parameter or a integer representing a timestamp.
	 * @param mixed $date The date (ymdHi format) or a timestamp
	 * @return string
	 */
	public function date2string ($date) {
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
	                $date_string = $this->i18n('date.minutes_ago', 1) +1;
	            }
	            elseif($minusdate > 59) {
	                $date_string = $this->i18n('date.minutes_ago', $minusdate - 40);
	            }
	            elseif($minusdate > 1 && $minusdate < 59) {
	                $date_string = $this->i18n('date.minutes_ago', $minusdate);
	            }
	            break;
	        
	        case ($minusdate > 99 && $minusdate < 2359):
	            $flr = floor($minusdate * .01) +1;
	            if($flr == 1) {
	                $date_string = $this->i18n('date.hours_ago', 1);;
	            }
	            else {
	                $date_string = $this->i18n('date.hours_ago', $flr);
	            }
	            break;
	        
	        case ($minusdate > 2359 && $minusdate < 310000):
	            $flr = floor($minusdate * .0001) +1;
	            if($flr == 1) {
	                $date_string = $this->i18n('date.days_ago', 1);
	            }
	            else {
	                $date_string = $this->i18n('date.days_ago', $flr);
	            }
	            break;
	        
	        case ($minusdate > 310001 && $minusdate < 12320000):
	            $flr = floor($minusdate * .000001) +1;
	            if($flr == 1) {
	                $date_string = $this->i18n('date.months_ago', 1);
	            }
	            else {
	                $date_string = $this->i18n('date.months_ago', $flr);
	            }
	            break;
	        
	        case ($minusdate > 100000000):
	        default:
	            $flr = floor($minusdate * .00000001) +1;
	            if($flr == 1) {
	                $date_string = $this->i18n('date.years_ago', 1);
	            }
	            else {
	                $date_string = $this->i18n('date.years_ago', $flr);
	            }
	    }
	
	    return $date_string;
	}
	
	/**
	 * @brief Use a string format for identifying translations
	 * @param string $key
	 * @param mixed $arg @optional @multiple You may pass as many arguments as the translation accepts (according to
	 * the sprintf syntax) 
	 * @return string
	 */
	public function i18n ($key) {
		$args = func_get_args();
		array_shift($args);
		
		$msg = $this->getIterator();
		foreach (explode('.', $key) as $k)
			$msg = $msg->$k;
			
		if (!$msg->getValue()) {
			trigger_error("Undefined translation {$key}");
			return "";
		}
		
        switch (count($args)) {
            case 0: return $msg; break;
            case 1: return sprintf((string)$msg, $args[0]); break;
            case 2: return sprintf((string)$msg, $args[0], $args[1]); break;
            case 3: return sprintf((string)$msg, $args[0], $args[1], $args[2]); break;
            case 4: return sprintf((string)$msg, $args[0], $args[1], $args[2], $args[3]); break;
            case 5: return sprintf((string)$msg, $args[0], $args[1], $args[2], $args[3], $args[4]); break;
            default:
                array_unshift($msg, $args);
                return call_user_func_array('sprintf', $args);
                break;
        }
	}
	
	/**
	 * @brief Generates the translation tree
	 * @throws axMissingFileException If the file is not found or not readable
	 * @throws RuntimeException If the file could not be parsed
	 * @return void
	 */
	protected function _generateTree () {
		if (!is_file($this->_file) || !is_readable($this->_file))
            throw new axMissingFileException($this->_file);
		
        if (!$ini = parse_ini_file($this->_file, true))
            throw new RuntimeException("Cannot parse $file");
        
        foreach (array_keys($ini) as $key) {
            if (($offset = strpos($key, ':')) !== false && isset($ini[trim(substr($key, $offset+1))]))
                $ini[$key] += $ini[trim(substr($key, $offset+1))];
                
			$tree = new axTreeItem;
			foreach ($ini[$key] as $k => $v) {
				$p = explode('.', $k);
				$c = $tree;
				foreach ($p  as $sk)
					$c = $c->$sk;
				$c->setValue($v);
			}
			$this->_tree[trim(substr($key, 0, $offset ? $offset : strlen($key)))] = $tree;
        }
	}
	
	/**
	 * @brief Put the dictionnary in cache for later use
	 * 
	 * Does nothing if cache is disabled
	 * 
	 * @rerturn void
	 */
	protected function _cache () {
		if (!$this->_cache_dir)
			return false;
		
		$buffer = '<?php $tree=' . var_export($this->_tree, true) . '; ?>';
		return (boolean)file_put_contents($this->_cache_dir . '/' . self::CACHE_FILE, $buffer);
	}
	
	/**
     * @brief Get accepeted languages using browser capabilities
     * 
     * The returned array will be ordered by browser preference (for instance en_US > en_GB > en). 
     * 
     * @return array
     */
    protected static function _getAcceptedLanguages () {
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
            
            $countries   = explode('-', $match[1]);
            $region      = array_shift($countries);
            $country_sub = explode('_', $region);
            $region      = array_shift($country_sub);
            
            foreach($countries as $country)
                $languages[$region . '_' . strtoupper($country)] = $quality;
            
            foreach($country_sub as $country)
                $languages[$region . '_' . strtoupper($country)] = $quality;

            $languages[$region] = $quality;
        }
        
        return self::$_accepted_languages_cache = $languages;
    }
    
    /**
     * @brief Determine the nearest available lang according to accepted languages
     * 
     * Will return false if no corresponding language could be found
     * 
     * @return string
     */
    protected function _determineLanguage () {
    	foreach ($this->_getAcceptedLanguages() as $accept => $priority) {
    		if (isset($this->_tree[$accept]))
    			return $accept;
    	}
    	return false;
    }
}