<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Request Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage core
 */
class axRequest {
    
    protected $_headers;
    protected $_post;
    protected $_get;
    protected $_request;
    protected $_cookies;
    protected $_files;
    protected $_filters;
    protected $_browscap;
    
    public function __construct ($cache_dir) {
        $this->_headers  = getallheaders();
        $this->_post     = $_POST;
        $this->_get      = $_GET;
        $this->_request  = $_REQUEST;
        $this->_cookies  = $_COOKIE;
        $this->_files    = $_FILES;
        $this->_filters  = array();
        $this->_browscap = ($cache_dir && class_exists('Browscap', true)) ? new Browscap($cache_dir) : null;
    }
    
    public function reset () {
        $this->_headers  = getallheaders();
        $this->_post     = $_POST;
        $this->_get      = $_GET;
        $this->_request  = $_REQUEST;
        $this->_cookies  = $_COOKIE;
        $this->_filters  = array();
    }
    
    public function getHeaders () {
        return $this->_headers;
    }
    
    public function headerExists ($header) {
        return isset($this->_headers[$header]);
    }
    
    public function getHeader ($header) {
        return $this->headerExists($header) ? $this->_headers[$header] : null;
    }
    
    public function getMethod () {
        return $this->_server['REQUEST_METHOD'];
    }
    
    public function getEnv ($varname) {
        return getenv($varname);
    }
    
    public function getServer ($varname = null) {
        if ($varname)
            return isset($_SERVER[$varname]) ? $_SERVER[$varname] : null;
        
        return $_SERVER;
    }
    
    public function getCookies () {
        return $this->_cookies;
    }
    
    public function cookieExists ($name) {
        return array_key_exists($name, $this->_cookies);
    }
    
    public function getCookie ($name) {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name] : null;
    }
    
    public function setFilter (array $filter, $type = null) {
        if ($type === null) {
            $this->_filters['default'] = array(
                'filter' => $filter,
                'flag'   => true,
            );
            return $this;
        }
        
        if (!$type = self::_determineType($type))
            throw new InvalidArgumentException("Invalid type");
            
        $this->_filters[$type] = array(
            'filter' => $filter,
            'flag'   => true,
        );
        return $this;
    }
    
    public function getParameter ($name, $type = INPUT_REQUEST) {
        if (!$type = self::_determineType($type))
            throw new InvalidArgumentException("Invalid type");
        
        if (isset($this->_filters[$type]) && $this->_filters[$type]['flag'])
            $this->_applyFilter($type);
            
        return isset($this->{"_{$type}"}[$name]) ? $this->{"_{$type}"}[$name] : null; 
    }
    
    public function getParameters ($type = INPUT_REQUEST) {
        if (!$type = self::_determineType($type))
            throw new InvalidArgumentException("Invalid type");
            
        if (isset($this->_filters[$type]) && $this->_filters[$type]['flag'])
            $this->_applyFilter($type);
            
        return $this->{"_{$type}"};
    }
     
    public function setParameter ($name, $value, $type = INPUT_REQUEST) {
        if (!$type = self::_determineType($type))
            throw new InvalidArgumentException("Invalid type");
            
        $this->{"_{$type}"}[$name] = $value;
        if (isset($this->_filters[$type]))
            $this->_filters[$type]['flag'] = true;
        return $this;
    }
    
    public function __get ($key) {
        return $this->getParameter($key);
    }
    
    public function __set ($key, $value) {
        $this->setParameter($key, $value);
    }
    
    public function getFile ($param_name) {
        return isset($this->_files[param_name]) ? $this->_files[param_name] : null;
    }
    
    public function getFiles () {
        return $this->_files;
    }
    
    protected function _applyFilter ($type) {
        if (!isset($this->_filters[$type]) || !isset($this->{"_{$type}"}))
            return false;
            
        if (!$this->{"_{$type}"} = filter_var_array($this->{"_{$type}"}, $this->_filters[$type]))
            throw new RuntimeException("Invalid filter");

        $this->_filters[$type]['flag'] = false;
        return $this;
    }
    
    protected static function _determineType ($type) {
        $types = array(
            INPUT_POST    => 'post', 
            INPUT_GET     => 'get', 
            INPUT_REQUEST => 'request', 
            INPUT_COOKIE  => 'cookie',
        );
        
        $type = strtolower($type);
        
        if (array_key_exists($type, $types))
            $type = $types[$type];
        elseif (!in_array($type, $types))
            return false;
            
        return $type;
    }
}

defined('INPUT_REQUEST') or define('INPUT_REQUEST', 99);