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
    
    protected $_viewPaths;
    protected $_outputCallback;
    protected $_layoutVars;
    
    public function __construct ($view_paths = null) {
        $this->_viewPaths      = (array)$view_paths;
        $this->_outputCallback = false;
        $this->_layoutVars     = array();
    }
    
    public function add ($view) {
        if (!$this->_viewPaths[] = realpath($view));
            throw new axMissingFileException($view);
            
        return $this;
    }
    
    public function load () {
        $args = func_get_args();
        if (!count($args))
            return false;
        
        if (count($args) == 1 && $args[0] instanceof axResponse) {
            /**
             * @var axResponse
             */
            $response = $args[0];
            
            $view    = $response->getView();
            $section = $response->getSection();
            $format  = $response->getFormat();
            
            if (!is_file($path = $this->_findView($section, $view, $format)))
                return false;
        }
        elseif (count($args) == 1 && is_file($args[0])) {
            $path = $args[0];
        }
        else {
            list($section,$view,$format) = $args + array('','','html');
            
            if (!is_file($path = $this->_findView($section, $view, $format)))
                return false;
        }
        
        // TODO continue here
    }
    
    public function setOutputCallback ($alpha) {
        if (!is_callable($this->_outputCallback = $alpha))
            throw new InvalidArgumentException("Provided callback is not callable");
            
        return $this;
    }
    
    public function setOutputFormat ($format) {
        switch (strtolower($format)) {
            case 'html': header('Content-Type: text/html; charset=UTF-8'); break;
            case 'json': header('Content-Type: application/json; charset=UTF-8'); break;
            case 'csv' : header('Content-Type: application/csv; charset=UTF-8'); break;
            case 'xml' : header('Content-Type: text/xml; charset=UTF-8'); break;
            case 'text': header('Content-Type: text/plain; charset=UTF-8'); break;
            default: throw new RuntimeException("Unrecognized format {$format}");
        }
        
        return $this;
    }
    
    public function setLayout ($layout, $format = null) {
        if ($layout === false) {
            $this->_layout = false;
            return $this;
        }
        
        if (!is_file($file = $layout)
         && !is_file($file = $this->_findLayout($layout, $format)))
            throw new RuntimeException("Unable to find layout {$layout}");
            
        $this->_layout = $file;
        return $this;
    }
    
    public function getVar ($name) {
        return isset($this->_layoutVars[$name]) ? $this->_layoutVars[$name] : null;
    }
    
    public function setVar ($name, $value) {
        $this->_layoutVars[$name] = $value;
        return $this;
    }
    
    public function __get ($key) {
        return $this->getVar($key);
    }
    
    public function __set ($key, $value) {
        $this->setVar($key, $value);
    }
    
    public function __isset ($key) {
        return isset($this->_layoutVars[$key]);
    }
    
    public function __unset ($key) {
        unset($this->_layoutVars[$key]);
    }
    
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
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // OLD -------------------------------------------------------------------------------------------------------------
    
    /**
     * Internal configuration
     * @internal
     * @var array
     */
    protected static $_config = array();
    
    /**
     * Layout vars
     * @internal
     * @var array
     */
    protected static $_layout_vars = array();
    
    /**
     * axResponse object
     * @internal
     * @var axResponse
     */
    protected static $_response;
    
    /**
     * Load and display the controller / action associated view
     * @param string $controller
     * @param string $action
     * @return void
     */
    public static function load ($controller, $action) {
        foreach (self::$_response->getHeaders() as $header)
            header($header);
        
        self::setContentType($__format = self::$_response->getOutputFormat());
        
        $__section  = strtolower(str_replace('Controller', '', $controller));
        $__view     = strtolower(($v = self::$_response->getResponseView()) ? $v : $action);
        $__filename = self::getViewFilePath($__section, $__view, $__format);
        
        if (!$__filename) {
            axLog::warning("No view defined for {$__section}/{$__view} with format {$__format}");
            return;
        }
        
        try {
            ob_start();
            
            extract(self::$_layout_vars);
            extract(self::$_response->getResponseVars());
            foreach (self::$_response->getMessages() as $level => $messages)
                ${$level} = $messages;

            include $__filename;
            
            ${'page_content'} = ob_get_contents();
            ob_end_clean();
        }
        catch (Exception $e) {
            ob_end_clean();
            if (PHP_VERSION_ID >= 50300)
                throw new RuntimeException("Exception during view loading", 2004, $e);
            else
                throw new RuntimeException("Exception during view loading", 2004);
        }
        
        if (self::$_response->layout()) {
            if ($__layout = self::getLayoutFilePath($__format))
                include $__layout;
        }
        else {
            echo ${'page_content'};
        }
    }
    
	/**
     * Set the configuration
     * @param array $configuration = array()
     * @return void
     */
    public static function setConfig ($configuration = array()) {
        $default = array(
            'default_output_format' => 'html',
            'view_paths'            => array(AXIOM_APP_PATH . "/view"),
            'layout_file'           => 'default',
            'layout_content_var'    => 'page_content',
        );
        self::$_config = array_merge($default, $configuration);
    }
    
    /**
     * Set the header for the given format
     * @param string $output_format = null
     * @throws RuntimeException
     * @return void
     */
    public static function setContentType ($output_format = null) {
        if (!$output_format)
            $output_format = self::$_config['default_output_format'];

        switch (strtolower($output_format)) {
            case 'html': header('Content-Type: text/html; charset=UTF-8'); break;
            case 'json': header('Content-Type: application/json; charset=UTF-8'); break;
            case 'csv' : header('Content-Type: application/csv; charset=UTF-8'); break;
            case 'xml' : header('Content-Type: text/xml; charset=UTF-8'); break;
            case 'text': header('Content-Type: text/plain; charset=UTF-8'); break;
            default: throw new RuntimeException("Format $output_format not recognized", 2005);
        }
    }
    
    /**
     * Set layout file
     * @param string $file
     * @return void
     */
    public static function setLayoutFile ($file) {
        self::$_config['layout_file'] = $file;
    }
    
    /**
     * Get the layout file path.
     *
     * Will return the absolute path
     * of the proper layout or false
     * if such layout doesn't exists.
     *
     * @internal
     * @param string $format
     * @return string
     */
    protected static function getLayoutFilePath ($format = "html") {
        return self::getViewFilePath('layouts', self::$_config['layout_file'], $format);
    }
    
    /**
     * Add layout vars at once
     * @param array $collection
     * @return void
     */
    public static function addLayoutVars ($collection) {
        if (!empty($collection))
            self::$_layout_vars = array_merge(self::$_layout_vars, (array)$collection);
    }
    
    /**
     * Get layout vars
     * @return array
     */
    public static function getLayoutVars () {
        return self::$_layout_vars;
    }
    
    /**
     * Get the given layout var
     * @param string $key
     * @return mixed
     */
    public static function getLayoutVar ($key) {
        return isset(self::$_layout_vars[$key]) ? self::$_layout_vars[$key] : null;
    }
    
    /**
     * Set the given layout var
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setLayoutVar ($key, $value) {
        self::$_layout_vars[$key] = $value;
    }
    
    /**
     * Add a view path
     * @param string $path
     * @throws axMissingFileException if $path is not a valid directory
     * @return void
     */
    public static function addPath ($path) {
        if (is_dir($path))
            self::$_config['view_paths'][] = $path;
        else
            throw new axMissingFileException($path, 2006);
    }
    
    /**
     * Gets the view file.
     *
     * Will retrun the absolute path
     * of the view file or false if such
     * file doesn't exists.
     *
     * @internal
     * @param string $section
     * @param string $view
     * @param string $format
     * @return string
     */
    protected static function getViewFilePath ($section, $view, $format) {
        foreach (array_unique(self::$_config['view_paths']) as $vpath) {
            if (is_file($path = realpath($vpath) . "/{$section}/{$view}.{$format}.php"))
                return $path;
        }
        return false;
    }
    
    /**
     * Set the internal response object
     * @param Response $response
     * @return void
     */
    public static function setResponse (axResponse &$response) {
        self::$_response = $response;
    }
    
	/**
     * Load a view specified by $section and $view
     * in a buffer and return it.
     *
     * Will return false if an error was
     * encountered in the loaded view.
     *
     * Note: this method is intended for specific
     * purposes only. DO NOT chain partials
     * nor call partial everytime for practical
     * reasons because this method can be considered
     * as heavily demanding.
     *
     * @param string $section
     * @param string $view
     * @param string $format = "html"
     * @return string
     */
    public static function partial ($section, $view, $format = "html") {
        if (!$__filename = self::getViewFilePath($section, $view, $format)) {
            axLog::warning("Partial loading failed: no view for {$section}/{$view} with format {$format}");
            return false;
        }
        
        try {
            extract(self::$_layout_vars);
            extract(self::$_response->getResponseVars());
            ob_start();
            include $__filename;
            $buffer = ob_get_contents();
            ob_end_clean();
        }
        catch (Exception $e) {
            ob_end_clean();
            axLog::handleException($e);
            return false;
        }
        return $buffer;
    }
}

/**
 * Non PHP-doc
 * @see axViewManager::partial
 */
function partial ($section, $view, $format = "html") {
    return axViewManager::partial($section, $view, $format);
}

/**
 * Format URL
 * @param string $url
 * @param string $lang = false
 * @return string
 */
function url ($url, $lang = false) {
    if (!$lang)
        $lang = axLang::getLocale();
        
    $url = trim($url, '/');
    return rtrim(getenv("REWRITE_BASE"), '/') . "/$lang/$url";
}

/**
 * Format src
 * @param string $ressource
 * @return string
 */
function src ($ressource) {
    $ressource = trim($ressource, '/');
    return rtrim(getenv("REWRITE_BASE"), '/') . "/$ressource";
}