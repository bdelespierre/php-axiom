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
 * @author Delespierre
 * @version $Rev$
 * @subpackage ViewManager
 */
class ViewManager {
    
    /**
     * Internal configuration
     * @internal
     * @var array
     */
    protected static $_config = array();
    
    /**
     * Layout vars
     * @var array
     */
    protected static $_layout_vars = array();
    
    /**
     * Response object
     * @var Response
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
        self::setContentType($format = ($f = self::$_response->getOutputFormat()) ? $f : self::$_config['default_output_format']);
        
        $section = strtolower(str_replace('Controller', '', $controller));
        $view = strtolower(($v = self::$_response->getResponseView()) ? $v : $action);
        
        if (file_exists($__filename = realpath(self::$_config['view_path']) . "/{$section}/{$view}.{$format}.php")) { }
        elseif (file_exists($__filename = realpath(self::$_config['default_view_path']) . "/{$section}/{$view}.{$format}.php")) { }
        else return;
        
        try {
            ob_start();
            
            extract(self::$_layout_vars);
            extract(self::$_response->getResponseVars());
            foreach (self::$_response->getMessages() as $level => $messages)
                ${$level} = $messages;

            include $__filename;
            
            ${self::$_config['layout_content_var']} = ob_get_contents();
            ob_end_clean();
        }
        catch (Exception $e) {
            ob_end_clean();
            if (PHP_VERSION_ID >= 50300)
                throw new RuntimeException("Exception during view loading", 2004, $e);
            else
                throw new RuntimeException("Exception during view loading", 2004);
        }
        
        if (self::$_response->layout())
            include self::getLayoutFilePath($format);
        else
            echo ${self::$_config['layout_content_var']};
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
     * Set the configuration
     * @param array $configuration = array()
     * @return void
     */
    public static function setConfig ($configuration = array()) {
        $default = array(
            'default_output_format' => 'html',
            'default_view_path' => dirname(dirname(__FILE__)) . "/application/view",
            'view_path' => dirname(dirname(__FILE__)) . "/application/view/",
            'layout_file' => 'default',
            'layout_content_var' => 'page_content',
        );
        self::$_config = array_merge($default, $configuration);
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
     * Get the layout file path
     * @retunr string
     */
    public static function getLayoutFilePath ($format = "html") {
        return dirname(dirname(__FILE__)) . "/application/view/layouts/" . self::$_config['layout_file'] . ".{$format}.php";
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
    
    public static function setViewPath ($path) {
        if (file_exists($path)) {
            self::$_config['view_path'] = $path;
        }
        else
            throw new MissingFileException($path, 2006);
    }
    
    /**
     * Set the internal response object
     * @param Response $response
     * @return void
     */
    public static function setResponse (Response &$response) {
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
     * reasons for this method can be considered
     * as heavily demanding.
     *
     * @param string $section
     * @param string $view
     * @param string $format = "html"
     * @return string
     */
    public static function partial ($section, $view, $format = "html") {
        if (file_exists($__filename = realpath(self::$_config['view_path']) . "/{$section}/{$view}.{$format}.php")) { }
        elseif (file_exists($__filename = realpath(self::$_config['default_view_path']) . "/{$section}/{$view}.{$format}.php")) { }
        else return false;
        
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
            return false;
        }
        return $buffer;
    }
}

/**
 * Non PHP-doc
 * @see ViewManager::partial
 */
function partial ($section, $view, $format = "html") {
    return ViewManager::partial($section, $view, $format);
}