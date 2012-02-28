<?php
/**
 * @brief Table data helper class file
 * @file axTableDataHelper.class.php
 */

/**
 * @brief Table Data Helper Class
 *
 * @class axTableDataHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axTableDataHelper extends axBaseHelper {
    
    /**
     * @brief Constructor.
     * @param sring $value @optional @default{null}
     * @param string $type @optional @default{'data'} Can be either 'data' or 'head'
     */
    public function __construct ($value = null, $type = 'data') {
        switch (strtolower($type)) {
            case 'th':
            case 'head':
                parent::__construct('th', array(), $value);
                break;
            
            case 'td':
            case 'data':
            default:
                parent::__construct('td', array(), $value);
                break;
        }
    }
    
    /**
     * @copydoc axTableDataHelper::__construct()
     * @static
     * @brief Constructor static alias
     * @return axTableDataHelper
     */
    public static function export ($value = null, $type = 'data') {
        return new self ($value, $type);
    }
}