<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Base Controller Abstract Class
 *
 * @abstract
 * @author Delespierre
 * @version $Rev$
 * @subpackage BaseController
 */
abstract class BaseController {
    
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
     * Init the controller
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public static function _init (Request &$request, Response &$response) {
        self::setRequest($request);
        self::setResponse($response);
    }
    
    /**
     * Set the request object
     * @param Request $request
     * @return void
     */
    final protected static function setRequest (Request &$request) {
        self::$_request = $request;
    }
    
    /**
     * Set the resoibse object
     * @param Response $response
     * @return void
     */
    final protected static function setResponse (Response &$response) {
        self::$_response = $response;
    }
    
    /**
     * Forward the action to another controller
     * @param string $controller
     * @param string $action = "index"
     * @throws RuntimeException
     * @throws ForwardException
     * @return void
     */
    final protected static function forward ($controller, $action = "index") {
        if (strpos($controller, 'Controller') === false)
            throw new RuntimeException("$controller is not a valid controller name", 2040);
        
        if ($controller == 'BaseController')
            throw new RuntimeException("Redirection is impossible on $controller", 2041);
        
        if (!Autoloader::load($controller))
            throw new BadMethodCallException("Cannot find $controller", 2042);
            
        throw new ForwardException($controller, $action);
    }
    
    /**
     * Triggers a redirection
     * @param string $url
     * @param int $method = RedirectException::REDIRECT_REFRESH
     * @throws InvalidArgumentException
     * @throws RedirectException
     * @return void
     */
    final protected static function redirect ($url, $method = RedirectException::REDIRECT_REFRESH) {
        if (!$url)
            throw new InvalidArgumentException("First parameter is expected to be a valid url", 2043);
            
        throw new RedirectException($url, $method);
    }
}