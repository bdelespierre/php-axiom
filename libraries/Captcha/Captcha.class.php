<?php

class Captcha {
    
    protected static $_config;
    
    protected static $_dictionnary_struct;
    
    protected static $_session;
    
    public static function setConfig (array $config = array()) {
        $default = array(
            'dictionnaries_path' => APPLICATION_PATH . '/ressource/captcha',
            'dictionnary'        => 'static.dictionnary.ini',
            'dictionnary_type'   => 'static',
        );
        
        self::$_config = $config + $default;
    }
    
    protected static function _parseDictionnary ($path) {
        if (!self::$_dictionnary_struct = parse_ini_file($path))
            throw new RuntimeException("Cannot parse {$path}");
    }
    
    public static function generate ($lang) {
        if (empty(self::$_dictionnary_struct)) {
            if (!is_file($path = realpath(self::$_config['dictionnaries_path']) . '/' . self::$_config['dictionnary']))
                throw new MissingFileException($path);
                
            self::_parseDictionnary($path);
        }
        
        if (empty(self::$_session)) {
            self::$_session = new Session;
            self::$_session->start();
        }
        
        if (empty(self::$_dictionnary_struct[$lang]))
            throw new RuntimeException("Dictionnary does not provide any data for {$lang} language");
            
        $question = array_rand(self::$_dictionnary_struct[$lang]);
        
        if (self::$_config['dictionnary_type'] == 'static') {
            $answer = explode(',', self::$_dictionnary_struct[$lang][$question]);
        }
        elseif (self::$_config['dictionnary_type'] == 'dynamic') {
            $fct = self::$_dictionnary_struct[$lang][$question];
            if (!preg_match('~($\w+,?)*\|.*;~', $fct))
                throw new RuntimeException("Cannot understand alpha function defintion");
                
            list($args, $code) = explode('|', $fct);
            if (!$alpha = create_function($args, $code))
                throw new RuntimeException("Invalid alpha function definition");
                
            $argc = count(explode(',', $args));
            $argv = array();
            for ($i=0; $i<$argc; $i++) {
                $argv[$i] = rand(0,9);
            }
            
            $question = call_user_func_array('sprintf', $argv);
            $answer = call_user_func_array($alpha, $argv);
        }
        else {
            throw new RuntimeException("Unrecognized dictionnary type " . self::$_config['dictionnary_type']);
        }
            
        self::$_session->captcha_ans = $answer;
        return $question;
    }
}