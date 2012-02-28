<?php
/**
 * @brief INI configuration class file
 * @file axIniConfiguration.class.php
 */

/**
 * @brief INI Configuration file parser
 *
 * @todo axIniConfiguration class description
 * @author Delespierre
 * @ingroup Configuration
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axIniConfiguration implements axConfiguration {

	/**
	 * @brief Cache file
	 * @var string
	 */
	const CACHE_FILE = "config.cache.php";
	
	/**
	 * @brief Configuration file path
	 * @property string $_file
	 */
	protected $_file;
	
	/**
	 * @brief Configuration section
	 * @property string $_section
	 */
	protected $_section;
	
	/**
	 * @brief Cache directory (false if cache is disabled)
	 * @property string $_cache_dir
	 */
	protected $_cache_dir;

    /**
     * @brief INI Tree structure
     * @property axConfigurationItem $_tree
     */
    protected $_tree;

    /**
     * @brief Constructor
     * @note If the @c $cache_dir isn't valid, the cache will be silently disabled
     * @param string $file The INI file path to parse
     * @param string $section The section to be used
     * @param string $cache_dir @optional @default{false} The directory for caching (or false if cache is disabled)
     */
    public function __construct ($file, $section, $cache_dir = false) {
    	$this->_file      = $file;
    	$this->_section   = $section;
    	$this->_cache_dir = $cache_dir !== false ? realpath($cache_dir) : false;
    }

    /**
     * @copydoc axConfiguration::__get()
     */
    public function __get ($key) {
        return $this->getIterator()->$key;
    }
    
    /**
     * @copydoc IteratorAggregate::getIterator()
     */
    public function getIterator() {
    	if (!isset($this->_tree)) {
	    	if ($this->_cache_dir && is_readable($c = $this->_cache_dir . '/' . self::CACHE_FILE)) {
	    		require $c;
	    		$this->_tree = $tree;
	    	}
	    	else {
	        	$this->_generateTree($this->_section);
	        	$this->_cache();
	    	}
    	}
    	
    	return $this->_tree;
    }
	
	/**
     * @brief Generates the tree structure using the INI structure
     * @param string $section The section to use
     * @throws RuntimeException If the file cannot be parsed
     * @return void
     */
    protected function _generateTree ($section) {
    	if (!is_file($this->_file) || !is_readable($this->_file))
            throw new axMissingFileException($this->_file);

        if (!$ini = parse_ini_file($this->_file, true))
            throw new RuntimeException("Cannot parse $file");
        
        foreach (array_keys($ini) as $key) {
            if (($offset = strpos($key, ':')) !== false && isset($ini[trim(substr($key, $offset+1))]))
                $ini[$key] += $ini[trim(substr($key, $offset+1))];
            if (strpos(trim($key), $section) === 0)
                $section = $key;
        }
    	
        if (!isset($ini[$section]))
            throw new RuntimeException("Unable to find section $section");
        	
        $this->_tree = new axTreeItem;
        foreach ($ini[$section] as $key => $value) {
            $p = explode('.', $key);
            $c = $this->_tree;
            foreach ($p as $k)
                $c = $c->__get($k);
            $c->setValue($value);
        }
    }
    
    /**
     * @brief Put current configuration tree in cache for later use
     * 
     * Does nothing if the cache is disabled.
     * 
     * @return boolean
     */
	protected function _cache () {
		if (!$this->_cache_dir)
			return false;
		
		$buffer = '<?php $tree=' . var_export($this->_tree, true) . '; ?>';
		return (boolean)file_put_contents($this->_cache_dir . '/' . self::CACHE_FILE, $buffer);
	}
}