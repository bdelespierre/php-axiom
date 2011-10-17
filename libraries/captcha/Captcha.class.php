<?php

class Captcha {
    
    protected static $_config;
    
    protected static $_dictionnary_struct;
    
    protected static $_session;
    
    public static function setConfig (array $config = array()) {
        $default = array(
            'dictionnaries_path' => APPLICATION_PATH . '/ressource/captcha',
            'dictionnary'        => 'static.dictionary.ini',
            'dictionnary_type'   => 'static',
        );
        
        self::$_config = $config + $default;
    }
    
    protected static function _parseDictionnary ($path) {
        if (!self::$_dictionnary_struct = parse_ini_file($path, true))
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
            $answer = array_map('strtolower', array_map('trim', explode(',', self::$_dictionnary_struct[$lang][$question])));
        }
        elseif (self::$_config['dictionnary_type'] == 'dynamic') {
            if (!$alpha = callback(self::$_dictionnary_struct[$lang][$question]))
                throw new RuntimeException("Cannot understand alpha function definition");
                
            $argc = substr_count($question, '%d');
            $argv = array();
            for ($i=0; $i<$argc; $i++)
                $argv[] = rand(0,9);

            $answer   = array(call_user_func_array($alpha, $argv));
            array_unshift($argv, $question);
            $question = call_user_func_array('sprintf', $argv);
        }
        else {
            throw new RuntimeException("Unrecognized dictionnary type " . self::$_config['dictionnary_type']);
        }
            
        self::$_session->captcha_ans = $answer;
        return $question;
    }
    
    public static function verify ($answer) {
        if (empty(self::$_session)) {
            self::$_session = new Session;
            self::$_session->start();
        }
        
        return in_array(strtolower(trim($answer)), self::$_session->captcha_ans);
    }
}