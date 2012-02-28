<?php
/**
 * @brief Col helper class file
 * @file axColHelper.class.php
 */

/**
 * @brief Column Helper Class
 *
 * @class axColHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axColHelper extends axBaseHelper {
    
    /**
     * @brief Constructor
     */
    public function __construct () {
        parent::__construct('col');
    }
    
    /**
     * @copydoc axBaseHelper::setValue()
     * Will always throw a BadMethodCallException
     * @throws BadMethodCallException Because @c col tag cannot have value
     */
    public function setValue ($value) {
        throw new BadMethodCallException("Col tag cannot have value", 3007);
    }
    
    /**
     * @copydoc axBaseHelper::appendChild()
     * Will always throw a BadMethodCallException
     * @throws BadMethodCallException Because @c col tag cannot have children
     */
    public function appendChild ($node) {
        throw new BadMethodCallException("Col tag cannot have children", 3008);
    }
    
    /**
     * @copydoc axBaseHelper::prependChild()
     * Will always throw a BadMethodCallException
     * @throws BadMethodCallException Because @c col tag cannot have children
     */
    public function prependChild ($node) {
        throw new BadMethodCallException("Col tag cannot have children", 3008);
    }
    
    /**
     * @brief Constructor static alias
     * @return axColHelper
     */
    public static function export () {
        return new self;
    }
}