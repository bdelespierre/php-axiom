<?php
/**
 * @brief Textarea helper class file
 * @file axTextareaHelper.class.php
 */

/**
 * @brief Texarea Helper Class
 *
 * @class axTextareaHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axTextareaHelper extends axBaseHelper {

    /**
     * @brief Constructor
     * @param string $name
     * @param mixed $value @optional @default
     */
    public function __construct ($name, $value = "") {
        parent::__construct('textarea', array('name' => $name), $value);
        
        if (empty($value))
            $this->_children[] = null;
    }

    /**
     * @copydoc axTextareaHelper::__construct()
     * @static
     * @brief Constructor static alias
     * @return axTextareaHelper
     */
    public static function export ($name, $value = "") {
        return new self ($name, $value);
    }
}