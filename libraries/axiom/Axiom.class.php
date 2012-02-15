<?php

final class Axiom {
    
    const VERSION = '1.2.0';
	
	private static $_config;
	
	private static $_library;
	
	private static $_locale;

	private static $_database;
	
	private static $_session;
	
	private static $_log;
	
	private static $_captcha;
	
	private static $_module;
	
	public static function configuration () {
		if (isset(self::$_config))
			return self::$_config;
			
		if (!func_num_args())
			trigger_error("Configuration initialized with default parameters", E_USER_WARNING);
			
		$defaults = array(AXIOM_APP_PATH . '/config/config.ini', 'default', 'axIniConfiguration');
		list($file, $section, $class) = func_get_args() + $defaults;
		
		if (!class_exists($class, true))
			throw new RuntimeException("Class {$class} not found");
		
		return self::$_config = new $class($file, $section, AXIOM_APP_PATH . '/ressource/cache');
	}
	
	public static function library () {
		if (isset(self::$_library))
			return self::$_library;
		
		self::$_library = new axLibrary(AXIOM_APP_PATH . '/ressource/cache');
		self::$_library->register();
		return self::$_library;
	}
	
	public static function locale () {
		if (isset(self::$_locale))
			return self::$_locale;
						
		$conf = self::configuration();
		if (!$conf->localization->getValue())
			return false;
		
		$lang_file = $conf->localization->lang->file;
		$lang = $conf->localization->lang;
		$default_lang = $conf->localization->lang->default;
		
		if (strpos($lang_file, '//') === 0)
			$lang_file = str_replace("//", AXIOM_APP_PATH . '/', $lang_file);
		
		return self::$_locale = new axLocale($lang_file, $lang, $default_lang, AXIOM_APP_PATH . '/ressource/cache');
	}
	
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
	
	public static function session () {
		if (isset(self::$_session))
			return self::$_session;
			
		$conf = self::configuration();
		if (!$conf->session->getValue())
			return false;
			
		return self::$_session = new axSession($conf->session->name);
	}
	
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