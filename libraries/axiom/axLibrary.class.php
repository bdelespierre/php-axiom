<?php
/**
 * @brief Library class file
 * @file axLibrary.class.php
 */

/**
 * @brief Library Class
 *
 * This class is inspired by Gerald's Blog.
 * @link http://www.croes.org/gerald/blog
 *
 * @todo axLibrary long description
 * @author Delespierre
 * @since 1.2.0
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axLibrary {
	
    /**
     * @brief Default library path
     * @property string $_defaultLibPath
     */
    protected $_defaultLibPath;
    
    /**
     * @brief Default application path
     * @property string $_defaultAppPath
     */
    protected $_defaultAppPath;
	
	/**
	 * @brief Constructor
	 * @param string $lib_path @optional @default{null} The default library path
	 * @param string $app_path @optional @default{null} The default application path
	 * @param string $extensions @optionnal @default{'.class.php'} The class files extension
	 */
	public function __construct ($lib_path = "", $app_path = "", $extensions = ".class.php") {
		$this->_defaultLibPath = rtrim($lib_path, '/');
		$this->_defaultAppPath = rtrim($app_path, '/');
		
		spl_autoload_extensions($extensions);
		$this->register();
	}
	
	/**
	 * @brief Add a library
	 *
	 * Will seek for the library into the default library path and into the default app path if @c $name is not
	 * a valid directory.
	 *
	 * @param string $name The name of a folder located in @c /libraries or @c /application/libraries, or a path
	 * @throws RuntimeException If the library path cannot be found or if the include path cannot be defined
	 * @return axLibrary
	 */
	public function add ($name) {
		if (!is_dir($dir = $name)
		 && !is_dir($dir = $this->_defaultLibPath .'/'. $name)
	     && !is_dir($dir = $this->_defaultAppPath .'/'. $name)
		 && !is_dir($dir = $this->_defaultAppPath .'/library/'. $name))
			throw new RuntimeException("Cannot find library {$name}");
		
		if (!set_include_path(get_include_path() . PATH_SEPARATOR .  $dir))
		    throw new RuntimeException("Cannot add $dir to include path");
		
		return $this;
	}
	
	/**
	 * @breif Register autoloader
	 * @return boolean
	 */
	public function register () {
		return spl_autoload_register();
	}
}