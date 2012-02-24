<?php
/**
 * @brief Base controller class file
 * @file axBaseController.class.php
 */

/**
 * @brief Controller base class
 * 
 * All application controller should extend this class.
 * 
 * @warning All application controllers must have the @e Controller suffix (for instance @e ArticleController). 
 *
 * @class axBaseController
 * @ingroup Core
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @copyright http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
abstract class axBaseController {
    
    /**
     * @brief Request object
     * @property axRequest $_request
     */
    protected static $_request;
    
    /**
     * @brief Response object
     * @property axResponse $_response
     */
    protected static $_response;
    
    /**
     * @brief Initialize the controller with the request and response
     * 
     * You may extend this method to provide custom initialization for your controller (for instance, to initialize a
     * session).
     * 
     * @param axRequest $request The request object reference
     * @param axResponse $response The response object reference 
     * @return void
     */
    public static function _init (axRequest &$request, axResponse &$response) {
        self::setRequest($request);
        self::setResponse($response);
    }
    
    /**
     * @brief Set the request object
     * 
     * @param axRequest $request The request object reference
     * @return void
     */
    final protected static function setRequest (axRequest &$request) {
        self::$_request = $request;
    }
    
    /**
     * Set the response object
     * 
     * @param axResponse $response The response object reference
     * @return void
     */
    final protected static function setResponse (axResponse &$response) {
        self::$_response = $response;
    }
    
    /**
     * @brief Forward the action to another controller
     * 
     * Calling this method will break the current execution call to continue the request processing on another 
     * controller/action couple. This is useful when you want to delegate a controller's action to another controller
     * (for instance, display an HTTP error message using the ErrorController::http403).
     * 
     * @note The forwarding mechanism uses an axForwardException to @e break the execution flow. The exception 
     * thrown is supposed to be caught by the axRouter that will take the appropriate action according to the 
     * exception.
     * 
     * @param string $controller The controller to invoke
     * @param string $action @optional @default{"index"} The action to invoke
     * @throws RuntimeException If the controller to invoke is invalid
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
     * @breif Triggers a redirection
     * 
     * This method works the same way axBaseController::forward does but the invoked action will be an HTTP redirection
     * (permanent or refresh base according to the $method parameter, see axRedirectException for more details).
     * 
     * @param string $url The URL to redirect to
     * @param int $method @optional @default{axRedirectException::REDIRECT_REFRESH} The redirection method
     * @throws InvalidArgumentException if the @c $url parameter is not a valid URL
     * @throws axRedirectException
     * @return void
     */
    final protected static function redirect ($url, $method = axRedirectException::REDIRECT_REFRESH) {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("First parameter is expected to be a valid url", 2043);
            
        throw new axRedirectException($url, $method);
    }
}