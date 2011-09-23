<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Response Class
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage Response
 */
class Response {
    
    /**
     * Response vars
     * @var array
     */
    protected $_response_vars;
    
    /**
     * Response vars filter
     * @var array
     */
    protected $_filter;
    
    /**
     * View messages
     * @var array
     */
    protected $_messages;
    
    /**
     * Response view to be used
     * @var string
     */
    protected $_response_view;
    
    /**
     * Response output format
     * @var string
     */
    protected $_output_format;
    
    /**
     * Enable page layout
     * @var boolean
     */
    protected $_layout_enabled;
    
    /**
     * Headers to be sent
     * @var array
     */
    protected $_headers;
    
    /**
     * Default constructor
     */
    public function __construct () {
        $this->_response_vars = array();
        $this->_messages = array();
        $this->_layout_enabled = true;
        $this->_headers = array();
    }
    
    /**
     * Getter for response vars
     * @param string $key
     * @return mixed
     */
    public function __get ($key) {
        return isset($this->_response_vars[$key]) ? $this->_response_vars[$key] : null;
    }
    
    /**
     * Setter for response vars
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        $this->_response_vars[$key] = $value;
    }
    
    /**
     * Get response vars
     * @return array
     */
    public function getResponseVars () {
        return $this->_response_vars;
    }
    
    /**
     * Add responses vars at once
     * @param array $collection
     * @return void
     */
    public function addAll ($collection = array(), $method = "merge") {
        if (empty($collection))
            return;
            
        switch (strtolower($method)) {
            default:
            case "merge":
                $this->_response_vars = array_merge($this->_response_vars, (array)$collection);
                break;
                
            case "add":
                $this->_response_vars += (array)$collection;
        }
    }
    
    /**
     * Sets the response vars filter
     * @param array $definition
     * @return boolean
     */
    public function setFilter ($definition) {
        if ($response_vars = filter_var_array($this->_response_vars, $definition)) {
            $this->_filter = $definition;
            $this->_response_vars = $response_vars;
            return true;
        }
        return false;
    }
    
    /**
     * Get the ouput filter
     * Wil return null if no filter was set
     * @return array
     */
    public function getFilter () {
        return $this->_filter;
    }
    
    /**
     * Add a message
     * @param string $message
     * @param integer $level = MESSAGE_WARNING
     * @return void
     */
    public function addMessage ($message, $level = MESSAGE_WARNING) {
        if (!isset($this->_messages[$level]))
            $this->_messages[$level] = array();
        
        $this->_messages[$level][] = $message;
    }
    
    /**
     * Get messages
     * @return array
     */
    public function getMessages () {
        return $this->_messages;
    }
    
    /**
     * Get response view
     * @return string
     */
    public function getResponseView () {
        return isset($this->_response_view) ? $this->_response_view : null;
    }
    
    /**
     * Set response view
     * @param string $view_name
     * @return void
     */
    public function setResponseView ($view_name) {
        $this->_response_view = $view_name;
    }
    
    /**
     * Get output format
     * @return string
     */
    public function getOutputFormat () {
        return isset($this->_output_format) ? $this->_output_format : null;
    }
    
    /**
     * Set output format
     * @param string $format
     * @return void
     */
    public function setOutputFormat ($format) {
        $this->_output_format = $format;
    }
    
    /**
     * Set output transformer
     * FIXME Not implemented
     * @param callback $callback
     * @throws InvalidArgumentException
     * @return void
     */
    public function setOutputTransformer ($callback) {
        if (is_callable($callback))
            $this->_output_transformer = $callback;
        else
            throw new InvalidArgumentException("Passed callback is not callable", 2007);
    }
    
    /**
     * Disable or enable the page layout
     * @param $enabled =true
     * @return void
     */
    public function setLayout ($enabled = true) {
        $this->_layout_enabled = (boolean)$enabled;
    }
    
    /**
     * Tell if the layout is enabled
     * @return boolean
     */
    public function layout () {
        return $this->_layout_enabled;
    }
    
    /**
     * Add an HTTP header field
     * @param string $header
     * @return void
     */
    public function setHeader ($header) {
        $this->_headers[md5($header)] = $header;
    }
    
    /**
     * Removes an HTTP header field
     * @param string $header
     * @return void
     */
    public function unsetHeader ($header) {
        if (isset($this->_headers[md5($header)])) {
            unset($this->_headers[md5($header)]);
            return true;
        }
        return false;
    }
    
    /**
     * Get all the headers
     * @return array
     */
    public function getHeaders () {
        return $this->_headers;
    }
    
    /**
     * Removes all headers
     * @return void
     */
    public function flushHeaders () {
        $this->_headers = array();
    }
}