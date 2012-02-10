<?php

class axLocale implements IteratorAggregate {
	
	const CACHE_FILE = 'locale.%s.cache.php';
	
	protected static $_accepted_languages_cache;
	
	protected $_lang_file;
	protected $_lang;
	protected $_cache_dir;
	
	protected $tree;
	
	public function __construct ($lang_file, $lang = "auto", $default_lang = "en", $cache_dir = false) {
		$this->_lang = strtolower($lang);
		$this->_lang_file = $lang_file;
		$this->_cache_dir = realpath($cache_dir);
		$this->_tree = array();
	}
	
	public function __get ($key) {
		return $this->getIterator()->$key;
	}
	
	public function getIterator () {
		if (!isset($this->_tree)) {
			if ($this->_cache_dir && is_readable($c = $this->_cache_dir . '/' . sprintf(self::CACHE_FILE, $this->lang))) {
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
			throw new RuntimeException("Land {$lang} not available");
		
		return $this->_tree[$this->_lang];
	}
	
	public function setLang ($lang) {
		if ($lang === "auto" && !($this->_lang = $this->_determineLanguage()))
			return false;
		
		$this->_lang = $lang;
	}
	
	protected function _generateTree ($section) {
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
	
	protected function _cache () {
		if (!$this->_cache_dir)
			return false;
		
		$buffer = '<?php $tree=' . var_export($this->_tree, true) . '; ?>';
		return (boolean)file_put_contents($this->_cache_dir . '/' . sprintf(self::CACHE_FILE, $this->_lang), $buffer);
	}
	
	/**
     * Get accepeted languages using browser capabilities
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
    
    protected function _determineLanguage () {
    	foreach ($this->_getAcceptedLanguages() as $accept) {
    		if (isset($this->_tree[$accept]))
    			return $accept;
    	}
    	return false;
    }
}