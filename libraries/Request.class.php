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
 * @version $Rev$
 * @subpackage Request
 */
class Request {
    
    /**
     * Request paramerers
     * @var array
     */
    protected $_request_parameters;
    
    /**
     * Request parameters filter definition
     * @see http://www.php.net/manual/en/function.filter-var-array.php
     * @var array
     */
    protected $_filter;
    
    /**
     * Browser Capabilities
     * @var array
     */
    protected $_browser_capabilities;
    
    /**
     * Default constructor
     */
    public function __construct () {
        $this->_request_parameters = $_REQUEST;
        
        // TODO Add browser capabilities class
        $this->_browser_capabilities = null;
    }
    
    /**
     * Getter for request vars
     * @param string $key
     * @return mixed
     */
    public function __get ($key) {
        return isset($this->_request_parameters[$key]) ? $this->_request_parameters[$key] : null;
    }
    
    /**
     * Setter for request vars
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        $this->_request_parameters[$key] = $value;
    }
    
    /**
     * Get the request parameters ($_REQUEST)
     * @return array
     */
    public function getRequestParameters () {
        return $this->_request_parameters;
    }
    
    /**
     * Add many parameters at once.
     * Note: this method is not affected by the filtering,
     * use it carefully if you need to add non-secure data.
     * @param array $collection
     * @return void
     */
    public function addAll ($collection = array(), $method = "merge") {
        if (empty($collection))
            return;
            
        switch (strtolower($method)) {
            default:
            case 'merge':
                $this->_request_parameters = array_merge($this->_request_parameters, (array)$collection);
                break;
                
            case 'add':
                $this->_request_parameters += (array)$collection;
        }
    }
    
    /**
     * Sets an input filter
     * @param array $definition
     * @return boolean
     */
    public function setFilter ($definition) {
        if ($request_parameters = filter_var_array($this->_request_parameters, $definition)) {
            $this->_filter = $definition;
            $this->_request_parameters = $request_parameters;
            return true;
        }
        return false;
    }
    
    /**
     * Get the input filter
     * Wil return null if no filter was set
     * @return array
     */
    public function getFilter () {
        return $this->_filter;
    }
    
    /**
     * Get the browser capabilities
     * @return array
     */
    public function getBrowserCapabilities() {
        return $this->_browser_capabilities;
    }
}