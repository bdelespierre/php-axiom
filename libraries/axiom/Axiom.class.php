<?php

final class Axiom {
	
	private static $_config;
	
	private static $_library;
	
	private static $_database;
	
	public static function configuration () {
		if (isset(self::$_config))
			return self::$_config;
			
		if (!func_num_args)
			trigger_error("Configuration initialized with default parameters", E_USER_WARNING);
			
		$defaults = array(dirname(dinrname(__FILE__)) . '/application/config/conf.ini', 'default', 'axIniConfiguration');
		list($file, $section, $class) = func_get_args() + $default;
		
		if (!class_exists($class, true))
			throw new RuntimeException("Class {$class} not found");
		
		return self::$_config = new $class($file,$section,AXIOM_APP_PATH . '/ressource/cache');
	}
	
	public static function library () {
		if (isset(self::$_library))
			return self::$_library;
		
		self::$_library = new axLibrary(AXIOM_APP_PATH . '/ressource/cache');
		self::$_library->register();
		return self::$_library;
	}
	
	public function database () {
		if (isset(self::$_database))
			return self::$_database;
			
		$conf = self::configuration();
		if (!$conf->database)
			return false;
			
		$dsn = "{$conf->database->type}:dbname={$conf->database->database};host={$conf->database->host}";
		$user = (string)$conf->database->user;
		$pass = (string)$conf->database->pass;
		
		list($driver_options) = func_get_args() + array(array());
		
		self::$_database = new PDO($dsn, $user, $pass, $driver_options);
	}
	
	
}