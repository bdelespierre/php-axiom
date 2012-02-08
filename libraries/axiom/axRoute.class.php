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
 * See axRouter::connect for more details.
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage core
 */
class axRoute {
    
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
        $url = '/' . trim($url, '/') . '/';
        
        if (empty($this->_pattern))
            $this->_compileTemplate($this->_template);
        	
        if (preg_match($this->_pattern, $url, $matches)) {
            unset($matches[0]);
            if (!empty($this->_keys) && !empty($matches)) {
                $this->_params = array_merge($this->_params, array_intersect_key($matches, $this->_keys));
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
            if (preg_match('~\{:(?P<key>\w+)(:(?P<tpl>[^\}:]*))?(:(?P<opt>[^\}:]*))?\}~', $token, $matches)) {
                if ($matches['key'] == 'lang' && empty($matches['tpl'])) {
                    $matches['tpl'] = '[a-z]{2}';
                }
                if ($matches['key'] == 'id' && empty($matches['tpl'])) {
                    $matches['tpl'] = '\d+';
                }
                if (($matches['key'] == 'controller' || $matches['key'] == 'action') && empty($matches['tpl'])) {
                    $matches['tpl'] = "\w{3,}";
                }
                if (empty($matches['tpl'])) {
                    $matches['tpl'] = "[^/]+";
                }
                
                $subpattern = "(?P<{$matches['key']}>{$matches['tpl']})/";
                
                if (isset($matches['opt']) && strpos($matches['opt'], '?') !== false) {
                    $subpattern = "({$subpattern})?";
                }
                
                $this->_keys[$matches['key']] = $matches['key'];
                $pattern_pieces[] = $subpattern;
            }
            else {
                $pattern_pieces[] = $token . '/';
            }
        } while ($token = strtok('/'));
        $this->_pattern = '~^/' . implode($pattern_pieces) . '$~';
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