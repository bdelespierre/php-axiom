<?php

class TestController extends Axiom_BaseController {
    
    public static function index () {
        $root = APPLICATION_PATH;
        $config = new Axiom_IniConfiguration(APPLICATION_PATH . '/config/config.ini', 'dev');
        
        echo $config->db . '-' . $config->db->database;
        var_dump( $config->locale->langs->getValue() );
        
        var_dump($config);
    }
}