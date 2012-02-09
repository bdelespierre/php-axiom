<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Library Class
 * 
 * TODO class documentation
 * 
 * This class is inspired by Gerald's Blog : http://www.croes.org/gerald/blog
 * 
 * @author Delespierre
 * @since 1.1.4
 * @package libaxiom
 * @subpackage core
 */
class axLibrary {
	
	/**
	 * Cache file
	 * @var string
	 */
	const CACHE_FILE = 'library.cache.php';
	
	/**
	 * Library directories
	 * @var array
	 */
	protected $_directories;
	
	/**
	 * Registered classes
	 * @var array
	 */
	protected $_classes;
	
	/**
	 * Cache dir path
	 * @var string
	 */
	protected $_cache_dir;
	
	/**
	 * Flag to tell if the library paths have been crawled
	 * true: crawl is possible
	 * false: crawl has been done before and will not be done again
	 * @var boolean
	 */
	protected $_regenerate_flag = true;
	
	/**
	 * Default constructor
	 * 
	 * Takes the cache directory path as only parameter.
	 * 
	 * @param string $cache_dir [optional]
	 */
	public function __construct ($cache_dir = false) {
		$this->_directories = array();
		$this->_classes = array();
		$this->_cache_dir = realpath($cache_dir);
	}
	
	/**
	 * Add a library to discover
	 * 
	 * $name parameter is a foldername.
	 * if it's not an actual folder, a seek will be perofrmed in
	 * /library and /application/library to find the directory.
	 * 
	 * You may also pass a $options parameters to set file 
	 * extensiosn for this library or to ask for a recursive
	 * inclusion.
	 * 
	 * A RuntimeExcetion is emitted if the library path cannot 
	 * be found.
	 * 
	 * @param string $name
	 * @param array $options [optional]
	 * @throws RuntimeException
	 * @return axLibrary
	 */
	public function add ($name, array $options = array()) {
		$default = array(
			'recursive' => false,
			'extension' => null,
		);
		
		$options += $default;
		if (!is_dir($dir = $name) && !is_dir($dir = AXIOM_LIB_PATH .'/'. $name) && !is_dir($dir = AXIOM_APP_PATH .'/library/'. $name))
			throw new RuntimeException("Cannot find library {$name}");
		
		if (!is_readable($dir))
			throw new RuntimeException("{$dir} is not readable");
		
		$this->_directories[$dir] = $options;
		return $this;
	}
	
	/**
	 * __autoload implementation
	 * 
	 * Will seek for the $classname in the local cache
	 * and include the proper file if found.
	 * 
	 * An axClassNotFoundException is thrown if such class
	 * can not be found, even after a library crawl.
	 * 
	 * @param string $classname
	 * @throws axClassNotFoundException
	 * @return boolean
	 */
	public function autoload ($classname) {
		if ($this->_load($classname))
			return true;
			
		if ($this->_regenerate_flag) {
			var_dump("REGENERATE !");
			$this->_regenerate_flag = false;
			$this->_includeAll();
			$this->_cache();
			return $this->autoload($classname);
		}
		
		throw new axClassNotFoundException($classname);
	}
	
	/**
	 * Register current instance as autoloader
	 * @return boolean
	 */
	public function register () {
		return spl_autoload_register(array($this, 'autoload'));
	}
	
	/**
	 * Crawls all the registered path to locate the library files
	 * according to the extension(s) provided as options
	 * (see axLibrary::add).
	 * @return void
	 */
	protected function _includeAll () {
		foreach ($this->_directories as $dir => $opt) {
			$directories = new AppendIterator();
			
			if (isset($opt['recursive']) && $opt['recursive'])
				$directories->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)));
			else
				$directories->append(new DirectoryIterator($dir));
			
			$ext = isset($opt['extensions']) ? $opt['extensions'] : '.class.php';
			$files = new axExtensionFilterIterator($directories, $ext);
			
			foreach ($files as $file) {
				$classname = substr((string)$file, 0, strpos((string)$file, '.'));
				$this->_classes[$classname] = (string)$file;
			}
		}
	}
	
	/**
	 * Tries to load the given class file
	 * @param string $classname
	 * @return boolean
	 */
	protected function _load ($classname) {
		if (empty($this->_classes)) {
			if ($this->_cache_dir && is_readable($c = $this->_cache_dir . '/' . self::CACHE_FILE)) {
				var_dump("CACHE READ !");
				require $c;
				$this->_classes = $classes;
			}
		}
		
		return isset($this->_classes[$classname]) && require_once $this->_classes[$classname];
	}
	
	/**
	 * Stores the local class cache in file for later use
	 * @return boolean
	 */
	protected function _cache () {
		$buffer = '<?php $classes=' . var_export($this->_classes, true) . '; ?>';
		return (boolean)file_put_contents($this->_cache_dir . '/' . self::CACHE_FILE, $buffer);
	}
}