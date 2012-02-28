<?php
/**
 * @brief Route class file
 * @file axRouter.class.php
 */

/**
 * @brief Route Class
 *
 * Class used to manage and to recognize routes. A route is identified by matching a pattern against the $url parameter.
 * This pattern is provided to the Route insance by using a template, such as /{:controller}/{:action}. 
 * See axRouter::connect() for more details.
 *
 * @class axRoute
 * @author Delespierre
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axRoute {
    
    /**
     * @brief Template string
     * @property string $_template
     */
    protected $_template;
    
    /**
     * @brief PCRE pattern compiled from template
     * @internal
     * @property string $_pattern
     */
    protected $_pattern;
    
    /**
     * @brief PCRE pattern keys
     * @internal
     * @property array $_keys
     */
    protected $_keys;

    /**
     * @brief Route parameters
     * @property array $_params
     */
    protected $_params;

    /**
     * @brief Route options
     * @property array $_options;
     */
    protected $_options;
    
    /**
     * @brief Constructor
     * @param string $template
     * @param array $params @optional @default{array()} Parameters to populate the Route instance with
     * @param array $options @optional @default{array()} Arbitraty options array wich can be retrieved with 
     * Route::getOptions()
     */
    public function __construct ($template, array $params = array(), array $options = array()) {
        $this->_template = $template;
        $this->_params   = $params;
        $this->_options  = $options;
        $this->_keys     = array();
    }
    
    /**
     * @brief Tells if the Route instance match the given url.
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
     * @brief Compiles the pattern into a PCRE expression.
     * @internal
     * @todo Add insensitive option
     * @param string $template
     * @return void
     */
    protected function _compileTemplate ($template) {
        $token = strtok($template, '/');
        $pattern_pieces = array();
        $current_key = 1;
        do {
            // Here be dragons
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
     * @brief Get the Route parameters
     * @return array
     */
    public function getParams () {
        return $this->_params;
    }
    
    /**
     * @brief Get the Route options
     * @return array
     */
    public function getOptions () {
        return $this->_options;
    }
    
    /**
     * @brief Get the Route template (useful for debugging)
     * @return string
     */
    public function getTemplate () {
        return $this->_template;
    }
    
    /**
     * @brief Get the Route PCRE pattern (useful for debugging)
     * @return string
     */
    public function getPattern () {
        return $this->_pattern;
    }
}