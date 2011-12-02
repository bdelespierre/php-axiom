<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Redirect Exception Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage exception
 */
class RedirectException extends Exception {
    
    /**
     * Constants
     */
    const REDIRECT_PERMANENT = 1;
    const REDIRECT_REFRESH = 2;
    
    /**
     * Expressed in seconds
     * @var integer
     */
    const REFRESH_DELAY = 5;
    
    /**
     * URL to redirect to
     * @var string
     */
    protected $_url;
    
    /**
     * Redirect method
     * @var integer
     */
    protected $_method;
    
    /**
     * Default constructor
     * @param string $url
     * @param integer $method
     */
    public function __construct ($url, $method = self::REDIRECT_PERMANENT) {
        parent::__construct("Redirect to $url");
        $this->_url = $url;
        $this->_method = $method;
    }
    
    /**
     * Get the redirection URL
     * @return string
     */
    public function getUrl () {
        return $this->_url;
    }
    
    /**
     * Get the redirection method
     * @return integer
     */
    public function getMethod () {
        return $this->_method;
    }
    
    /**
     * Get the proper header string to send to the browser.
     *
     * Will return false if the redirect method is not set.
     * @return string
     */
    public function getHeaderString () {
        switch ($this->_method) {
            case self::REDIRECT_REFRESH:   return "Refresh: " . self::REFRESH_DELAY . ";url={$this->_url}"; break;
            case self::REDIRECT_PERMANENT: return "Location: {$this->_url}"; break;
        }
        return false;
    }
    
    /**
     * (non-PHPdoc)
     * @see Exception::__toString()
     */
    public function __toString () {
        return ($header = $this->getHeaderString()) ? $header : "";
    }
}