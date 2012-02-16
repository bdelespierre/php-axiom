<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * View Manager Class
 * 
 * TODO add long description
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage core
 */
class axViewManager {
    
    /**
     * Registered view paths
     * @internal
     * @var array
     */
    protected $_viewPaths;
    
    /**
     * Output (transformation) callback
     * @internal
     * @var callback
     */
    protected $_outputCallback;
    
    /**
     * Layout name
     * @internal
     * @var string
     */
    protected $_layout;
    
    /**
     * Default view format
     * @internal
     * @var string
     */
    protected $_defaultFormat;
    
    /**
     * Layout variables
     * @var array
     */
    protected $_layoutVars;
    
    /**
     * Default constructor
     * @param string $layout
     * @param array $view_paths [optional] [default `null`]
     * @param string $default_format [optional] [default `"html"`]
     * @param array $layout_vars [optional] [default `array()`]
     */
    public function __construct ($layout, $view_paths = null, $default_format = 'html', array $layout_vars = array()) {
        $this->_viewPaths      = (array)$view_paths;
        $this->_outputCallback = false;
        $this->_layoutVars     = $layout_vars;
        $this->_layout         = $layout ? $layout : false;
        $this->_defaultFormat  = $default_format;
    }
    
    /**
     * Add a view directory
     * 
     * Will throw an `axMissingFileException` if the directory does'nt exists.
     * 
     * @param string $view The directory path to add
     * @throws axMissingFileException 
     * @return axViewManager
     */
    public function add ($view) {
        if (!$this->_viewPaths[] = realpath($view));
            throw new axMissingFileException($view);
        
        return $this;
    }
    
    /**
     * Loads a view
     * 
     * This methods accepts 3 different prototypes:
     * * axViewManager::load(axResponse $response)
     * * axViewManager::load($path, array $vars = array())
     * * axViewManager::load($section, $view, $format = "html", array $vars = array(), $layout = null)
     * 
     * In the first and third forms, the view path is determined using the `$section` and `$view` parameters (which are
     * extracted from the `$response` object in the first form) and by seeking for the appropriate file according to
     * registered view paths (added with `axViewManager::add`).
     * 
     * In all cases, the complete page buffer is returned.
     * 
     * A `RuntimeException` is thrown if
     * * the calculated view path is invalid (the file simply doesn't exist)
     * * the layout for this view / format cannot be found
     * * the view cannot be loaded
     * * the layout cannot be loaded
     * 
     * @return string
     */
    public function load () {
        $args = func_get_args();
        
        if (!count($args)) {
            throw new InvalidArgumentException("No argument provided");
        }
        elseif (count($args) == 1 && $args[0] instanceof axResponse) {
            
            /**
             * First form:
             * axViewManager::load(axResponse $response);
             */
            
            $response = $args[0];
            $view     = $response->getView();
            $section  = $response->getViewSection();
            $format   = $response->getFormat() ? $response->getFormat() : $this->_defaultFormat;
            $vars     = $response->getVars();
            
            // TODO add scripts, stylesheet and messages incorporation here
            
            if (!$response->layoutState()) {
                $layout = false;
            }
            elseif ($response->getLayout() !== null && $response->getLayout() != $this->_layout) {
                $layout = $response->getLayout();
            }
            else {
                $layout = $this->_layout;
            }
            
            if (!is_file($view_path = $this->_findView($section, $view, $format)))
                throw new RuntimeException("Unable to find view {$section}/{$view} with {$format} format");
        }
        elseif ((count($args) == 2 && is_array($args[1])) || count($args) == 1) {
            
            /**
             * Second Form:
             * axViewManager::load($path, array $vars = array());
             */
            
            list($view_path,$vars,$layout,$format) = $args + array('', array(), $this->_layout, $this->_defaultFormat);
            
            if (!is_file($view_path))
                throw new RuntimeException("Unable to find view {$view_path}");
        }
        else {
            
            /**
             * Third From:
             * axViewManager::load($section, $view, $format = "html", array $vars = array(), $layout = null);
             */
            
            list($section,$view,$format,$vars,$layout) = $args + array(
            	'', '', $this->_defaultFormat, array(),$this->_layout
            );
            
            if (!is_file($view_path = $this->_findView($section, $view, $format)))
                throw new RuntimeException("Unable to find view {$section}/{$view} with {$format} format");
        }
        
        if ($layout && !is_file($layout) && !$layout = $this->_findLayout($layout, $format))
            throw new RuntimeException("Unable to find layout {$layout} with format {$format}");
        
        if (!$this->setOutputFormat($format))
            throw new RuntimeException("Unable to set format to {$format}");
        
        // TODO send headers as well here !
        
        if (!${'view_content'} = $this->_loadView($view_path, $vars))
            throw new RuntimeException("Unable to load view {$view_path}");
            
        if ($layout) {
            return $this->_loadLayout($layout, $vars + array('view_content' => ${'view_content'}));
        }
        else {
            return ${'view_content'};
        }
    }
    
    /**
     * Set the output callback
     * @param callback $callback
     * @return axViewManager
     */
    public function setOutputCallback ($callback) {
        if (!is_callable($this->_outputCallback = $callback))
            return false;
        
        return $this;
    }
    
    /**
     * Set the output format (sends the content-type header)
     * @param string $format Must be one of `html`,`json`,`csv`,`xml`,`text`
     * @return axViewManager
     */
    public function setOutputFormat ($format) {
        switch (strtolower($format)) {
            case 'html': header('Content-Type: text/html; charset=UTF-8'); break;
            case 'json': header('Content-Type: application/json; charset=UTF-8'); break;
            case 'csv' : header('Content-Type: application/csv; charset=UTF-8'); break;
            case 'xml' : header('Content-Type: text/xml; charset=UTF-8'); break;
            case 'text': header('Content-Type: text/plain; charset=UTF-8'); break;
            default: return false;
        }
        
        return $this;
    }
    
    /**
     * Set the layout name and optionaly format
     * @param string $layout
     * @param string $format [optional] [default `null`] If not provided, the default format is used (as per set in the
     * constructor)
     * @return axViewManager
     */
    public function setLayout ($layout, $format = null) {
        $this->_layout = $layout;
        return $this;
    }
    
    /**
     * Get the given layout variable
     * @param sring $name
     * @return mixed
     */
    public function getVar ($name) {
        return isset($this->_layoutVars[$name]) ? $this->_layoutVars[$name] : null;
    }
    
    /**
     * Set the given layout variable
     * @param string $name
     * @param mixed $value
     * @return axViewManager
     */
    public function setVar ($name, $value) {
        $this->_layoutVars[$name] = $value;
        return $this;
    }
    
    /**
     * Add a collection to the registered view variables
     * @param array $vars The map of variables to add
     * @param string $method [optional] [default `axViewManager::MERGE_VARS`] The adding method
     * @return axViewManager
     */
    public function addAll (array $vars, $method = self::MERGE_VARS) {
        switch ($method) {
            case self::MERGE_VARS:
                $this->_layoutVars = array_merge($this->_layoutVars, $vars);
                break;
            case self::ADD_VARS:
                $this->_layoutVars += $vars;
                break;
            default:
                return false;
        }
        return $this;
    }
    
    /**
     * __get implementeation
     * 
     * Alias of `axViewManager::getVar`
     * 
     * @see axViewManager::getVar
     * @param string $key
     * @return mixed
     */
    public function __get ($key) {
        return $this->getVar($key);
    }
    
    /**
     * __set implementeation
     * 
     * Alias of `axViewManager::setVar`
     * 
     * @see axViewManager::setVar
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        $this->setVar($key, $value);
    }
    
    /**
     * __isset implementation
     * 
     * Check whenever a layout variable is set or not
     * 
     * @param string $key
     * @return boolean
     */
    public function __isset ($key) {
        return isset($this->_layoutVars[$key]);
    }
    
    /**
     * __unset implementation
     * 
     * Unset the given layout variable
     * 
     * @param string $key
     * @return void
     */
    public function __unset ($key) {
        unset($this->_layoutVars[$key]);
    }
    
    /**
     * Find the proper layout according to the `$layout` and `$format` parameters
     * 
     * Returns the path in case of success, false on errors.
     * 
     * @param string $layout
     * @param string $format
     * @return string
     */
    protected function _findLayout ($layout, $format) {
        if (!$format || !$layout)
            return false;
            
        $format = strtolower($format);
        
        foreach ($this->_viewPaths as $view_path) {
            if (is_file($path = $view_path . "/layouts/{$layout}.{$format}.php"))
                return $path;
        }
        return false;
    }
    
    /**
     * Find the proper view according to the `$section`, `$view` and `$format` parameters
     * 
     * Return the path in case of success, false on errors.
     * 
     * @param string $section
     * @param string $view
     * @param string $format
     * @return string
     */
    protected function _findView ($section, $view, $format) {
        if (!$section || !$view || !$format)
            return false;
        
        $format = strtolower($format);
            
        foreach ($this->_viewPaths as $view_path) {
            if (is_file($path = $view_path . "/{$section}/{$view}.{$format}.php"))
                return $path;
        }
        return false;
    }
    
    /**
     * Load (include) the view and returns the produced buffer
     * @param string $__path
     * @param array $__vars [optional] [default `array()`]
     * @return string
     */
    protected function _loadView ($__path, array $__vars = array()) {
        if (!ob_start())
            return false;
        
        extract($this->_layoutVars, EXTR_PREFIX_ALL, 'layout');
        extract($__vars);
        
        if (!include $__path)
            return false;
        
        return ob_get_clean();
    }
    
    /**
     * Load (include) the layout and returns the produced buffer
     * @param string $__path
     * @param array $__vars [optional] [default `array()`]
     * @return string
     */
    protected function _loadLayout ($__path, array $__vars = array()) {
        if (!ob_start())
            return false;
            
        extract($this->_layoutVars, EXTR_PREFIX_ALL, 'layout');
        extract($__vars);
        
        if (!include $__path)
            return false;
        
        ${'layout_content'} = ob_get_clean();
        return ${'layout_content'};
    }
    
    /**
     * Variable adding flags
     * @see axViewManager::addVars
     * @var string
     */
    const MERGE_VARS = "merge";
    const ADD_VARS   = "add";
}