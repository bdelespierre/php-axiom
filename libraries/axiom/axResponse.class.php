<?php
/**
 * @brief Response class file
 * @file axResponse.class.php
 */

/**
 * @brief Response Class
 * 
 * @todo axResponse long description
 * @class axResponse
 * @author Delespierre
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axResponse {
    
    /**
     * @brief View name
     * @property string $_view
     */
    protected $_view;
    
    /**
     * @brief View section
     * @property string $_viewSection
     */
    protected $_viewSection;
    
    /**
     * @brief View format
     * @property string $_viewFormat
     */
    protected $_viewFormat;
    
    /**
     * @brief Layout name
     * @property string $_viewLayout
     */
    protected $_viewLayout;
    
    /**
     * @brief Enabled layout flag
     * @property boolean $_layoutEnabled
     */
    protected $_layoutEnabled;
    
    /**
     * @brief View variables
     * @property array $_vars
     */
    protected $_vars;
    
    /**
     * @brief Response Headers
     * @property array $_headers
     */
    protected $_headers;
    
    /**
     * @brrief Output callback
     * 
     * This callback will be executed on the response buffer (the stream sent to the browser).
     * 
     * @property callback $_outputCallback 
     */
    protected $_outputCallback;
    
    /**
     * @brief View vars filter
     * @see http://www.php.net/manual/en/function.filter-var-array.php
     * @property array $_filters
     */
    protected $_filter;
    
    /**
     * @brief Filter flag
     * 
     * Tells whenever the filter has been applied and should not be applied again.
     * 
     * @property boolean $_filterFlag
     */
    protected $_filterFlag;
    
    /**
     * @brief View messages
     * @property array $_messages
     */
    protected $_messages;
    
    /**
     * @brief View stylesheets
     * @property array
     */
    protected $_styleSheets;
    
    /**
     * @brief View scripts
     * @property array $_scripts 
     */
    protected $_scripts;
    
    /**
     * @brief Constructor
     */
    public function __construct () {
        $this->_layoutEnabled = true;
        $this->_vars          = array();
        $this->_headers       = array();
        $this->_messages      = array();
        $this->_styleSheets   = array();
        $this->_scripts       = array();
    }
    
    /**
     * @brief Reset the axResponse instance to its initial state
     * @return void
     */
    public function reset () {
        $this->_view           = null;
        $this->_viewSection    = null;
        $this->_viewFormat     = null;
        $this->_viewLayout     = null;
        $this->_outputCallback = null;
        $this->_filter         = null;
        $this->_layoutEnabled  = true;
        $this->_vars           = array();
        $this->_headers        = array();
        $this->_messages       = array();
        $this->_styleSheets    = array();
        $this->_scripts        = array();
    }
    
    /**
     * @brief Get the view name
     * 
     * Will return @c null if no view name was specified.
     * 
     * @return string
     */
    public function getView () {
        return $this->_view;
    }
    
    /**
     * @brief Set view name
     * @param string $view
     * @return axResponse
     */
    public function setView ($view) {
        $this->_view = $view;
        return $this;
    }
    
    /**
     * @brief Get the view section
     * 
     * Will return @c null if no view section was specified.
     * 
     * @return string
     */
    public function getViewSection () {
        return $this->_viewSection;
    }
    
    /**
     * @brief set View section
     * @param string $section
     * @return axResponse
     */
    public function setViewSection ($section) {
        $this->_viewSection = $section;
        return $this;
    }
    
    /**
     * @brief Get view format
     * 
     * Will return @c null if no format was specified.
     * 
     * @return string
     */
    public function getFormat () {
        return $this->_viewFormat;
    }
    
    /**
     * @brief Set view format
     * @param srting $format
     * @return axResponse
     */
    public function setFormat ($format) {
        $this->_viewFormat = $format;
        return $this;
    }
    
    /**
     * @brief Get view layout
     * 
     * Will return @c null if not layout was specified
     * 
     * @return string
     */
    public function getLayout () {
        return $this->_viewLayout;
    }
    
    /**
     * @brief Set view layout
     * @param string $layout
     * @return axResponse
     */
    public function setLayout ($layout) {
        $this->_viewLayout = $layout;
        return $this;
    }
    
    /**
     * @brief Enable of disable layout according to the $enabled parameter
     * @param boolean $enabled @optional @default{true}
     * @return axResponse
     */
    public function enableLayout ($enabled = true) {
        $this->_layoutEnabled = (boolean)$enabled;
        return $this;
    }
    
    /**
     * @brief Tells if the layout is enabled or not
     * @return boolean
     */
    public function layoutState () {
        return $this->_layoutEnabled;
    }
    
    /**
     * @brief Get the given var
     * 
     * If a filter was set using axResponse::setFilter, the filter will be applied before the data is returned.
     * Will return null if the corresponding var is not set. Will return false if the filter fails.
     * @warning You will loose all vars that doesn't pass the filter, they'll be set to @c false.
     * 
     * @param string $name
     * @return mixed
     */
    public function getVar ($name) {
        if ($this->_filterFlag)
            $this->_applyFilter();
        
        return isset($this->_vars[$name]) ? $this->_vars[$name] : null;
    }
    
    /**
     * @brief Set the given var
     * @param string $name
     * @param mixed $value
     * @return axResponse
     */
    public function setVar ($name, $value) {
        $this->_vars[(string)$name] = $value;
        $this->_filterFlag = true;
        return $this;
    }
    
    /**
     * @brief __get implementation
     * 
     * @see axResponse::getVar()
     * @param string $key
     * @return mixed
     */
    public function __get ($key) {
        return $this->getVar($key);
    }
    
    /**
     * @brief __set implementation
     * 
     * @see axResponse::setVar()
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        $this->setVar($key,$value);
    }
    
    /**
     * @brief Get all the registered var
     * 
     * If a filter was set using axResponse::setFilter, the filter will be applied before the data are returned.
     * Will return false if the filter fails.
     * @warning You will loose all vars that doesn't pass the filter, they'll be set to @c false.
     * 
     * @see http://www.php.net/manual/en/function.filter-var-array.php
     * @return array
     */
    public function getVars () {
        if ($this->_filterFlag)
            $this->_applyFilter();
        
        return $this->_vars;
    }
    
    /**
     * @brief Add or merge a collection to the current registered variables 
     * 
     * Will return false if the @c $method parameter is unknown.
     * 
     * @param array $collection
     * @param string $method @optional @default{axResponse::MERGE_VARS} Possible values are axResponse::MERGE_VARS 
     * or axResponse::ADD_VARS
     * @return axResponse
     */
    public function add ($collection, $method = self::MERGE_VARS) {
        if (!$collection)
            return $this;
        
        switch ($method) {
            case self::MERGE_VARS:
                $this->_vars = array_merge($this->_vars, $collection);
                break;
            case self::ADD_VARS:
                $this->_vars += $vars;
                break;
            default:
                return false;
        }
        $this->_filterFlag = true;
        return $this;
    }
    
    /**
     * @brief Remove all the registered variables
     * @return axResponse 
     */
    public function clearVars () {
        $this->_vars = array();
        return $this;
    }
    
    /**
     * @brief Add an header to the header list
     * 
     * If the header is already present in the list, it will be replaced.
     * @note HTTP Response header fields have been presets as axResponse constants.
     * 
     * @param string $field Should be one of axResponse::HEADER_*
     * @param string $value
     * @return axResponse
     */
    public function setHeader ($field, $value) {
        $this->_headers[$field] = "{$field}: {$value}";
        return $this;
    }
    
    /**
     * @brief Set multiple headers at once
     * 
     * The @c $headers parameters must be an associative array which keys are header fields and values header values.
     * Example:
     * @code
     * $response->setHeaders(array(
     * 	   axResponse::HEADER_CONTENT_TYPE         => 'application/octet-stream',
     * 	   axResponse::HEADER_CONTENT_DISCPOSITION => 'attachment; filename=download.abc'
     * ));
     * @endcode
     * If some header were previously set, they will be replaced.
     * 
     * @see axResponse::addHeader()
     * @param array $headers
     * @return axResponse
     */
    public function setHaders (array $headers) {
        foreach ($headers as $field => $value) {
            $this->setHeader($field, $value);
        }
        return $this;
    }
    
    /**
     * @brief Set the HTTP status
     * @link http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     * @param string $http_status
     * @return axResponse
     */
    public function setStatus ($http_status) {
        $this->_headers['status'] = $http_status;
        return $this;
    }
    
    /**
     * @brief Get the headers list
     * @return array
     */
    public function getHeaders () {
        return array_unique($this->_headers);
    }
    
    /**
     * @brief Clear all registered headers
     * @return axResponse
     */
    public function clearHeaders () {
        $this->_headers = array();
        return $this;
    }
    
    /**
     * @brief Set a callback to be executed on the output buffer before it is sent to the browser
     * 
     * Usage:
     * @code
     * // Let's replace all <h1> tags by <h3>
     * $response->setOutputCallback(create_function(
     *     '$buffer',
     *     'return str_replace(array('<h1>','</h1>'),array('<h3>','</h3>'),$buffer);'
     * ));
     * @endcode
     * Callback must be a valid callback (see http://php.net/manual/en/language.pseudo-types.php), false will be 
     * returned otherwise.
     * 
     * @param callback $callback
     * @return axResponse
     */
    public function setOutputCallback ($callback) {
        if (!is_callable($this->_outputCallback = $callback)) {
            $this->_outputCallback = null;
            return false;
        }
        return $this;
    }
    
    /**
     * @brief Get the output callback
     * 
     * Will return @c null if no output callback was set.
     * 
     * @return callback
     */
    public function getOutputCallback () {
        return $this->_outputCallback;
    }
    
    /**
     * @brief Tells whenever a filter has been registered or not
     * @return boolean
     */
    public function hasFilter () {
        return isset($this->_filter);
    }
    
    /**
     * @brief Get the registered filter
     * 
     * @see http://php.net/manual/en/function.filter-var-array.php
     * @return array
     */
    public function getFilter () {
        return $this->_filter;
    }
    
    /**
     * @brief Set a response variable filter
     * 
     * When you set a variable filter, all data you may extract with axResponse::getVar(), axResponse::__get(), or 
     * axResponse::getVars() are filtered using filter_var_array before they  are returned, allowing you to set 
     * sanitize or validation filter, for instance to prevent XSS attacks.
     * 
     * @warning The @c $filter parameter must be compliant with the @c $definition parameter of PHP's filter_var_array. 
     * If the filtering ends up with an error, all variables registered in axResponse are erased and a RuntimeException 
     * will be thrown when accessing datas with axResponse::getVar(), axResponse::__get(), or axResponse::getVars().
     * 
     * @note The filter will be applied on read so your changes won't take effects until you extract response data with
     * axResponse::getVar(), axResponse::getVars() or with the magic method axResponse::__get().
     * 
     * @see http://php.net/manual/en/function.filter-var-array.php
     * @param array $filter
     * @return axResponse
     */
    public function setFilter (array $filter) {
        $this->_filter = $filter;
        $this->_filterFlag = true;
        return $this;
    }
        
    /**
     * @brief Adds a view message
     * 
     * For convenience, the `$message` parameter will be casted to string. Custom levels are authorized.
     * 
     * @param string $message
     * @param strign $level @optional @default{axResponse::MESSAGE_WARNING} One of 
     * axResponse::MESSAGE_WARNING,axResponse::MESSAGE_NOTICE, or axResponse::MESSAGE_ALERT or any string  describing a 
     * level
     * @return axResponse
     */
    public function addMessage ($message, $level = self::MESSAGE_WARNING) {
        if (!isset($this->_messages[$level]))
            $this->_messages[$level] = array();
        
        $this->_messages[$level][(string)$message] = (string)$message;
        return $this;
    }
    
    /**
     * @breif Remove a view message
     * @param string $message
     * @return axResponse
     */
    public function removeMessage ($message) {
        foreach ($this->_messages as $level => $messages) {
            if (isset($messages[(string)$message]))
                unset($this->_messages[$level][(string)$message]);
        }
        return $this;
    }
    
    /**
     * @brief Get all view messages, optionnaly filtered by their level
     * 
     * If no level is provided, all messages from all levels will be returned in a 2 dimentionnal associative array.
     * 
     * @param string $level @optionnal @default{null}
     * @return array
     */
    public function getMessages ($level = null) {
        if ($level)
            return isset($this->_messages[$level]) ? $this->_messages[$level] : array();
        else
            return $this->_messages;
    }
    
    /**
     * @brief Erase all registered messages
     * @return axResponse
     */
    public function clearMessages () {
        $this->_messages = array();
        return $this;
    }
    
    /**
     * @brief Add a view style sheet
     * @param string $stylesheet The `href` attribute of the `<link />` tag
     * @param unknown_type $type @optional @default{"text/css"} The `type` attribute of the `<link />` tag
     * @param unknown_type $media @optional @default{"screen"} The `media` attribute of the `<link />` tag
     * @return axResponse
     */
    public function addStyleSheet ($stylesheet, $type= "text/css", $media = "screen") {
        $this->_styleSheets[$stylesheet] = array(
        	'href'  => $stylesheet, 
        	'type'  => $type,
            'media' => $media
        );
        return $this;
    }
    
    /**
     * @brief Remove a style sheet (identified by its `href` attribute)
     * @see axResponse::addStyleSheet
     * @param string $stylehseet
     * @return axResponse
     */
    public function removeStyleSheet ($stylehseet) {
        unset($this->_styleSheets[$stylesheet]);
        return $this;
    }
    
    /**
     * @brief Erase all registered stylesheets
     * @return axResponse
     */
    public function clearStyleSheets () {
        $this->_styleSheets = array();
        return $this;
    }
    
    /**
     * @breif Add a view script 
     * @param string $script The `src` attribute of the `<script>` tag
     * @param string $type @optional @default{"text/javascript"} The `type` attribute of the `<script>` tag
     * @return axResponse
     */
    public function addScript ($script, $type = "text/javascript") {
        $this->_scripts[$script] = array(
            'src'  => $script,
            'type' => $type
        );
        return $this;
    }
    
    /**
     * @brief Remove a script (identified by its `src`)
     * @see axResponse::addScript
     * @param string $script 
     * @return axResponse
     */
    public function removeScript ($script) {
        unset($this->_scripts[$script]);
        return $this;
    }
    
    /**
     * @brief Erase all registered scripts
     * @return axResponse
     */
    public function clearScripts () {
        $this->_scripts = array();
        return $this;
    }
    
    /**
     * @brief PHP setcookie alias
     * 
     * Returns the current instance in case of success, false otherwise.
     * 
     * @link http://php.net/manual/en/function.setcookie.php
     * @param string $name
     * @param string $value @optional @default{""}
     * @param integer $expire @optional @default{0}
     * @param string $path @optional @default{""}
     * @param string $domain @optional @default{""}
     * @param boolean $secure @optional @default{false}
     * @param string $httponly @optional @default{false}
     * @return boolean
     */
    public function setCookie ($name, 
                               $value = "", 
                               $expire = 0, 
                               $path = "",
                               $domain = "", 
                               $secure = false, 
                               $httponly = false) {
        return setcookie($name,$value,$expire,$path,$domain,$secure,$httponly) ? $this : false;
    }
    
    /**
     * @brief PHP setrawcookie alias
     * 
     * Returns the current instance in case of success, false otherwise.
     * 
     * @link http://www.php.net/manual/en/function.setrawcookie.php
     * @param string $name
     * @param string $value @optional @default{""}
     * @param integer $expire @optional @default{0}
     * @param string $path @optional @default{""}
     * @param string $domain @optional @default{""}
     * @param boolean $secure @optional @default{false}
     * @param string $httponly @optional @default{false}
     * @return boolean
     */
    public function setRawCookie ($name, 
                                  $value = "", 
                                  $expire = 0, 
                                  $path = "",
                                  $domain = "", 
                                  $secure = false, 
                                  $httponly = false) {
        return setrawcookie($name,$value,$expire,$path,$domain,$secure,$httponly) ? $this : false;
    }
    
    /**
     * @brief If any, apply the filter on the registered variables
     * 
     * @important If the filter fails, all variables will be erased as well as the registered filter to prevent the 
     * script to be stuck. A RuntimeException will also be thrown.
     * 
     * @internal
     * @throws RuntimeException If the filter fails
     * @return boolan
     */
    protected function _applyFilter () {
        if (!isset($this->_filter))
            return false;
        
        $this->_vars = filter_var_array($this->_vars, $this->_filter);
        $this->_filterFlag = false;
        if (!$this->_vars) {
            $this->clearVars();
            $this->_filter = null;
            throw new RuntimeException("Incorrect filter definition");
        }
        return true;
    }
    
    /**
     * @brief Var merging flags
     * @see axResponse::addVars()
     * @var string
     */
    const MERGE_VARS      = "merge";
    const ADD_VARS        = "add";
    
    /**
     * @brief Message level flags
     * @var string
     */
    const MESSAGE_NOTICE  = "notice";
    const MESSAGE_WARNING = "warning";
    const MESSAGE_ALERT   = "alert";
    
    /**
     * @brief Header fields
     * @see axResponse::addHeader()
     * @see http://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Responses
     * @var string
     */
    const HEADER_ACCEPT_RANGE        = "Accept-Ranges";
    const HEADER_AGE                 = "Age";
    const HEADER_ALLOW               = "Allow";
    const HEADER_CACHE_CONTROL       = "Cache-Control";
    const HEADER_CONNECTION          = "Connection";
    const HEADER_CONTENT_ENCODING    = "Content-Encoding";
    const HEADER_CONTENT_LANGUAGE    = "Content-Language";
    const HEADER_CONTENT_LENGTH      = "Content-Length";
    const HEADER_CONTENT_LOCATION    = "Content-Location";
    const HEADER_CONTENT_MD5         = "Content-MD5";
    const HEADER_CONTENT_DISPOSITION = "Content-Disposition";
    const HEADER_CONTENT_RANGE       = "Content-Range";
    const HEADER_CONTENT_TYPE        = "Content-Type";
    const HEADER_DATE                = "Date";
    const HEADER_ETAG                = "ETag";
    const HEADER_EXPIRES             = "Expires";
    const HEADER_LAST_MODIFIED       = "Last-Modified";
    const HEADER_LINK                = "Link";
    const HEADER_LOCATION            = "Location";
    const HEADER_P3P                 = "P3P";
    const HEADER_PRAGMA              = "Pragma";
    const HEADER_PROXY_AUTHENTICATE  = "Proxy-Authenticate";
    const HEADER_REFRESH             = "Refresh";
    const HEADER_RETRY_AFTER         = "Retry-After";
    const HEADER_SERVER              = "Server";
    const HEADER_SET_COOKIE          = "Set-Cookie";
    const HEADER_STS                 = "Strict-Transport-Security";
    const HEADER_TRAILER             = "Trailer";
    const HEADER_TRANSFER_ENCODING   = "Transfer-Encoding";
    const HEADER_VARY                = "Vary";
    const HEADER_VIA                 = "Via";
    const HEADER_WARNING             = "Warning";
    const HEADER_WWW_AUTHENTICATE    = "WWW-Authenticate";
}