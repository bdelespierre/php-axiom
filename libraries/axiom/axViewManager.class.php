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
    
    const MERGE_VARS = "merge";
    const ADD_VARS   = "add";
    
    protected $_viewPaths;
    protected $_outputCallback;
    protected $_layout;
    protected $_defaultFormat;
    protected $_layoutVars;
    
    public function __construct ($layout, $view_paths = null, $default_format = 'html', array $layout_vars = array()) {
        $this->_viewPaths      = (array)$view_paths;
        $this->_outputCallback = false;
        $this->_layoutVars     = $layout_vars;
        $this->_layout         = $layout ? $layout : false;
        $this->_defaultFormat  = $default_format;
    }
    
    public function add ($view) {
        if (!$this->_viewPaths[] = realpath($view));
            throw new axMissingFileException($view);
        
        return $this;
    }
    
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
    
    public function setOutputCallback ($callback) {
        if (!is_callable($this->_outputCallback = $callback))
            return false;
        
        return $this;
    }
    
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
    
    public function setLayout ($layout, $format = null) {
        $this->_layout = $layout;
        return $this;
    }
    
    public function getVar ($name) {
        return isset($this->_layoutVars[$name]) ? $this->_layoutVars[$name] : null;
    }
    
    public function setVar ($name, $value) {
        $this->_layoutVars[$name] = $value;
        return $this;
    }
    
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
    
    protected function _loadView ($__path, $__vars) {
        if (!ob_start())
            return false;
        
        extract($this->_layoutVars, EXTR_PREFIX_ALL, 'layout');
        extract($__vars);
        
        if (!include $__path)
            return false;
        
        return ob_get_clean();
    }
    
    protected function _loadLayout ($__path, $__vars) {
        if (!ob_start())
            return false;
            
        extract($this->_layoutVars, EXTR_PREFIX_ALL, 'layout');
        extract($__vars);
        
        if (!include $__path)
            return false;
        
        ${'layout_content'} = ob_get_clean();
        return ${'layout_content'};
    }
}