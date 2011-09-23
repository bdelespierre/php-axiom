<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Label Helper Class
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage LabelHelper
 */
class LabelHelper extends BaseHelper {

    /**
     * Default constructor
     * @param scalar $value
     * @param string $for = ""
     */
    public function __construct ($value, $for = "") {
        parent::__construct('label', array(), $value);
        if ($for)
            $this->setFor($for);
    }

    /**
     * Constructor static alias
     * @param scalar $value
     * @param string $for = ""
     * @return LabelHelper
     */
    public static function export ($value, $for = "") {
        return new self ($value, $for);
    }
}