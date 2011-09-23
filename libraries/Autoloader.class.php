<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Autoloader Class
 *
 * @author Delespierre
 * @version $Rev: 65 $
 * @subpackage Autoloader
 */
class Autoloader {
    
    /**
     * Autoloader contfig
     * @var array
     */
    protected static $_config = array();
    
    protected static $_al_registered;
    
    /**
     * Set the Autoload configuration
     * @param array $config = array()
     * @return void
     */
    public static function setConfig ($config = array()) {
        $default = array(
            'paths' => array(
                APPLICATION_PATH . '/controller',
                APPLICATION_PATH . '/model',
                LIBRARY_PATH,
                LIBRARY_PATH . '/exceptions',
                LIBRARY_PATH . '/helpers',
                LIBRARY_PATH . '/loggers',
                LIBRARY_PATH . '/feeds',
				LIBRARY_PATH . '/uploader',
            ),
            'extension' => '.class.php',
        );
        
        self::$_config = array_merge_recursive($default, $config);
    }
    
	/**
     * Add a path to the autoloader and return it.
     *
     * Note: you must start the autoloader when calling this
     * method or the changes since the last 'start' invocation
     * will not be effective.
     *
     * @param string $path
     * @param string $name = null
     * @throws RuntimeException
     * @return boolean
     */
    public static function add ($path) {
        if (!file_exists($path))
            throw new RuntimeException("Path $path not found", 2044);
            
        return self::$_config['paths'][] = $path;
    }
    
    /**
     * Start auloading handle.
     * Will return the spl_autoload_register status.
     * @param array $paths = array()
     * @return boolean
     */
    public static function start ($paths = array()) {
        $include_path = array_unique(array_merge(self::$_config['paths'], explode(PATH_SEPARATOR, get_include_path())));
        
        if (set_include_path(implode(PATH_SEPARATOR, $include_path)) === false)
            throw new RuntimeException("Could not register the new include path", 2045);
            
        return spl_autoload_register(array(__CLASS__, 'load'));
    }
    
    /**
     * Prevent the autoloader to load classes.
     *
     * This method may be useful if you have
     * a custom autoloader or if you're using
     * another framework.
     *
     * Note: the include path previously set
     * by Autoloader::start is preserved.
     *
     * @return boolean
     */
    public static function stop () {
        return spl_autoload_unregister(array(__CLASS__, 'load'));
    }
    
    /**
     * Load the give class.
     *
     * Will return true if the class file
     * was successfuly loaded, false otherwise
     *
     * EG: Autoloader::load('MyRandomClass');
     *
     * Note: The autoloader will seek for a
     * file named MyRandomClass.class.php,
     * this extension may be set manualy in
     * Autoloader::setConfig.
     *
     * @param string $class
     * @return boolean
     */
    public static function load ($class) {
        return @include_once $class . self::$_config['extension'];
    }
}