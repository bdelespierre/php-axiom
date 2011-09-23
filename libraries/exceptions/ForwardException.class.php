<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Foward Exception
 *
 * @author Delespierre
 * @version $Rev: 46 $
 * @subpackage ForwardException
 */
class ForwardException extends LogicException {
    
    /**
     * Controller to forward to
     * @internal
     * @var string
     */
    protected $_controller;
    
    /**
     * Action to forward to
     * @internal
     * @var string
     */
    protected $_action;
    
    /**
     * Default constructor
     * @param string $controller
     * @param string $action
     */
    public function __construct ($controller, $action = 'index') {
        parent::__construct("Forward action to $controller::$action");
        $this->_controller = $controller;
        $this->_action = $action;
    }
    
    /**
     * Get the destination controller
     * @return string
     */
    public function getController () {
        return $this->_controller;
    }
    
    /**
     * Get the destination action
     * @return string
     */
    public function getAction () {
        return $this->_action;
    }
}