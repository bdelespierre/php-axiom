<?php
/**
 * @brief Session class file
 * @file axSession.class.php
 */

/**
 * @brief Session Class
 *
 * @note By default any instance of this class will point to the same $_SESSION structure for practical purposes. 
 *
 * @class axSession
 * @author Delespierre
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axSession {
    
    /**
     * @brief axSession values
     * @property array $_session_parameters
     */
    protected $_session_parameters;
	
    /**
     * @breif Construct a new session handler instance
     */
    public function __construct ($name) {
    	$this->name($name);
        if (!$this->id())
            self::start();
        
        $this->_session_parameters = & $_SESSION;
    }
    
    /**
     * @brief Get a session reference
     * @param string $key
     * @return mixed
     */
    public function __get ($key) {
        return isset($this->_session_parameters[$key]) ? $this->_session_parameters[$key] : null;
    }
    
    /**
     * @brief Set any session reference
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        $this->_session_parameters[$key] = $value;
    }
    
    /**
     * @brief Start the session
     * @static
     * @return boolean
     */
    public static function start () {
        if (!self::started())
            return session_start();
        return false;
    }
    
    /**
     * @brief Destroy the session
     * @static
     * @return boolean
     */
    public static function destroy () {
        return session_destroy();
    }
    
    /**
     * @brief Get or set the session id
     * @static
     * @param string $id @optional @default{false}
     * @return string
     */
    public static function id ($id = false) {
        return $id !== false ? session_id($id) : session_id();
    }
    
    /**
     * @brief Get or set the session name
     * @static
     * @param string $name @optional @default{false}
     * @return string
     */
    public static function name ($name = false) {
        return $name !== false ? session_name($name) : session_name();
    }
    
    /**
     * @brief Tell if session is started
     * @static
     * @return boolean
     */
    public static function started () {
        return self::id() !== "";
    }
}