<?php
/**
 * @brief Redirect exception class file
 * @file axRedirectException.class.php
 */

/**
 * @brief Redirect Exception Class
 *
 * @todo Redirect Exception long description
 * @class axRedirectException
 * @author Delespierre
 * @ingroup Exception
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axRedirectException extends Exception {
    
    /**
     * Constants
     * @var integer
     */
    const REDIRECT_PERMANENT = 1;
    const REDIRECT_REFRESH = 2;
    
    /**
     * @brief Expressed in seconds
     * @var integer
     */
    const REFRESH_DELAY = 5;
    
    /**
     * @brief URL to redirect to
     * @property string $_url
     */
    protected $_url;
    
    /**
     * @brief Redirect method
     * @property integer $_method
     */
    protected $_method;
    
    /**
     * @brief Constructor
     * @param string $url
     * @param integer $method axRedirectException::REDIRECT_PERMANENT or axRedirectException::REDIRECT_REFRESH
     */
    public function __construct ($url, $method = self::REDIRECT_PERMANENT) {
        parent::__construct("Redirect to $url");
        $this->_url = $url;
        $this->_method = $method;
    }
    
    /**
     * @brief Get the redirection URL
     * @return string
     */
    public function getUrl () {
        return $this->_url;
    }
    
    /**
     * @brief Get the redirection method
     * @return integer
     */
    public function getMethod () {
        return $this->_method;
    }
    
    /**
     * @brief Get the proper header string to send to the browser.
     *
     * Will return false if the redirect method is not set.
     * 
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
     * @brief Get the header from this exception as string
     * @return string
     */
    public function __toString () {
        return ($header = $this->getHeaderString()) ? $header : "";
    }
}