<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Error Controller
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage ErrorController
 */
class ErrorController extends BaseController {
    
    public static function index () { }
    
    /* HTTP 40* Errors */
    public static function http400 () { self::$_response->setHeader("HTTP/1.0 400 Bad Request"); }
    public static function http401 () { self::$_response->setHeader("HTTP/1.0 401 Unauthorized"); }
    public static function http403 () { self::$_response->setHeader("HTTP/1.0 403 Forbidden"); }
    public static function http404 () { self::$_response->setHeader("HTTP/1.0 404 Not Found"); }
    
    /* HTTP 50* Errors */
    public static function http500 () { self::$_response->setHeader("HTTP/1.0 500 Internal Server Error"); }
    public static function http501 () { self::$_response->setHeader("HTTP/1.0 501 Not Implemented"); }
    public static function http503 () { self::$_response->setHeader("HTTP/1.0 503 Service Unavailable"); }
    
    /* OTHER HTTP Headers */
    public static function redirection () {}
}