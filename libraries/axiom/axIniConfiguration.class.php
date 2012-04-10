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
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axIniConfiguration implements axConfiguration {
	
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
     * @brief INI Tree structure
     * @property axConfigurationItem $_tree
     */
    protected $_tree;

    /**
     * @brief Constructor
     * @note If the @c $cache_dir isn't valid, the cache will be silently disabled
     * @param string $file The INI file path to parse
     * @param string $section The section to be used
     */
    public function __construct ($file, $section) {
    	$this->_file      = $file;
    	$this->_section   = $section;
    	
    	$this->_generateTree($this->_section);
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
     * @brief Serializable serialize method
     * @return string
     */
    public function serialize () {
        return serialize(array(
            'file'    => $this->_file,
            'section' => $this->_section,
            'tree'    => $this->_tree,
        ));
    }
    
    /**
     * @brief Serializable unserialize method
     * @param string serialized
     * @return void
     */
    public function unserialize ($serialized) {
        $struct = unserialize($serialized);
        if (!isset($struct['file'], $struct['section'], $struct['tree']))
            throw new RuntimeException("Cannot unserialize " . __CLASS__ . " instance, cache is corrupted");
        
        $this->_file    = $struct['file'];
        $this->_section = $struct['section'];
        $this->_tree    = $struct['tree'];
    }
}