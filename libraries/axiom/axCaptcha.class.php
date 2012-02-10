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
     * @var array
     */
    protected $_options;
    
    /**
     * Dictionnary cache
     * @internal
     * @var array
     */
    protected $_dictionnary_struct;
    
    /**
     * axSession handle
     * @internal
     * @var axSession
     */
    protected $_session;
    
    /**
     * Default constructor
     * @param array $config
     * @return void
     */
    public function __construct (array $options = array()) {
        $default = array(
            'dictionnaries_path' => null,
            'dictionnary'        => 'static.dictionary.ini',
            'dictionnary_type'   => 'static',
        );
        
        $this->_config = $config + $default;
    }
    
    /**
     * Parses the dictionnary pointed by $path
     * and saves the parse results in class cache
     * @internal
     * @param string $path
     * @throws RuntimeException
     * @return void
     */
    protected function _parseDictionnary ($path) {
        if (!$this->_dictionnary_struct = parse_ini_file($path, true))
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
     * @param string $lang
     * @throws axMissingFileException
     * @throws RuntimeException
     * @return string
     */
    public function generate ($lang) {
        if (empty($this->_dictionnary_struct)) {
            if (!is_file($path = realpath($this->_config['dictionnaries_path']) . '/' . $this->_config['dictionnary']))
                throw new axMissingFileException($path);
                
            $this->_parseDictionnary($path);
        }
        
        if (empty($this->_session)) {
            $this->_session = new axSession;
            $this->_session->start();
        }
        
        if (empty($this->_dictionnary_struct[$lang]))
            throw new RuntimeException("Dictionnary does not provide any data for {$lang} language");
            
        $question = array_rand($this->_dictionnary_struct[$lang]);
        
        if ($this->_config['dictionnary_type'] == 'static') {
            $answer = array_map('strtolower', array_map('trim', explode(',', $this->_dictionnary_struct[$lang][$question])));
        }
        elseif ($this->_config['dictionnary_type'] == 'dynamic') {
            if (!$alpha = callback($this->_dictionnary_struct[$lang][$question]))
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
            throw new RuntimeException("Unrecognized dictionnary type " . $this->_config['dictionnary_type']);
        }
            
        $this->_session->captcha_ans = $answer;
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
    public function verify ($answer) {
        if (empty($this->_session)) {
            $this->_session = new axSession;
            $this->_session->start();
        }
        
        return in_array(strtolower(trim($answer)), $this->_session->captcha_ans);
    }
}

if (!function_exists('callback')) {
	function callback ($fct) {
	    if (!preg_match('~(function)?\s*\((?P<args>[^\)]*)\)\s*\{(?P<code>.*)\}~', $fct, $matches))
	        return false;
	
	    $args = $matches['args'];
	    $code = $matches['code'];
	    return create_function($args, $code);
	}
}