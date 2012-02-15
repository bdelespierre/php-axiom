<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Axiom Class
 * 
 * TODO Long description here
 * 
 * @final
 * @static
 * @author Delespierre
 * @package libaxiom
 * @subpackage core
 * @since 1.2.0
 */
final class Axiom {
    
    /**
     * Axiom Framework Version
     * @var string
     */
    const VERSION = '1.2.0';

    // TODO add cache public property here
    
    /**
     * Configuration object
     * @internal
     * @staticvar
     * @var axConfiguration
     */
	private static $_config;
	
	/**
	 * Library object
	 * @internal
	 * @staticvar
	 * @var axLibrary
	 */
	private static $_library;
	
	/**
	 * Localization object
	 * @internal
	 * @staticvar
	 * @var axLocale
	 */
	private static $_locale;

	/**
	 * Database connection object
	 * @internal
	 * @staticvar
	 * @var PDO
	 */
	private static $_database;
	
	/**
	 * Session object
	 * @internal
	 * @staticvar
	 * @var axSession
	 */
	private static $_session;
	
	/**
	 * Log object
	 * @internal
	 * @staticvar
	 * @var axLog
	 */
	private static $_log;
	
	/**
	 * Captcha object
	 * @internal
	 * @staticvar
	 * @var axCaptcha
	 */
	private static $_captcha;
	
	/**
	 * Module Manager object
	 * @internal
	 * @staticvar
	 * @var unknown_type
	 */
	private static $_module;
	
	/**
	 * Get the configuration object
	 * 
	 * If the configuration object is not defined it will be initialized according to the method parameters.
	 * If no parameter is provided for the first call (implicit initialization) then the default parameters will be 
	 * used.
	 * Will throw a RuntimeException if the configuration class cannot be found (a lookup in the library will be
	 * performed).
	 * 
	 * @static
	 * @param string $file [optional] [default '/application/config/config.ini'] The configuration file
	 * @param string $section [optional] [default 'default'] The configuration section to be used
	 * @param string $class [optional] [default 'axIniConfiguration'] The configuration class to be used
	 * @throws RuntimeException
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
		
		return self::$_config = new $class($file, $section, AXIOM_APP_PATH . '/ressource/cache');
	}
	
	/**
	 * Get the library object
	 * 
	 * If the library object is not defined it will be initialized. No parameter is required to initialize the library 
	 * object.
	 * 
	 * To add new libraries, call the 'add' method as follow.
	 * * Axiom::library()->add('myLib');
	 * A lookup in the '/library' and '/application/library' will be performed. If no such directory is found, the
	 * library object will throw an exception.
	 * 
	 * Calling this method for the first time will register the library object as default Autoloader for PHP.
	 * 
	 * @static
	 * @return axLibrary
	 */
	public static function library () {
		if (isset(self::$_library))
			return self::$_library;
		
		if (func_num_args())
		    $cache_file = func_get_arg(0);
	    else
	        $cache_file = AXIOM_APP_PATH . '/ressource/cache';
			
		self::$_library = new axLibrary($cache_file);
		self::$_library->register();
		return self::$_library;
	}
	
	/**
	 * Get the localization object
	 * 
	 * If the localization object is not defined, it will be initialized using the configuration parameters (see
	 * Axiom::configuration method).
	 * 
	 * @static
	 * @return axLocale
	 */
	public static function locale () {
		if (isset(self::$_locale))
			return self::$_locale;
						
		$conf = self::configuration();
		if (!$conf->localization->getValue())
			return false;
		
		$lang_file    = $conf->localization->lang->file;
		$lang         = $conf->localization->lang;
		$default_lang = $conf->localization->lang->default;
		
		if (strpos($lang_file, '//') === 0)
			$lang_file = str_replace("//", AXIOM_APP_PATH . '/', $lang_file);
		
		return self::$_locale = new axLocale($lang_file, $lang, $default_lang, AXIOM_APP_PATH . '/ressource/cache');
	}
	
	/**
	 * Get the database connection object
	 * 
	 * If the database connection object is not defined, it will be initialized using the configuration parameter (see
	 * Axiom::configuration method).
	 * Note: when calling Axiom::database for the first time, you may pass an array as only parameter, this array
	 * will be used as the $options parameters of the PDO constructor (see PDO::__construct).
	 * E.G.
	 * * Axiom::database(array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ORACLE_NULL => true));
	 * 
	 * If the database connection object construction fails, a PDOException is emitted (see PDO::__construct).
	 * 
	 * @static
	 * @return PDO
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
		
		return self::$_database = new PDO($dsn, $user, $pass, $driver_options);
	}
	
	/**
	 * Get the session object
	 * 
	 * If the session object is not defined, it will be initialized using the configuration parameters (see 
	 * Axiom::configuration method).
	 * 
	 * @static
	 * @return axSession
	 */
	public static function session () {
		if (isset(self::$_session))
			return self::$_session;
			
		$conf = self::configuration();
		if (!$conf->session->getValue())
			return false;
			
		return self::$_session = new axSession($conf->session->name);
	}
	
	/**
	 * Get the log object
	 * 
	 * If the log object is not defined, it will be initialized using the configuration parameters (see 
	 * Axiom::configuration method).
	 * To add a logger in the log chain, simply call the addLogger method on the log object.
	 * E.G.
	 * * Axiom::log()->addLogger(new AxTextLogger(AXIOM_APP_PATH . '/ressource/log/app.log'));
	 * 
	 * @static
	 * @return axLog
	 */
	public static function log () {
		if (isset(self::$_log))
			return self::$_log;
			
		$conf = self::configuration();
		if (!$conf->log->getValue())
			return false;
			
		$opts = array(
			'ignore_repeated_messages' => $conf->log->ignore_repeated_messages,
			'log_errors'               => $conf->log->errors,
			'log_exceptions'           => $conf->log->exceptions,
		);
		
		return self::$_log = new axLog($opts);
	}
	
	/**
	 * Get the captcha object
	 * 
	 * If the captcha object is not defined, it will be initialized using the configuration parameters (see 
	 * Axiom::configuration method).
	 * 
	 * @static
	 * @return axCaptcha
	 */
	public static function captcha () {
		if (isset(self::$_captcha))
			return self::$_captcha;
			
		$conf = self::configuration();
		if (!$conf->captcha->getValue())
			return false;
			
		$opts = array(
			'dictionnaries_path' => $conf->captcha->dictionnary->path,
			'dictionnary' 	     => $conf->captcha->dictionnary,
			'dictionnary_type'   => $conf->captcha->dictionnary->type,
		);
		
		return self::$_captcha = new axCaptcha($opt);
	}
	
	/**
	 * Get the module manager object
	 * 
	 * If the module manager object is not defined, it will be initialized using the configuration parameters (see 
	 * Axiom::configuration method).
	 * 
	 * To check for module existency, use the exists method on the module manager object.
	 * E.G.
	 * * Axiom::module()->exists('module');
	 * 
	 * To load a module, use the load method.
	 * E.G.
	 * * Axiom::module()->load('module');
	 * 
	 * @static
	 * @return axModuleManager
	 */
	public static function module () {
	    if (isset(self::$_module))
	        return self::$_module;
	        
        if (!self::configuration()->module->getValue())
            return false;
	        
        $opts = array(
            'check_dependencies' => true,
            'cache_dir' => AXIOM_APP_PATH . '/ressource/cache',
        );
        $path = AXIOM_APP_PATH . '/module';
        
        return self::$_module = new axModuleManager($path, self::VERSION, $opts);
	}
}