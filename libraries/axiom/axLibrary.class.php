<?php
/**
 * @brief Library class file
 * @file axLibrary.class.php
 */

/**
 * @brief Library Class
 * 
 * @todo axLibrary long description here
 * 
 * This class is inspired by Gerald's Blog.
 * 
 * @link http://www.croes.org/gerald/blog
 * @author Delespierre
 * @since 1.2.0
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axLibrary {
	
	/**
	 * @brief Cache file
	 * @var string 
	 */
	const CACHE_FILE = 'library.cache.php';
	
	/**
	 * @brief Library directories
	 * @property array $_directories
	 */
	protected $_directories;
	
	/**
	 * @brief Registered classes
	 * @property array $_classes
	 */
	protected $_classes;
	
	/**
	 * @brief Cache dir path (false if cache is disabled)
	 * @property string $_cache_dir
	 */
	protected $_cache_dir;
	
	/**
	 * @brief Flag to tell if the library paths have been crawled
	 * 
	 * This flag takes 2 possible values
	 * @li true : crawl is possible
	 * @li false: crawl has been done before and will not be done again
	 * 
	 * @property boolean $_regenerate_flag
	 */
	protected $_regenerate_flag = true;
	
	/**
	 * @brief Constructor
	 * 
	 * Takes the cache directory path as only parameter.
	 * 
	 * @param string $cache_dir @optional @default{false} The cache directory (or false to disable the cache)
	 */
	public function __construct ($cache_dir = false) {
		$this->_directories = array();
		$this->_classes     = array();
		$this->_cache_dir   = $cache_dir !== false ? realpath($cache_dir) : false;
	}
	
	/**
	 * @brief Add a library to discover
	 * 
	 * The class file must be named according to the classname:
	 * @li for instance, the file for @c MyClass has to be named @c MyClass.php (pay attention to the case 
	 * sensitiveness)
	 * @li the extension is arbitrary (Axiom classes uses @c .class.php but you may use the extension you want as long 
	 * as you specify it in the @c $options parameter.
	 * 
	 * @note You may pass a $options parameters to set file extensions for the library or to ask for a recursive 
	 * inclusion. A reccursive inclusion consist of a complete directory tree traversing to find the class files.
	 * 
	 * @warning This class CANNOT handle more than one class per file for now.
	 * 
	 * @param string $name The name of a folder located in @c /libraries or @c /application/libraries, or a path
	 * @param array $options @optional @default{array()}
	 * @throws RuntimeException If the library path cannot be found
	 * @return axLibrary
	 */
	public function add ($name, array $options = array()) {
		$default = array(
			'recursive' => false,
			'extension' => null,
		);
		
		$options += $default;
		if (!is_dir($dir = $name) 
		 && !is_dir($dir = AXIOM_LIB_PATH .'/'. $name) 
		 && !is_dir($dir = AXIOM_APP_PATH .'/library/'. $name))
			throw new RuntimeException("Cannot find library {$name}");
		
		if (!is_readable($dir))
			throw new RuntimeException("{$dir} is not readable");
		
		$this->_directories[$dir] = $options;
		return $this;
	}
	
	/**
	 * @brief __autoload implementation
	 * 
	 * Will seek for the @c $classname in the local cache and include the proper file if found.
	 * 
	 * @param string $classname
	 * @throws axClassNotFoundException If such class can not be found, even after a library crawl
	 * @return boolean
	 */
	public function autoload ($classname) {
		if ($this->_load($classname))
			return true;
			
		if ($this->_regenerate_flag) {
			$this->_regenerate_flag = false;
			$this->_includeAll();
			$this->_cache();
			return $this->autoload($classname);
		}
		
		return false;
	}
	
	/**
	 * @breif Register current instance as autoloader
	 * @return boolean
	 */
	public function register () {
		return spl_autoload_register(array($this, 'autoload'));
	}
	
	/**
	 * @brief Crawls all the registered path to locate the library files according to the extension(s) provided as 
	 * options
	 * @see axLibrary::add()
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
			    $basename  = $file->getBasename();
				$classname = substr($basename, 0, strpos($basename, '.'));
				$this->_classes[$classname] = $file->getRealPath();
			}
		}
	}
	
	/**
	 * @brief Tries to load the given class file
	 * @param string $classname
	 * @return boolean
	 */
	protected function _load ($classname) {
		if (empty($this->_classes)) {
			if ($this->_cache_dir && is_readable($c = $this->_cache_dir . '/' . self::CACHE_FILE)) {
				require $c;
				$this->_classes = $classes;
			}
		}
		
		return isset($this->_classes[$classname]) && require_once $this->_classes[$classname];
	}
	
	/**
	 * @brief Stores the local class cache in file for later use
	 * 
	 * Does nothing if the cache is not activated.
	 * 
	 * @return boolean
	 */
	protected function _cache () {
	    if (!$this->_cache_dir)
	        return false;
	    
		$buffer = '<?php $classes=' . var_export($this->_classes, true) . '; ?>';
		return (boolean)file_put_contents($this->_cache_dir . '/' . self::CACHE_FILE, $buffer);
	}
}