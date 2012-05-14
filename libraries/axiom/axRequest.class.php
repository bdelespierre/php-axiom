<?php
/**
 * @brief Request class file
 * @file axRequest.class.php
 */

/**
 * @brief Request Class
 *
 * @todo axRequest long description
 * @class axRequest
 * @author Delespierre
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axRequest {
    
    /**
     * @brief Request headers
     * @property array $_headers
     */
    protected $_headers;
    
    /**
     * @brief HTTP POST parameters
     * @property array $_post
     */
    protected $_post;
    
    /**
     * @brief HTTP GET parameters
     * @property array $_get
     */
    protected $_get;
    
    /**
     * @brief Request parameters (POST + GET)
     * @property array $_request
     */
    protected $_request;
    
    /**
     * @brief Request cookies
     * @property array $_cookies
     */
    protected $_cookies;
    
    /**
     * @brief HTTP POST files
     * @property array $_files
     */
    protected $_files;
    
    /**
     * @brief Request variables filters
     * @property array $_filters
     */
    protected $_filters;
    
    /**
     * @brief Browser capabilities instance
     * @property Browscap $_browscap
     */
    protected $_browscap;
    
    /**
     * @brief Constructor
     * @param string $cache_dir @optional @default{null} The cache dir for the browscap settings (null to disable cache)
     */
    public function __construct ($cache_dir = null) {
        $this->_headers  = getallheaders();
        $this->_post     = $_POST;
        $this->_get      = $_GET;
        $this->_request  = $_REQUEST;
        $this->_cookies  = $_COOKIE;
        $this->_files    = $_FILES;
        $this->_filters  = array();
        $this->_browscap = ($cache_dir && class_exists('Browscap', true)) ? new Browscap($cache_dir) : null;
    }
    
    /**
     * @brief Resets the request object in its default state
     * @return void
     */
    public function reset () {
        $this->_headers  = getallheaders();
        $this->_post     = $_POST;
        $this->_get      = $_GET;
        $this->_request  = $_REQUEST;
        $this->_cookies  = $_COOKIE;
        $this->_filters  = array();
    }
    
    /**
     * @brief Get the request headers
     * @return array
     */
    public function getHeaders () {
        return $this->_headers;
    }
    
    /**
     * @brief Tells if a header had been recieved (identified by its name)
     * @param string $header
     * @return boolean
     */
    public function headerExists ($header) {
        return isset($this->_headers[$header]);
    }
    
    /**
     * @brief Get the given request header value (identified by its name)
     * @param string $header
     * @return string
     */
    public function getHeader ($header) {
        return $this->headerExists($header) ? $this->_headers[$header] : null;
    }
    
    /**
     * @brief Get the HTTP request method
     * @return string
     */
    public function getMethod () {
        return $this->_server['REQUEST_METHOD'];
    }
    
    /**
     * @brief Get an environment variable (provided by Apache for instance)
     *
     * This method is an alias of PHP's getenv()
     *
     * @link http://php.net/manual/fr/function.getenv.php
     * @param string $varname
     * @return string
     */
    public function getEnv ($varname) {
        return getenv($varname);
    }
    
    /**
     * @brief Get server variable
     *
     * If not variable is specified, the complete @c $_SERVER structure is returned.
     *
     * @param string $varname @optional @default{null}
     * @return string
     */
    public function getServer ($varname = null) {
        if ($varname)
            return isset($_SERVER[$varname]) ? $_SERVER[$varname] : null;
        
        return $_SERVER;
    }
    
    /**
     * @brief Get the request cookies
     * @return array
     */
    public function getCookies () {
        return $this->_cookies;
    }
    
    /**
     * @brief Tells if the given cookie exists (identified by its name)
     * @param string $name
     * @return boolean
     */
    public function cookieExists ($name) {
        return array_key_exists($name, $this->_cookies);
    }
    
    /**
     * @brief Get the given cookie (identified by its name)
     * @param string $name
     * @return string
     */
    public function getCookie ($name) {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name] : null;
    }
    
    /**
     * @brief Set a request variable filter
     *
     * When you set a variable filter, all data you may extract with axRequest::getParameters(),
     * axRequest::getParameter() or axResponse::__get() are filtered using filter_var_array before they  are returned,
     * allowing you to set sanitize or validation filter, for instance to prevent injection attacks.
     *
     * @warning The @c $filter parameter must be compliant with the @c $definition parameter of filter_var_array.
     * If the filtering ends up with an error, all variables registered in axResponse are erased and a RuntimeException
     * will be thrown when accessing datas with axResponse::getVar(), axResponse::__get() or axResponse::getVars().
     *
     * @note The filter will be applied on read so your changes won't take effects until you extract response data with
     * axRequest::getParameters(), axRequest::getParameter() or axResponse::__get().
     *
     * @param array $filter The filter description (must be compliant with filter_var_array filter description)
     * @param mixed $type @optional @default{INPUT_REQUEST} The kind of request parameters to filter. Can be either
     * `INPUT_GET`, `INPUT_POST`, `INPUT_REQUEST`, `INPUT_COOKIE` or string `get`, `post`, `request`, `cookie` (the
     * case is insensitive)
     * @throw InvalidArgumentException In case of invalid @c $type
     * @return axRequest
     */
    public function setFilter (array $filter, $type = INPUT_REQUEST) {
        if (!$type = self::_determineType($type))
            throw new InvalidArgumentException("Invalid type");
            
        $this->_filters[$type] = array(
            'filter' => $filter,
            'flag'   => true,
        );
        return $this;
    }
    
    /**
     * @brief Get a request parameter
     *
     * @param string $name
     * @param mixed $type @optional @default{INPUT_REQUEST} The kind of request parameters to filter. Can be either
     * `INPUT_GET`, `INPUT_POST`, `INPUT_REQUEST`, `INPUT_COOKIE` or string `get`, `post`, `request`, `cookie` (the
     * case is insensitive)
     * @throw InvalidArgumentException In case of invalid @c $type
     * @return mixed
     */
    public function getParameter ($name, $type = INPUT_REQUEST) {
        if (!$type = self::_determineType($type))
            throw new InvalidArgumentException("Invalid type");
        
        if (isset($this->_filters[$type]) && $this->_filters[$type]['flag'])
            $this->_applyFilter($type);
            
        return isset($this->{"_{$type}"}[$name]) ? $this->{"_{$type}"}[$name] : null;
    }
    
    /**
     * @brief Get all request parameters
     *
     * @param mixed $type @optional @default{INPUT_REQUEST} The kind of request parameters to filter. Can be either
     * `INPUT_GET`, `INPUT_POST`, `INPUT_REQUEST`, `INPUT_COOKIE` or string `get`, `post`, `request`, `cookie` (the
     * case is insensitive)
     * @throw InvalidArgumentException In case of invalid @c $type
     * @return array
     */
    public function getParameters ($type = INPUT_REQUEST) {
        if (!$type = self::_determineType($type))
            throw new InvalidArgumentException("Invalid type");
            
        if (isset($this->_filters[$type]) && $this->_filters[$type]['flag'])
            $this->_applyFilter($type);
            
        return $this->{"_{$type}"};
    }
    
    /**
     * @brief Manually set a request parameter
     *
     * @param string $name
     * @param mixed $value
     * @param mixed $type @optional @default{INPUT_REQUEST} The kind of request parameters to filter. Can be either
     * `INPUT_GET`, `INPUT_POST`, `INPUT_REQUEST`, `INPUT_COOKIE` or string `get`, `post`, `request`, `cookie` (the
     * case is insensitive)
     * @throw InvalidArgumentException In case of invalid @c $type
     * @return axRequest
     */
    public function setParameter ($name, $value, $type = INPUT_REQUEST) {
        if (!$type = self::_determineType($type))
            throw new InvalidArgumentException("Invalid type");
            
        $this->{"_{$type}"}[$name] = $value;
        if (isset($this->_filters[$type]))
            $this->_filters[$type]['flag'] = true;
        return $this;
    }
    
    /**
     * @brief Add a collection of parameters
     *
     * The @c method parameter can be either @c 'add', @c 'merge', axRequest::PROPERTY_MERGE, or axRequest::PROPERTY_ADD
     *
     * @param mixed $collection An associative structure to import
     * @param mixed $method @optional @default{axRequest::PROPERTY_MERGE} The merge mode
     * @param mixed $type @optional @default{INPUT_REQUEST} The type of variables to be added
     * @throws InvalidArgumentException If the @c $method parameter is invalid
     * @return axRequest
     */
    public function add ($collection, $method = self::PROPERTY_MERGE, $type = INPUT_REQUEST) {
        if (!$type = self::_determineType($type))
            throw new InvalidArgumentException("Invalid type");
        
        switch (strtolower($method)) {
            case 'merge':
            case self::PROPERTY_MERGE:
                $this->{"_{$type}"} = array_merge($this->{"_{$type}"}, $collection);
                break;
            case 'add':
            case self::PROPERTY_ADD:
                $this->{"_{$type}"} += $collection;
                break;
            default:
                throw new InvalidArgumentException("Invalid method {$method}");
        }
        if (isset($this->_filters[$type]))
            $this->_filters[$type]['flag'] = true;
            
        return $this;
    }
    
    /**
     * @brief __get implementation
     *
     * Alias of axRequest::getParameter() using INPUT_REQUEST type.
     *
     * @param string $key
     * @return mixed
     */
    public function __get ($key) {
        return $this->getParameter($key);
    }
    
    /**
     * @brief __set implementation
     *
     * Alias of axRequest::setParameter() with INPUT_REQUEST type.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        $this->setParameter($key, $value);
    }
    
    /**
     * @brief Get the given file (identified by the parameter name)
     *
     * Will return @c null if the file isn't set in $_FILES structure.
     *
     * @param string $param_name The POST parameter name
     * @return array
     */
    public function getFile ($param_name) {
        return isset($this->_files[param_name]) ? $this->_files[param_name] : null;
    }
    
    /**
     * @brief Get the $_FILES structure
     *
     * @return array
     */
    public function getFiles () {
        return $this->_files;
    }
    
    /**
     * @brief Applies a filter on the given variable type
     *
     * Will return false if no filter is defined for this type.
     *
     * @param mixed $type @optional @default{INPUT_REQUEST} The kind of request parameters to filter. Can be either
     * `INPUT_GET`, `INPUT_POST`, `INPUT_REQUEST`, `INPUT_COOKIE` or string `get`, `post`, `request`, `cookie` (the
     * case is insensitive)
     * @throws RuntimeException In case of incorrect type
     * @return axRequest
     */
    protected function _applyFilter ($type) {
        if (!isset($this->_filters[$type]) || !isset($this->{"_{$type}"}))
            return false;
        
        if (!$this->{"_{$type}"} = filter_var_array($this->{"_{$type}"}, $this->_filters[$type]['filter']))
            throw new RuntimeException("Invalid filter");

        $this->_filters[$type]['flag'] = false;
        return $this;
    }
    
    /**
     * @brief Determine the real type according to the @c $type parameter
     * @param mixed $type
     * @return string
     */
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
    
    /**
     * @brief Property adding flags
     * @var integer
     */
    const PROPERTY_MERGE = 1;
    const PROPERTY_ADD   = 2;
}

/**
 * @def INPUT_REQUEST
 */
defined('INPUT_REQUEST') or define('INPUT_REQUEST', 99);