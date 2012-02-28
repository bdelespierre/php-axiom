<?php
/**
 * @brief Label helper class file
 * @file axLabelHelper.class.php
 */

/**
 * @brief Label Helper Class
 *
 * @class axLabelHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axLabelHelper extends axBaseHelper {

    /**
     * @brief Constructor
     * @param scalar $value The label's value
     * @param string $for @optional @default{""} The label for attribute value 
     */
    public function __construct ($value, $for = "") {
        parent::__construct('label', array(), $value);
        if ($for)
            $this->setFor($for);
    }

    /**
     * @copydoc axLabelHelper::__construct()
     * @brief Constructor static alias
     * @return axLabelHelper
     */
    public static function export ($value, $for = "") {
        return new self ($value, $for);
    }
}