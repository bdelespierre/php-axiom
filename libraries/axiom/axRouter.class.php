<?php
/**
 * @brief Router class file
 * @file axRouter.class.php
 */

/**
 * @brief Router and dispatcher Class
 * 
 * @todo axRouter long description
 * @warning This class rely on the Axiom class for locale and module settings.
 * @class axRouter
 * @author Delespierre
 * @package libaxiom
 * @subpackage core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axRouter {
    
    /**
     * @brief Routes
     * @property array $_routes
     */
    protected static $_routes;

    /**
     * @brief axRequest object
     * @property axRequest $_request
     */
    protected static $_request;
    
    /**
     * @brief axResponse object
     * @property axResponse $_response
     */
    protected static $_response;
    
    /**
     * @brief Connect a route
     *
     * Routes are matched according to a given template. Ttemplates follow the following format:
     * @code
     * /[string|{:key<:pattern><:?>}]/...
     * @endcode
     * 
     * Examples of valid templates:
     * @code
     * /a/b/c
     * /{:controller}/{:action}
     * /articles/{:id:\d+}
     * /{:lang:\w{2}:?}/{:controller}/{:action}
     * @endcode
     * 
     * @todo Describe completely the route management here
     *
     * Three prototypes are available:
     * @li axRouter::connect(new Route($template, $params, $options))
     * @li axRouter::connect($template, array('controller' => 'xxx', 'action' => 'yyy' ...), $options);
     * @li axRouter::connect($template, 'controller::action', $options);
     *
     * @param mixed $template The template or the objet to match the url against
     * @param mixed $params @optional @default{array()} The parameters of the route (must contain at least the 
     * controller's name), you may leave it blank if your template catches the controller's name
     * @param array $options @optional @default{array()} The route options
     * @throws RuntimeException If the route cannot be connected (not instance of axRouter)
     * @return void
     */
    public static function connect ($template, $params = array(), array $options = array()) {
        if (!is_object($template)) {
            if (is_string($params))
                $params = self::_parseParamString($params);
            
            $params  += array('action' => 'index');
            $template = new axRoute($template, $params, $options);
        }
        
        if ($template instanceof axRoute)
            self::$_routes[] = $template;
        else
            throw new RuntimeException("Cannot connect route", 2049);
    }
    
    /**
     * @brief Run router
     *
     * If not route/action is given, the router will determine the route based on the URL. See axRouter::connect() for 
     * more information about connecting routes.
     * 
     * @todo Describe axRouter::run behavior
     *
     * @static
     * @param mixed $route @optional @default{null}
     * @param string $action @optional @default{null}
     * @throws RuntimeException If no controller is found in the route
     * @return void
     */
    public static function run ($route = null, $action = null) {
        if (!isset(self::$_request))
            self::$_request = new axRequest;
            
        if (!isset(self::$_response))
            self::$_response = new axResponse;
            
        if (empty($route))
            $route = self::_getRoute(self::$_request->url);
        
        if ($route instanceof axRoute) {
            $params  = $route->getParams();
            $options = $route->getOptions();
            
            if (empty($params['controller']))
                throw new RuntimeException("No controller specified");

            self::$_request->add($params);
            $controller = ucfirst($params['controller']);
            $action     = !empty($params['action']) ? $params['action'] : 'index';
            
            if (!empty($options['lang']))
                $lang = $options['lang'];
                
            if (!empty($params['lang']))
                $lang = $params['lang'];
            
            if (!empty($lang) && Axiom::locale() && $lang != Axiom::locale()->getLang())
                Axiom::locale()->setLang($lang);
            
            if (!empty($options['module'])) {
                try {
                    Axiom::module()->load($options['module']);
                }
                catch (Exception $e) {
                    return self::run('error', 'http500');
                }
            }
        }
        elseif (is_string($route)) {
            if (Axiom::module()->exists($route))
                Axiom::module()->load($route);
            
            $controller = ucfirst($route);
            $action     = !empty($action) ? $action : 'index';
        }
        else {
            list($controller, $action) = array('ErrorController', 'http404');
        }
        
        if (strpos(strtolower($controller), 'controller') === false)
            $controller .= 'Controller';
        
        if (!class_exists($controller, true))
            list($controller, $action) = array('ErrorController', 'http404');
        
        self::_load($controller, $action);
    }
    
    /**
     * @brief Invoke the given controller / action and load the corresponding view
     * 
     * @todo Describe axRouter::load behavior
     * 
     * @static
     * @param string $controller
     * @param string $action @optional @default{null} If null will use the @c index action
     * @return void
     */
    protected static function _load ($controller, $action = null) {
        if (empty($action))
            $action = "index";
            
        try {
            call_user_func_array(array($controller, '_init'), array(&self::$_request, &self::$_response));
            if (!is_callable(array($controller, $action)))
                throw new BadMethodCallException("No such action for $controller", 2003);
            self::$_response->add(call_user_func(array($controller, $action)));
        }
        catch (BadMethodCallException $e) {
            return self::run("error", "http404");
        }
        catch (axLoginException $e) {
            return self::run("error", "http403");
        }
        catch (axForwardException $e) {
            return self::load($e->getController(), $e->getAction());
        }
        catch (axRedirectException $e) {
            return self::_redirect($e);
        }
        catch (Exception $e) {
            if ($code = $e->getCode())
                self::$_response->error_code = $code;
            return self::run("error", "http500");
        }
        
        if (!self::$_response->getViewSection()) {
            $section = strtolower($controller);
            $section = ($offset = strpos($section, 'controller')) !== false ? 
                substr($section, 0, $offset): 
                $section;
            self::$_response->setViewSection($section);
        }
        
        if (!self::$_response->getView()) {
            $view = strtolower($action);
            self::$_response->setView($view);
        }
        
        try {
            echo Axiom::view()->load(self::$_response);
        }
        catch (Exception $e) {
            self::$_response->reset();
            if ($code = $e->getCode())
                self::$_response->error_code = $code;
            return self::run("error", "http500");
        }
    }
    
    /**
     * @brief Handler for axRedirectException.
     *
     * Will send the proper header to the browser and optionnaly load the ErrorController::redirection view.
     *
     * @static
     * @internal
     * @return void
     */
    protected static function _redirect (axRedirectException $exception) {
        header((string)$exception);
        
        if ($exception->getMethod() == axRedirectException::REDIRECT_REFRESH) {
            self::$_response->addAll(array('url' => $exception->getUrl()));
            self::load('ErrorController', 'redirection');
        }
    }
    
    /**
     * @brief Parses a route param string into an array.
     *
     * This method allows the Routes to be connected by providing strings as parameters instead of array for practical 
     * reasons.
     * These calls are identicals:
     * @li axRouter::connect('/a/b', 'FooController::bar');
     * @li axRouter::connect('/a/b', array('controller' => 'FooController', 'action' => 'bar'));
     *
     * @static
     * @param string $params
     * @return array
     */
    protected static function _parseParamString ($params) {
        list($controller, $action) = (strpos($params, '::') !== false) ? explode('::', $params) : array($params, 'index');
        return array('controller' => $controller, 'action' => $action);
    }
    
    /**
     * @brief Get the route instance that matches the given URL.
     * @internal
     * @param string $url
     */
    protected static function _getRoute ($url) {
        foreach (self::$_routes as $route) {
            if ($params = $route->match($url)) {
                return $route;
            }
        }
        return false;
    }
}