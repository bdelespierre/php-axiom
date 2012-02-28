<?php
/**
 * @brief Axiom class file
 * @file Axiom.class.php
 */

/**
 * @brief Main framework class.
 * 
 * This class is a front-end to the most used class in the Axiom framework. It provides direct access to
 * @li Configuration (Axiom::configuration)
 * @li Libraries (Axiom::library)
 * @li Localization (Axiom::locale)
 * @li Database Connection (Axiom::database)
 * @li Session (Axiom::session)
 * @li Logging (Axiom::log)
 * @li Captcha (Axiom::captcha)
 * @li Module Management (Axiom::module)
 * @li View Management (Axiom::view)
 * 
 * This class is also a bridge between the different libraries compounded in the framework and their configuration that
 * is being held by the axConfiguration object. Axiom class brings all these items together so you don't have to setup 
 * the configuration manually.
 * 
 * @class Axiom
 * @author Delespierre
 * @ingroup Core
 * @since 1.2.0
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @copyright http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
final class Axiom {
    
    /**
     * @brief Axiom Framework Version
     */
    const VERSION = '1.2.0';

    /**
     * @brief Flag used to toggle cache
     * @property boolean $cache
     */
    public static $cache = true;
    
    /**
     * @brief Configuration object
     * @internal
     * @property axConfiguration $_config
     */
	private static $_config;
	
	/**
	 * @brief Library object
	 * @internal
	 * @property axLibrary $_library
	 */
	private static $_library;
	
	/**
	 * @brief Localization object
	 * @internal
	 * @property axLocale $_locale
	 */
	private static $_locale;

	/**
	 * @brief Database connection object
	 * @internal
	 * @property axDatabase $_database
	 */
	private static $_database;
	
	/**
	 * @brief Session object
	 * @internal
	 * @property axSession $_session
	 */
	private static $_session;
	
	/**
	 * @brief Log object
	 * @internal
	 * @property axLog $_log
	 */
	private static $_log;
	
	/**
	 * @brief Captcha object
	 * @internal
	 * @property axCaptcha $_captcha
	 */
	private static $_captcha;
	
	/**
	 * @brief Module Manager object
	 * @internal
	 * @property axModuleManager $_module
	 */
	private static $_module;
	
	/**
	 * @brief View Manager object
	 * @internal
	 * @property axViewManager $_view
	 */
	private static $_view;
	
	/**
	 * @brief Get the configuration object
	 * 
	 * If the configuration object is not defined it will be initialized according to the method parameters.
	 * If no parameter is provided for the first call (implicit initialization) then the default parameters will be 
	 * used.
	 * 
	 * @param string $file @optional @default{"/application/config/config.ini"} The configuration file to be used
	 * @param string $section @optional @default{"default"} The configuration section to be used
	 * @param string $class @optional @default{"axIniConfiguration"} The configuration class to be used
	 * @throws RuntimeException If the configuration class cannot be found
	 * @return axConfiguration
	 */
	public static function configuration () {
		if (isset(self::$_config))
			return self::$_config;
			
		if (!func_num_args())
			trigger_error("Configuration initialized with default parameters", E_USER_WARNING);
			
		$defaults = array(AXIOM_APP_PATH . '/config/config.ini', 'default', 'axIniConfiguration');
		list($file, $section, $class) = func_get_args() + $defaults;
		
		if (!class_exists($class, true))
			throw new axClassNotFoundException($class);
		
        $cache_dir = self::$cache ? AXIOM_APP_PATH . '/ressource/cache' : false;
        
		return self::$_config = new $class($file, $section, $cache_dir);
	}
	
	/**
	 * @brief Get the library object
	 * 
	 * If the library object is not defined it will be initialized. No parameter is required to initialize the library 
	 * object. Calling this method for the first time will register the library object as default Autoloader for PHP.
	 * 
	 * @return axLibrary
	 */
	public static function library () {
		if (isset(self::$_library))
			return self::$_library;
		
		$cache_dir = self::$cache ? AXIOM_APP_PATH . '/ressource/cache' : false;
			
		self::$_library = new axLibrary($cache_dir);
		self::$_library->register();
		return self::$_library;
	}
	
	/**
	 * @brief Get the localization object
	 * 
	 * If the localization object is not defined, it will be initialized using the configuration parameters (see 
	 * Axiom::configuration).
	 * 
	 * @return axLocale
	 */
	public static function locale () {
		if (isset(self::$_locale))
			return self::$_locale;
						
		$conf = self::configuration();
		if (!$conf->localization->getValue())
			return false;
		
		$lang_file    = (string)$conf->localization->lang->file;
		$lang         = (string)$conf->localization->lang;
		$default_lang = (string)$conf->localization->lang->default;
		
		$cache_dir = self::$cache ? AXIOM_APP_PATH . '/ressource/cache' : false;
		
		if (strpos($lang_file, '//') === 0)
			$lang_file = str_replace("//", AXIOM_APP_PATH . '/', $lang_file);
		
		return self::$_locale = new axLocale($lang_file, $lang, $default_lang, $cache_dir);
	}
	
	/**
	 * @brief Get the database connection object
	 * 
	 * If the database connection object is not defined, it will be initialized using the configuration parameter (see 
	 * Axiom::configuration).
	 * When calling Axiom::database for the first time, you may pass an array as only parameter, this array
	 * will be used as the $driver_options parameters of the PDO constructor
	 * (see @link{http://www.php.net/manual/en/pdo.construct.php}).
	 * 
	 * @param array $driver_options @optional @default{array()} The driver options
	 * @throws PDOException If the database connection object construction fails
	 * @return axDatabase
	 */
	public static function database () {
		if (isset(self::$_database))
			return self::$_database;
			
		$conf = self::configuration();
		if (!$conf->database->getValue())
			return false;
			
		$dsn = "{$conf->database->type}:dbname={$conf->database->database};host={$conf->database->host}";
		$user = (string)$conf->database->user;
		$pass = (string)$conf->database->pass;
		
		list($driver_options) = func_get_args() + array(array());
		
		return self::$_database = new axDatabase($dsn, $user, $pass, $driver_options);
	}
	
	/**
	 * @brief Get the session object
	 * 
	 * If the session object is not defined, it will be initialized using the configuration parameters (see 
	 * Axiom::configuration).
	 * 
	 * @return axSession
	 */
	public static function session () {
		if (isset(self::$_session))
			return self::$_session;
			
		$conf = self::configuration();
		if (!$conf->session->getValue())
			return false;
			
		return self::$_session = new axSession((string)$conf->session->name);
	}
	
	/**
	 * @brief Get the log object
	 * 
	 * If the log object is not defined, it will be initialized using the configuration parameters (see 
	 * Axiom::configuration).
	 * 
	 * @return axLog
	 */
	public static function log () {
		if (isset(self::$_log))
			return self::$_log;
			
		$conf = self::configuration();
		if (!$conf->log->getValue())
			return false;
			
		$opts = array(
			'ignore_repeated_messages' => (string)$conf->log->ignore_repeated_messages,
			'log_errors'               => (string)$conf->log->errors,
			'log_exceptions'           => (string)$conf->log->exceptions,
		);
		
		return self::$_log = new axLog($opts);
	}
	
	/**
	 * @brief Get the captcha object
	 * 
	 * If the captcha object is not defined, it will be initialized using the configuration parameters (see 
	 * Axiom::configuration).
	 * 
	 * @return axCaptcha
	 */
	public static function captcha () {
		if (isset(self::$_captcha))
			return self::$_captcha;
			
		$conf = self::configuration();
		if (!$conf->captcha->getValue())
			return false;
			
		$opts = array(
			'dictionnaries_path' => (string)$conf->captcha->dictionnary->path,
			'dictionnary' 	     => (string)$conf->captcha->dictionnary,
			'dictionnary_type'   => (string)$conf->captcha->dictionnary->type,
		);
		
		return self::$_captcha = new axCaptcha($opt);
	}
	
	/**
	 * @brief Get the module manager object
	 * 
	 * If the module manager object is not defined, it will be initialized using the configuration parameters (see 
	 * Axiom::configuration).
	 * 
	 * @return axModuleManager
	 */
	public static function module () {
	    if (isset(self::$_module))
	        return self::$_module;
	        
        if (!self::configuration()->module->getValue())
            return false;
	        
        $opts = array(
            'check_dependencies' => true,
            'cache_dir'          => self::$cache ? AXIOM_APP_PATH . '/ressource/cache' : false,
        );
        $path = AXIOM_APP_PATH . '/module';
        
        return self::$_module = new axModuleManager($path, self::VERSION, $opts);
	}
	
	/**
	 * @brief Get the view manager
	 * 
	 * If the view manager object is not defined, it will be initialized using the configuration parameters (see 
	 * Axiom::configuration).
	 * 
	 * @return axViewManager
	 */
	public static function view () {
	    if (isset(self::$_view))
	        return self::$_view;
	        
        $conf      = self::configuration()->view;
        $layout    = (string)$conf->layout->getValue();
        $vars      = $conf->layout->vars->getValue() ? $conf->layout->vars->getValue() : array();
        $format    = (string)$conf->format->default;
        $view_path = AXIOM_APP_PATH . '/view';
        
        return self::$_view = new axViewManager($layout, $view_path, $format, $vars);
	}
}

/**
 * @brief Core Module
 * 
 * The Core module contains the core framework. Axiom framework cannot work properly without at least those libraries.
 * 
 * @defgroup Core
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */