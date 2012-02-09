<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * INI Configuration file parser
 *
 * TODO class description
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage configuration
 */
class axIniConfiguration implements axConfiguration {

	/**
	 * Cache file
	 * @var string
	 */
	const CACHE_FILE = "config.cache.php";
	
	/**
	 * Configuration file
	 * @var string
	 */
	protected $_file;
	
	/**
	 * Configuration section
	 * @var string
	 */
	protected $_section;
	
	/**
	 * Cache directory
	 * @var string
	 */
	protected $_cache_dir;

    /**
     * INI Tree structure
     * @var axConfigurationItem
     */
    protected $_tree;

    /**
     * Default constructor
     * @param string $file
     * @param string $section [optional]
     * @throws axMissingFileException
     * @throws RuntimeException
     */
    public function __construct ($file, $section, $cache_dir = false) {
    	$this->_file = $file;
    	$this->_section = $section;
    	$this->_cache_dir = realpath($cache_dir);
    }

    /**
     * Generates the tree structure using the INI structure
     * @param string $section
     * @throws RuntimeException
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
     * (non-PHPdoc)
     * @see axConfiguration::__get()
     */
    public function __get ($key) {
        return $this->getIterator()->$key;
    }
    
    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator() {
    	if (!isset($this->_tree)) {
	    	if ($this->_cache_dir && is_readable($c = $this->_cache_dir . '/' . self::CACHE_FILE)) {
	    		require $c;
	    		$this->_tree = $tree;
	    	}
	    	else {
	        	$this->_generateTree($section);
	        	$this->_cache();
	    	}
    	}
    	
    	return $this->_tree;
    }
    
    /**
     * Put current configuration tree in cache for later use
     * @return boolean
     */
	protected function _cache () {
		if (!$this->_cache_dir)
			return false;
		
		$buffer = '<?php $tree=' . var_export($this->_tree, true) . '; ?>';
		return (boolean)file_put_contents($this->_cache_dir . '/' . self::CACHE_FILE, $buffer);
	}
}