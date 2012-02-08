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
 * @package libaxiom
 * @subpackage core
 */
class axAutoloader {
    
    /**
     * Autoloader config
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
                LIBRARY_PATH . '/core',
                LIBRARY_PATH . '/exception',
                LIBRARY_PATH . '/helper',
                LIBRARY_PATH . '/log',
                LIBRARY_PATH . '/feed',
				LIBRARY_PATH . '/upload',
				LIBRARY_PATH . '/captcha',
				LIBRARY_PATH . '/browser',
				LIBRARY_PATH . '/configuration',
            ),
            'extensions' => '.class.php'
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
    public static function addPath ($path) {
        if (!is_dir($path))
            throw new axMissingFileException($path, 2044);
            
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
            
        spl_autoload_extensions(self::$_config['extensions']);
        return spl_autoload_register();
    }
    
    /**
     * Load the given class
     * @param string $class
     * @return boolean
     */
    public static function load ($class) {
        try {
            spl_autoload($class);
            return true;
        }
        catch (Exception $e) {
            return false;
        }
    }
    
}