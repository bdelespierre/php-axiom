<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Texarea Helper Class
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage TextareaHelper
 */
class TextareaHelper extends BaseHelper {

    /**
     * Default constructor
     * @param string $name
     * @param mixed $value
     */
    public function __construct ($name, $value = "") {
        parent::__construct('textarea', array('name' => $name), $value);
        
        if (empty($value))
            $this->_children[] = null;
    }

    /**
     * Constructor static alias
     * @param string $name
     * @param mixed $value
     * @return TextareaHelper
     */
    public static function export ($name, $value = "") {
        return new self ($name, $value);
    }
}