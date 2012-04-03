<?php
/**
 * @brief Router class file
 * @file axRouter.class.php
 */

/**
 * @brief Router and dispatcher Class
 *
 * @todo axRouter long description
 * @todo Add route caching
 *
 * @class axRouter
 * @author Delespierre
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axRouter {
    
    /**
     * @brief Max number of 'bounces'
     * @see axRouter::_bounce
     * @property integer $_bounces
     */
    protected $_bounces = 25;
    
    /**
     * @brief Logging object
     * @property axLog $_log
     */
    protected $_log;
    
    /**
     * @brief Locale object
     * @property axLocale $_locale
     */
    protected $_locale;
    
    /**
     * @brief Module manager object
     * @property axModuleManager $_module
     */
    protected $_module;
    
    /**
     * @brief View manager object
     * @property axViewManager $_view
     */
    protected $_view;
    
    /**
     * @brief Routes
     * @property array $_routes
     */
    protected $_routes;

    /**
     * @brief axRequest object
     * @property axRequest $_request
     */
    protected $_request;
    
    /**
     * @brief axResponse object
     * @property axResponse $_response
     */
    protected $_response;
    
    /**
     * @brief Constructor
     *
     * @todo Add route caching
     *
     * @param axViewManager The view manager
     * @param axLog $log @optional @default{null} The log object to be used for reporting
     * @param axLocale $locale @optional @default{null} The locale object to be used
     * @param axModuleManager $module_manger @optional @default{null}
     * @param array $routes @optional @default{array()} The default routes to be added
     */
    public function __construct (axViewManager   $view_manager,
                                 axLog           $log               = null,
                                 axLocale        $locale            = null,
                                 axModuleManager $module_manager    = null,
                                 array           $routes            = array()) {
        if (!$view_manager)
            throw new InvalidArgumentException("You must provide an instance of view manager to router constructor");
        
        $this->_view   = $view_manager;
        $this->_log    = $log;
        $this->_locale = $locale;
        $this->_module = $module_manager;
        $this->_routes = array_filter($routes, callback('function ($i) { return $i instanceof axRoute; }'));
    }
    
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
    public function connect ($template, $params = array(), array $options = array()) {
        if (!is_object($template)) {
            if (is_string($params))
                $params = self::_parseParamString($params);
            
            $params  += array('action' => 'index');
            $template = new axRoute($template, $params, $options);
        }
        
        if ($template instanceof axRoute)
            $this->_routes[] = $template;
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
     * @param mixed $route @optional @default{null}
     * @param string $action @optional @default{null}
     * @throws RuntimeException If no controller is found in the route
     * @return void
     */
    public function run ($route = null, $action = null) {
        if (!isset($this->_request))
            $this->_request = new axRequest;
            
        if (!isset($this->_response))
            $this->_response = new axResponse;
        
        if (empty($route))
            $route = $this->_getRoute($this->_request->url);
        
        if ($route instanceof axRoute) {
            $params  = $route->getParams();
            $options = $route->getOptions();
            
            if (empty($params['controller'])) {
                $this->_log->error("No controller specified for this route");
                return $this->_load('ErrorController', 'http500');
            }

            $this->_request->add($params);
            $controller = ucfirst($params['controller']);
            $action     = !empty($params['action']) ? $params['action'] : 'index';
            
            if (!empty($options['lang']))
                $lang = $options['lang'];
                
            if (!empty($params['lang']))
                $lang = $params['lang'];
            
            if (!empty($lang) && $this->_locale && $lang != $this->_locale->getLang())
                $this->_locale->setLang($lang);
            
            if (!empty($options['module'])) {
                try {
                    $this->_module->load($options['module']);
                }
                catch (Exception $e) {
                    $this->_log->handleException($e);
                    return $this->load('ErrorController', 'http500');
                }
            }
        }
        elseif (is_string($route)) {
            if ($this->_module->exists($route))
                $this->_module->load($route);
            
            $controller = ucfirst($route);
            $action     = !empty($action) ? $action : 'index';
        }
        else {
            $this->_log->debug("No route identified");
            list($controller, $action) = array('ErrorController', 'http404');
        }
        
        if (strpos(strtolower($controller), 'controller') === false)
            $controller .= 'Controller';
        
        if (!class_exists($controller, true)) {
            $this->_log->debug("No such controller $controller");
            list($controller, $action) = array('ErrorController', 'http404');
        }
        
        $this->_load($controller, $action);
    }
    
    /**
     * @brief Invoke the given controller / action and load the corresponding view
     *
     * @todo Describe axRouter::load behavior
     *
     * @param string $controller
     * @param string $action @optional @default{null} If null will use the @c index action
     * @return void
     */
    protected function _load ($controller, $action = null) {
        if (empty($action))
            $action = "index";
        
        $this->_bounce();
        $this->_log->debug("Loading $controller::$action");
        try {
            call_user_func_array(array($controller, '_init'), array(&$this->_request, &$this->_response));
            if (!is_callable(array($controller, $action)))
                throw new BadMethodCallException("No such action $action for $controller");
            $this->_response->add(call_user_func(array($controller, $action)));
        }
        catch (BadMethodCallException $e) {
            $this->_log->handleException($e);
            return $this->load("ErrorController", "http404");
        }
        catch (axLoginException $e) {
            return $this->load("ErrorController", "http403");
        }
        catch (axForwardException $e) {
            return $this->_load($e->getController(), $e->getAction());
        }
        catch (axRedirectException $e) {
            return $this->_redirect($e);
        }
        catch (Exception $e) {
            $this->_log->handleException($e);
            if ($code = $e->getCode())
                $this->_response->error_code = $code;
            return $this->load("ErrorController", "http500");
        }
        
        if (!$this->_response->getViewSection()) {
            $section = strtolower($controller);
            $section = ($offset = strpos($section, 'controller')) !== false ?
                substr($section, 0, $offset):
                $section;
            $this->_response->setViewSection($section);
        }
        
        if (!$this->_response->getView()) {
            $view = strtolower($action);
            $this->_response->setView($view);
        }
        
        $this->_log->debug("Loading view " . $this->_response->getViewSection() . '/' . $this->_response->getView());
        try {
            echo $this->_view->load($this->_response);
        }
        catch (Exception $e) {
            $this->_log->handleException($e);
            $this->_response->reset();
            if ($code = $e->getCode())
                $this->_response->error_code = $code;
            return $this->load("ErrorController", "http500");
        }
    }
    
    /**
     * @brief Handler for axRedirectException.
     *
     * Will send the proper header to the browser and optionnaly load the ErrorController::redirection view.
     *
     * @internal
     * @return void
     */
    protected function _redirect (axRedirectException $exception) {
        header((string)$exception);
        
        if ($exception->getMethod() == axRedirectException::REDIRECT_REFRESH) {
            $this->_response->addAll(array('url' => $exception->getUrl()));
            $this->load('ErrorController', 'redirection');
        }
    }
    
    /**
     * @brief Get the route instance that matches the given URL.
     * @internal
     * @param string $url
     */
    protected function _getRoute ($url) {
        foreach ($this->_routes as $route) {
            if ($params = $route->match($url)) {
                $this->_log->debug("Requested ressource: " . $this->_request->getServer('REQUEST_URI'))
                           ->debug("Identified route "     . $route->getTemplate());
                return $route;
            }
        }
        return false;
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
     * @brief Stuck in a loop security
     *
     * This method allows a maximum number of bounces to occur before the application stop, preventing the application
     * from bouncing until the max execution time is reached.
     *
     * This method is intended to provide security for the axRouter::load method. Each time the axRouter::load method
     * is invoked for a single request, the 'bouncing' counter is decreased. If it reaches zero, the application is
     * stopped.
     *
     * For example, suppose that one has unproperly set an route/action that forwards on itself, the load method
     * would then be stuck in a loop (invoke > forward > invoke > forward and so on), it's just like being stuck in
     * portals. The axRouter::_bounce method prevent that from happening.
     *
     * @return void
     */
    protected function _bounce () {
        if (($this->_bounces--) == 0) {
            $this->_log->error("CRITICAL ERROR: load loop detected, exiting");
            exit('Stuck in a loop');
        }
    }
}