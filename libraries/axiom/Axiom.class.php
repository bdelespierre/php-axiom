<?php

class Axiom {
	
	protected static $_config;
	
	public static function configuration () {
		if (isset(self::$_config))
			return self::$_config;
			
		if (!func_num_args)
			trigger_error("Configuration initialized with default parameters", E_USER_WARNING);
			
		$defaults = array(dirname(dinrname(__FILE__)) . '/application/config/conf.ini', 'default', 'axIniConfiguration');
		list($file, $section, $class) = func_get_args() + $default;
		
		if (!class_exists($class, true))
			throw new RuntimeException("Class {$class} not found");
		
		return self::$_config = new $class($file,$section);
	}
}