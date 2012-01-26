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
 * @package libaxiom
 * @subpackage core
 */
abstract class Axiom_BaseController {
    
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
     * Init the controller
     * @param Axiom_Request $request
     * @param Axiom_Response $response
     * @return void
     */
    public static function _init (Axiom_Request &$request, Axiom_Response &$response) {
        self::setRequest($request);
        self::setResponse($response);
    }
    
    /**
     * Set the request object
     * @param Axiom_Request $request
     * @return void
     */
    final protected static function setRequest (Axiom_Request &$request) {
        self::$_request = $request;
    }
    
    /**
     * Set the response object
     * @param Axiom_Response $response
     * @return void
     */
    final protected static function setAxiom_Response (Axiom_Response &$response) {
        self::$_response = $response;
    }
    
    /**
     * Forward the action to another controller
     * @param string $controller
     * @param string $action = "index"
     * @throws RuntimeException
     * @throws Axiom_ForwardException
     * @return void
     */
    final protected static function forward ($controller, $action = "index") {
        if (strpos($controller, 'Controller') === false)
            throw new RuntimeException("$controller is not a valid controller name", 2040);
        
        if ($controller == 'Axiom_BaseController')
            throw new RuntimeException("Redirection is impossible on $controller", 2041);
        
        if (!Axiom_Autoloader::load($controller))
            throw new BadMethodCallException("Cannot find $controller", 2042);
            
        throw new Axiom_ForwardException($controller, $action);
    }
    
    /**
     * Triggers a redirection
     * @param string $url
     * @param int $method = Axiom_RedirectException::REDIRECT_REFRESH
     * @throws InvalidArgumentException
     * @throws Axiom_RedirectException
     * @return void
     */
    final protected static function redirect ($url, $method = Axiom_RedirectException::REDIRECT_REFRESH) {
        if (!$url)
            throw new InvalidArgumentException("First parameter is expected to be a valid url", 2043);
            
        throw new Axiom_RedirectException($url, $method);
    }
}