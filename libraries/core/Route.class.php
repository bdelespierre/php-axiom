<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Route Class
 *
 * Class intented to manage and to
 * recognize routes.
 *
 * A route is identified by matching
 * a pattern against the $url parameter.
 * This pattern is provided to the
 * Route insance by using a template,
 * such as /{:controller}/{:action}.
 * See Router::connect for more details.
 *
 * @author Delespierre
 * @version $rev$
 * @subpackage Route
 */
class Route {
    
    /**
     * Template string
     * @internal
     * @var string
     */
    protected $_template;
    
    /**
     * PCRE pattern compiled from template
     * @internal
     * @var string
     */
    protected $_pattern;
    
    /**
     * PCRE pattern keys
     * @internal
     * @var array
     */
    protected $_keys;

    /**
     * Route parameters
     * @internal
     * @var array
     */
    protected $_params;

    /**
     * Route options
     * @internal
     * @var array
     */
    protected $_options;
    
    /**
     * Default constructor
     * @param string $template
     * @param array $params optional parameters to populate the Route instance with
     * @param array $options arbitraty options array wich can be retrieved with Route::getOptions
     */
    public function __construct ($template, array $params = array(), array $options = array()) {
        $this->_template = $template;
        $this->_params   = $params;
        $this->_options  = $options;
        $this->_keys     = array();
    }
    
    /**
     * Tells if the Route instance
     * match the given url.
     * @param string $url
     * @return boolean
     */
    public function match ($url) {
        $url = '/' . trim($url, '/');
        
        if (empty($this->_pattern))
            $this->_compileTemplate($this->_template);
        	
        if (preg_match($this->_pattern, $url, $matches)) {
            unset($matches[0]);
            if (!empty($this->_keys) && !empty($matches)) {
                $this->_params = array_merge($this->_params, array_combine($this->_keys, $matches));
            }
            return true;
        }
        return false;
    }
    
    /**
     * Compiles the pattern into a PCRE expression.
     * @internal
     * @param string $template
     * @return void
     */
    protected function _compileTemplate ($template) {
        $token = strtok($template, '/');
        $pattern_pieces = array();
        $current_key = 1;
        do {
            if (preg_match('~{:(.+)}~', $token, $matches)) {
                if ($matches[1] === 'lang') {
                    $key = 'lang';
                    $subpattern = "([a-z]{2})";
                }
                elseif ($matches[1] === 'id') {
                    $key = 'id';
                    $subpattern = "([0-9]+)";
                }
                elseif ($matches[1] === 'controller' || $matches[1] === 'action') {
                    $key = $matches[1];
                    $subpattern = "(\w{3,})";
                }
                elseif (($offset = strpos($matches[1], ':')) !== false) {
                    list($key, $subpattern) = explode(':', $matches[1]);
                    $subpattern = "($subpattern)";
                }
                else {
                    $key = $matches[1];
                    $subpattern = "([^/]+)";
                }
                $pattern_pieces[] = $subpattern;
                $this->_keys[$current_key++] = $key;
            }
            else {
                $pattern_pieces[] = $token;
            }
        } while ($token = strtok('/'));
        $this->_pattern = '~^/' . implode('/', $pattern_pieces) . '$~';
    }
    
    /**
     * Get the Route parameters
     * @return array
     */
    public function getParams () {
        return $this->_params;
    }
    
    /**
     * Get the Route options
     * @return array
     */
    public function getOptions () {
        return $this->_options;
    }
    
    /**
     * Get the Route template
     * (useful for debugging)
     * @return string
     */
    public function getTemplate () {
        return $this->_template;
    }
    
    /**
     * Get teh Route PCRE pattern
     * (useful for debugging)
     * @return string
     */
    public function getPattern () {
        return $this->_pattern;
    }
}