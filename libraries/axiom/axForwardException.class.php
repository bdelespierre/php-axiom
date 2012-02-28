<?php
/**
 * @brief Forward exception class file
 * @file axForwardException.class.php
 */

/**
 * @brief Foward Exception
 *
 * Exception thrown to reroute flow in controllers.
 *
 * @class axForwardException
 * @author Delespierre
 * @ingroup Exception
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axForwardException extends LogicException {
    
    /**
     * @brief Controller to forward to
     * @property string $_controller
     */
    protected $_controller;
    
    /**
     * @brief Action to forward to
     * @property string $_action
     */
    protected $_action;
    
    /**
     * @brief Constructor
     * @param string $controller The controller to forward flow to
     * @param string $action The action to forward flow to
     */
    public function __construct ($controller, $action = 'index') {
        parent::__construct("Forward action to $controller::$action");
        $this->_controller = $controller;
        $this->_action = $action;
    }
    
    /**
     * @brief Get the destination controller
     * @return string
     */
    public function getController () {
        return $this->_controller;
    }
    
    /**
     * @brief Get the destination action
     * @return string
     */
    public function getAction () {
        return $this->_action;
    }
}