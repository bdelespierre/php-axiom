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
abstract class axBaseController {
    
    /**
     * axRequest object
     * @var axRequest
     */
    protected static $_request;
    
    /**
     * axResponse object
     * @var axResponse
     */
    protected static $_response;
    
    /**
     * Init the controller
     * @param axRequest $request
     * @param axResponse $response
     * @return void
     */
    public static function _init (axRequest &$request, axResponse &$response) {
        self::setRequest($request);
        self::setResponse($response);
    }
    
    /**
     * Set the request object
     * @param axRequest $request
     * @return void
     */
    final protected static function setRequest (axRequest &$request) {
        self::$_request = $request;
    }
    
    /**
     * Set the response object
     * @param axResponse $response
     * @return void
     */
    final protected static function setResponse (axResponse &$response) {
        self::$_response = $response;
    }
    
    /**
     * Forward the action to another controller
     * @param string $controller
     * @param string $action = "index"
     * @throws RuntimeException
     * @throws axForwardException
     * @return void
     */
    final protected static function forward ($controller, $action = "index") {
        if (strpos($controller, 'Controller') === false)
            throw new RuntimeException("$controller is not a valid controller name", 2040);
        
        if ($controller == 'axBaseController')
            throw new RuntimeException("Redirection is impossible on $controller", 2041);
        
        if (!axAutoloader::load($controller))
            throw new BadMethodCallException("Cannot find $controller", 2042);
            
        throw new axForwardException($controller, $action);
    }
    
    /**
     * Triggers a redirection
     * @param string $url
     * @param int $method = axRedirectException::REDIRECT_REFRESH
     * @throws InvalidArgumentException
     * @throws axRedirectException
     * @return void
     */
    final protected static function redirect ($url, $method = axRedirectException::REDIRECT_REFRESH) {
        if (!$url)
            throw new InvalidArgumentException("First parameter is expected to be a valid url", 2043);
            
        throw new axRedirectException($url, $method);
    }
}