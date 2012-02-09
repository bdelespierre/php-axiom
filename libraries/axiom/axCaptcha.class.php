<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Captcha Class
 *
 * Provides text captcha management.
 * Text captcha are simple question a
 * bot could not possibly answer, thus
 * protects a form from non-human users.
 *
 * Question are defined in dictionnaries
 * which are defined in ini files as described
 * in axCaptcha::setConfig method.
 *
 * Question can be static or dynamic:
 * - static question accepts a set of possible
 *   results as answers like
 *   Q: What is the capital of China
 *   A: pekin or beijing are both valid answers
 * - Dynamic question accepts only one possible
 *   answer, the question parameters are
 *   randomly generated like
 *   Q: What is 1 + 2 ?
 *   A: 3
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage captcha
 */
class axCaptcha {
    
    /**
     * Internal configuration
     * @internal
     * @static
     * @var array
     */
    protected static $_config;
    
    /**
     * Dictionnary cache
     * @internal
     * @static
     * @var array
     */
    protected static $_dictionnary_struct;
    
    /**
     * axSession handle
     * @internal
     * @static
     * @var axSession
     */
    protected static $_session;
    
    /**
     * Set Config
     * @static
     * @param array $config
     * @return void
     */
    public static function setConfig (array $config = array()) {
        $default = array(
            'dictionnaries_path' => AXIOM_APP_PATH . '/ressource/captcha',
            'dictionnary'        => 'static.dictionary.ini',
            'dictionnary_type'   => 'static',
        );
        
        self::$_config = $config + $default;
    }
    
    /**
     * Parses the dictionnary pointed by $path
     * and saves the parse results in class cache
     * @internal
     * @static
     * @param string $path
     * @throws RuntimeException
     * @return void
     */
    protected static function _parseDictionnary ($path) {
        if (!self::$_dictionnary_struct = parse_ini_file($path, true))
            throw new RuntimeException("Cannot parse {$path}");
    }
    
    /**
     * Generates a captcha for the given lang.
     *
     * Returns the question after setting the
     * answer in session for next use.
     *
     * Will throw a RuntimeException if the given
     * lang is not found in dictionnary.
     *
     * @static
     * @param string $lang
     * @throws axMissingFileException
     * @throws RuntimeException
     * @return string
     */
    public static function generate ($lang) {
        if (empty(self::$_dictionnary_struct)) {
            if (!is_file($path = realpath(self::$_config['dictionnaries_path']) . '/' . self::$_config['dictionnary']))
                throw new axMissingFileException($path);
                
            self::_parseDictionnary($path);
        }
        
        if (empty(self::$_session)) {
            self::$_session = new axSession;
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
    
    /**
     * Verify the answer agains the last
     * generated question.
     *
     * Return true if the provided answer
     * match any of possible question
     * results.
     *
     * @see axCaptcha::generate
     * @param string $answer
     * @return boolean
     */
    public static function verify ($answer) {
        if (empty(self::$_session)) {
            self::$_session = new axSession;
            self::$_session->start();
        }
        
        return in_array(strtolower(trim($answer)), self::$_session->captcha_ans);
    }
}