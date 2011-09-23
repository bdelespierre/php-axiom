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
 * @version $Rev$
 * @subpackage Router
 */
class Router {
    
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
     * Request object
     * @var Request
     */
    protected static $_request;
    
    /**
     * Response object
     * @var Response
     */
    protected static $_response;
    
    /**
     * Set configuration
     * @param array $config = array
     * @return void
     */
    public static function setConfig ($config = array()) {
        $default = array(
            'controller_path' => dirname(dirname(__FILE__)) . '/application/controller',
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
     * Router::connect(new Route($template, $params, $options))
     * Router::connect($template, array('controller' => 'xxx', 'action' => 'yyy' ...));
     * Router::connect($template, 'controller::action');
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
            $template = new Route($template, $params, $options);
        }
        
        if ($template instanceof Route)
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
     * See Router::connect for more information
     * about connecting routes.
     *
     * @param mixed $route = null
     * @param string $action = null
     * @throws RuntimeException
     * @return void
     */
    public static function run ($route = null, $action = null) {
        Log::notice('--- Router Started ---');
        
        if (!isset(self::$_request))
            self::$_request = new Request;
            
        if (!isset(self::$_response))
            self::$_response = new Response;
            
        if (empty($route)) {
            $route = self::_getRoute(self::$_request->url);
        }
        
        if ($route instanceof Route) {
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
            
            if (!empty($lang) && $lang != Lang::getLocale()) {
                ViewManager::setLayoutVar('lang', Lang::setLocale($lang));
            }
            
            if (!empty($options['module'])) {
                try {
                    ModuleManager::load($options['module']);
                }
                catch (Exception $e) {
                    Log::handleException($e);
                    return self::run('error', 'http500');
                }
            }
        }
        elseif (is_string($route)) {
            if (ModuleManager::exists($route))
                ModuleManager::load($route);
            
            $controller = $route;
            $action     = !empty($action) ? $action : 'index';
        }
        else {
            list($controller, $action) = array('ErrorController', 'http404');
        }
        
        if (strpos(strtolower($controller), 'controller') === false)
            $controller .= 'Controller';
        
        if (!Autoloader::load($controller))
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
            
            self::$_response->addAll(call_user_func(array($controller, $action)));
        }
        catch (BadMethodCallException $e) {
            Log::debug("BadMethodCallException catched by router: " . $e->getMessage());
            return self::run("error", "http404");
        }
        catch (LoginException $e) {
            Log::debug("LoginException catched by router: " . $e->getMessage());
            return self::run("error", "http403");
        }
        catch (ForwardException $e) {
            Log::debug("Forward catched by router");
            return self::load($e->getController(), $e->getAction());
        }
        catch (RedirectException $e) {
            Log::debug("RedirectException catched by router");
            return self::redirect($e);
        }
        catch (Exception $e) {
            Log::handleException($e);
            if ($code = $e->getCode())
                self::$_response->error_code = $code;
            return self::run("error", "http500");
        }
        
        ViewManager::setResponse(self::$_response);
        try {
            ViewManager::load($controller, $action);
        }
        catch (Exception $e) {
            Log::handleException($e);
            if ($code = $e->getCode())
                self::$_response->error_code = $code;
            return self::run("error", "http500");
        }
    }
    
    public static function redirect (RedirectException $exception) {
        header((string)$exception);
        
        if ($exception->getMethod() == RedirectException::REDIRECT_REFRESH) {
            self::$_response->addAll(array('url' => $exception->getUrl()));
            self::load('ErrorController', 'redirection');
        }
    }
    
    protected static function _parseParamString ($params) {
        list($controller, $action) = (strpos($params, '::') !== false) ? explode('::', $params) : array($params, 'index');
        return array('controller' => $controller, 'action' => $action);
    }
    
    protected static function _getRoute ($url) {
        foreach (self::$_routes as $route) {
            if (!$params = $route->match($url))
                continue;
            
            Log::debug("Selected Route: " . $route->getTemplate());
            return $route;
        }
        return false;
    }
}