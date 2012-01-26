<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Router Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage core
 */
class Axiom_Router {
    
    /**
     * Internal configuration
     * @internal
     * @var array
     */
    protected static $_config;
    
    /**
     * Routes
     * @var array
     */
    protected static $_routes;

    /**
     * Axiom_Request object
     * @var Axiom_Request
     */
    protected static $_request;
    
    /**
     * Axiom_Response object
     * @var Axiom_Response
     */
    protected static $_response;
    
    /**
     * Set configuration
     * @param array $config = array
     * @return void
     */
    public static function setConfig ($config = array()) {
        
        // FIXME Not used right now !
        
        $default = array(
            'controller_path' => APPLICATION_PATH . '/controller',
        );
        self::$_config = array_merge($config, $default);
    }
    
    /**
     * Connect a route
     *
     * Routes are matched according a given template,
     * templates follow the following format:
     * "/[string|{:key<:pattern>]...
     * Eg:
     * /admin/{:controller}/{:id:\d+}/{:args}
     *
     * Three prototypes are available:
     * Axiom_Router::connect(new Route($template, $params, $options))
     * Axiom_Router::connect($template, array('controller' => 'xxx', 'action' => 'yyy' ...));
     * Axiom_Router::connect($template, 'controller::action');
     *
     * @param mixed $template The template or the objet to match the url against
     * @param mixed $params The parameters of the route (must contain at least the controller's name)
     * @param array $options Not currently used
     * @throws RuntimeException
     * @return void
     */
    public static function connect ($template, $params = array(), array $options = array()) {
        if (!is_object($template)) {
            if (is_string($params))
                $params = self::_parseParamString($params);
            
            $params += array('action' => 'index');
            $template = new Axiom_Route($template, $params, $options);
        }
        
        if ($template instanceof Axiom_Route)
            self::$_routes[] = $template;
        else
            throw new RuntimeException("Cannot connect route", 2049);
    }
    
    /**
     * Run router.
     *
     * If not route/action is given, the router
     * will match the proper route by matching
     * against the URL.
     * See Axiom_Router::connect for more information
     * about connecting routes.
     *
     * @param mixed $route = null
     * @param string $action = null
     * @throws RuntimeException
     * @return void
     */
    public static function run ($route = null, $action = null) {
        Axiom_Log::notice('--- Axiom_Router Started ---');
        
        if (!isset(self::$_request))
            self::$_request = new Axiom_Request;
            
        if (!isset(self::$_response))
            self::$_response = new Axiom_Response;
            
        if (empty($route)) {
            $route = self::_getRoute(self::$_request->url);
        }
        
        if ($route instanceof Axiom_Route) {
            $params  = $route->getParams();
            $options = $route->getOptions();
            
            if (empty($params['controller']))
                throw new RuntimeException("No controller specified");

            self::$_request->addAll($params);
            $controller = ucfirst($params['controller']);
            $action     = !empty($params['action']) ? $params['action'] : 'index';
            
            if (!empty($options['lang']))
                $lang = $options['lang'];
                
            if (!empty($params['lang']))
                $lang = $params['lang'];
            
            if (!empty($lang) && $lang != Axiom_Lang::getLocale()) {
                Axiom_ViewManager::setLayoutVar('lang', Axiom_Lang::setLocale($lang));
            }
            
            if (!empty($options['module'])) {
                try {
                    Axiom_Log::debug("Switching to module context: {$options['module']}");
                    Axiom_ModuleManager::load($options['module']);
                }
                catch (Exception $e) {
                    Axiom_Log::handleException($e);
                    return self::run('error', 'http500');
                }
            }
        }
        elseif (is_string($route)) {
            if (Axiom_ModuleManager::exists($route)) {
                Axiom_Log::debug("Switching to module context: {$route}");
                Axiom_ModuleManager::load($route);
            }
            
            $controller = ucfirst($route);
            $action     = !empty($action) ? $action : 'index';
        }
        else {
            list($controller, $action) = array('ErrorController', 'http404');
        }
        
        if (strpos(strtolower($controller), 'controller') === false)
            $controller .= 'Controller';
        
        if (!Axiom_Autoloader::load($controller))
            list($controller, $action) = array('ErrorController', 'http404');
        
        self::load($controller, $action);
    }
    
    /**
     * Load the given controller and the given action
     * @param string $controller
     * @param string $action = null
     * @throws BadMethodCallException
     * @return void
     */
    public static function load ($controller, $action = null) {
        if (empty($action))
            $action = "index";
            
        try {
            call_user_func_array(array($controller, '_init'), array(&self::$_request, &self::$_response));
            if (!is_callable(array($controller, $action)))
                throw new BadMethodCallException("No such action for $controller", 2003);
            Axiom_Log::debug("Invoke: {$controller}::{$action}");
            self::$_response->addAll(call_user_func(array($controller, $action)));
        }
        catch (BadMethodCallException $e) {
            Axiom_Log::debug("BadMethodCallException caught by router: " . $e->getMessage());
            return self::run("error", "http404");
        }
        catch (Axiom_LoginException $e) {
            Axiom_Log::debug("Axiom_LoginException caught by router: " . $e->getMessage());
            return self::run("error", "http403");
        }
        catch (Axiom_ForwardException $e) {
            Axiom_Log::debug("Axiom_ForwardException caught by router");
            return self::load($e->getController(), $e->getAction());
        }
        catch (Axiom_RedirectException $e) {
            Axiom_Log::debug("Axiom_RedirectException caught by router");
            return self::redirect($e);
        }
        catch (Exception $e) {
            Axiom_Log::handleException($e);
            if ($code = $e->getCode())
                self::$_response->error_code = $code;
            return self::run("error", "http500");
        }
        
        Axiom_ViewManager::setResponse(self::$_response);
        try {
            Axiom_ViewManager::load($controller, $action);
        }
        catch (Exception $e) {
            Axiom_Log::handleException($e);
            if ($code = $e->getCode())
                self::$_response->error_code = $code;
            return self::run("error", "http500");
        }
    }
    
    /**
     * Handler for Axiom_RedirectException.
     *
     * Will send the proper header to the
     * browser and optionnaly load the
     * ErrorController::redirection view.
     *
     * @return void
     */
    public static function redirect (Axiom_RedirectException $exception) {
        header((string)$exception);
        
        if ($exception->getMethod() == Axiom_RedirectException::REDIRECT_REFRESH) {
            self::$_response->addAll(array('url' => $exception->getUrl()));
            self::load('ErrorController', 'redirection');
        }
    }
    
    /**
     * Parses a route param string into an array.
     *
     * This method allows the Routes to be connected
     * by providing strings as parameters instead of
     * array for practical reasons.
     * Thse calls are identicals:
     * * Axiom_Router::connect('/a/b', 'FooController::bar');
     * * Axiom_Router::connect('/a/b', array('controller' => 'FooController', 'action' => 'bar'));
     *
     * @internal
     * @param string $params
     * @return array
     */
    protected static function _parseParamString ($params) {
        list($controller, $action) = (strpos($params, '::') !== false) ? explode('::', $params) : array($params, 'index');
        return array('controller' => $controller, 'action' => $action);
    }
    
    /**
     * Get the route instance that matches the given URL.
     * @internal
     * @param string $url
     */
    protected static function _getRoute ($url) {
        foreach (self::$_routes as $route) {
            if ($params = $route->match($url)) {
                Axiom_Log::debug("Choosen route: " . $route->getTemplate() . " [params] " . json_encode($route->getParams()));
                return $route;
            }
        }
        return false;
    }
}