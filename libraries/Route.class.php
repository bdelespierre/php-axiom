<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

class Route {
    
    protected $_template;
    
    protected $_pattern;
    
    protected $_keys;

    protected $_params;

    protected $_options;
    
    public function __construct ($template, array $params = array(), array $options = array()) {
        $this->_template = $template;
        $this->_params = $params;
        $this->_options = $options;
        $this->_keys = array();
    }
    
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
    
    public function getParams () {
        return $this->_params;
    }
    
    public function getOptions () {
        return $this->_options;
    }
    
    public function getTemplate () {
        return $this->_template;
    }
    
    public function getPattern () {
        return $this->_pattern;
    }
}