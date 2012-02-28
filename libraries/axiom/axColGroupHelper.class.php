<?php
/**
 * @brief Col group helper class file
 * @file axColGroupHelper.class.php
 */

/**
 * @brief Column Group Helper Class
 *
 * @class axColGroupHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axColGroupHelper extends axBaseHelper {
    
    /**
     * @brief Constructor
     */
    public function __construct () {
        parent::__construct('colgroup');
    }
    
    /**
     * @brief Add a column to the colgroup and return it
     * @return axColHelper
     */
    public function addCol () {
        return $this->appendChild(axColHelper::export());
    }
    
    /**
     * @brief Constructor static alias
     * @return axColGroupHelper
     */
    public static function export () {
        return new self ();
    }
}