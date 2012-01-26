<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Session Class
 *
 * Note: by default any instance of
 * this class will point to the same
 * $_SESSION offset for practical
 * purposes. You may add as many
 * sub-arrays as you want.
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage core
 */
class Axiom_Session {
    
    /**
     * Internal configuration
     * @internal
     * @var array
     */
    protected static $_config;
    
    /**
     * Axiom_Session values
     * @var array
     */
    protected $_session_parameters;
    
    /**
     * Configure.
     *
     * Note: if index conf is left to null,
     * the $_SESSION array will be used.
     *
     * @param array $config
     * @return void
     */
    public static function setConfig (array $config = array()) {
        $default = array(
            // FIXME remove session index
            'index' => null,
            'name'  => 'php-axiom',
        );
        
        self::$_config = $config + $default;
        
        if (self::$_config['name'])
            self::name(self::$_config['name']);
    }
    
    /**
     * Construct a new session handler instance
     */
    public function __construct () {
        if (!session_id())
            self::start();
        
        if (self::$_config['index'])
            $this->_session_parameters = & $_SESSION[self::$_config['index']];
        else
            $this->_session_parameters = & $_SESSION;
    }
    
    /**
     * Get a session reference
     * @param string $key
     * @return mixed
     */
    public function __get ($key) {
        return isset($this->_session_parameters[$key]) ? $this->_session_parameters[$key] : null;
    }
    
    /**
     * Set any session reference
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        $this->_session_parameters[$key] = $value;
    }
    
    /**
     * Start the session
     * @return boolean
     */
    public static function start () {
        if (!self::started())
            return session_start();
        return false;
    }
    
    /**
     * Destroy the session
     * @return boolean
     */
    public static function destroy () {
        return session_destroy();
    }
    
    /**
     * Get or set the session id
     * @param string $id
     * @return string
     */
    public static function id ($id = false) {
        return $id !== false ? session_id($id) : session_id();
    }
    
    /**
     * Get or set the session name
     * @param string $name
     * @return string
     */
    public static function name ($name = false) {
        return $name !== false ? session_name($name) : session_name();
    }
    
    /**
     * Tell if session is started
     * @return boolean
     */
    public static function started () {
        return self::id() !== "";
    }
    
    /**
     * Renew the session
     * @return boolean
     */
    public static function renew () {
        self::start();
        self::destroy();
        return self::start();
    }
}