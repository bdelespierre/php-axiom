<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Table Data Helper Class
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage TableDataHelper
 */
class TableDataHelper extends BaseHelper {
    
    /**
     * Default constructor.
     *
     * Type parameter can be either 'data' or 'head'
     *
     * @param sring $value
     * @param string $type
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
     * Constructor static alias
     * @param string $value
     * @param string $type
     * @return TableDataHelper
     */
    public static function export ($value = null, $type = 'data') {
        return new self ($value, $type);
    }
}