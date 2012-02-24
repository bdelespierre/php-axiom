<?php
/**
 * @brief Captcha generator class file
 * @file axCaptcha.class.php
 */
 
/**
 * @brief Textual captcha generator
 *
 * Provides text captcha management. Text captcha are simple question a bot could not possibly answer, thus protects a 
 * form from non-human users. Question are defined in dictionnaries which are ini files passed to the constructor. 
 * 
 * Question can be static or dynamic:
 * @li static question accepts a set of possible results as answers like@n
 *   Q: What is the capital of China@n 
 *   A: pekin or beijing are both valid answers
 * @liDynamic question accepts only one possible answer, the question parameters are randomly generated like@n
 *   Q: What is 1 + 2 ?@n
 *   A: 3
 *   
 * @todo Describe the configuration parameter over the controller
 *   
 * @note The captcha library stores the answser information over the user's session, if no session is started when
 * calling axCaptcha::generate(), it will be started.
 *
 * @class axCatpcha
 * @author Delespierre
 * @ingroup Captcha
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @copyright http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axCaptcha {
    
    /**
     * @brief Internal configuration
     * @property array $_options
     */
    protected $_options;
    
    /**
     * @brief Dictionnary cache
     * @property array $_dictionary_struct
     */
    protected $_dictionnary_struct;
    
    /**
     * @brief axSession handle
     * @property axSession $_session
     */
    protected $_session;
    
    /**
     * @brief Constructor
     * @param array $config @optional @default{array()} The configuration
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
     * @brief Parses the dictionnary pointed by @c $path and saves the parse results in class cache
     * 
     * The dictionnary file will be parsed using PHP parse_ini_file.
     * 
     * @internal
     * @param string $path The dictionnary path
     * @throws RuntimeException If the file cannot be parsed
     * @return void
     */
    protected function _parseDictionnary ($path) {
        if (!$this->_dictionnary_struct = parse_ini_file($path, true))
            throw new RuntimeException("Cannot parse {$path}");
    }
    
    /**
     * @brief Generates a captcha for the given lang
     *
     * Returns the question after storing the answer in session for next use. 
     *
     * @param string $lang The question language
     * @throws axMissingFileException If the dictionnary file doesn't exists
     * @throws RuntimeException If the given langugage is not found in the dictionnary
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
     * @brief Verify the answer agains the last generated question.
     *
     * Return true if the provided answer match any of possible question results.
     *
     * @see axCaptcha::generate
     * @param string $answer The input field provided by the form
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
    /**
     * @brief callback helper
     * 
     * This method allow you to create lambda function in a convenient style:
     * @code
     * $alpha = callback('function ($a,$b) { return $a * $b; }');
     * @endcode
     * Will return the newly generated function's name or false in case of error.
     * 
     * @fn string callback (string $fct)
     * @param string $fct The function definition
     * @return string
     */
	function callback ($fct) {
	    if (!preg_match('~(function)?\s*\((?P<args>[^\)]*)\)\s*\{(?P<code>.*)\}~', $fct, $matches))
	        return false;
	
	    $args = $matches['args'];
	    $code = $matches['code'];
	    return create_function($args, $code);
	}
}