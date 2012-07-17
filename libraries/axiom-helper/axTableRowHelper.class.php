<?php
/**
 * @brief Table row helper class file
 * @file axTableRowHelper.class.php
 */

/**
 * @brief Table Row Helper
 *
 * @class axTableRowHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axTableRowHelper extends axBaseHelper {
    
    /**
     * @brief Constructor
     *
     * @param Traversable|array $values @optional @default{array()} 
     * @param string $cells_type @optional @default{"data"} Can be either "data" or "head"
     */
    public function __construct ($values = null, $cells_type = "data") {
        parent::__construct('tr');
        
        if (!empty($values))
            $this->addCells($values, $cells_type);
    }
    
    /**
     * @brief Add multiple cells at once.
     *
     * @param Traversable|array $values @optional @default{array()} 
     * @param string $cells_type @optional @default{"data"} Can be either "data" or "head"
     * @return axTableRowHelper
     */
    public function addCells ($values, $cells_type = "data") {
        if (is_scalar($values))
            $values = array($values);
        elseif (is_object($values) && $values instanceof axModel)
            $values = $values->getData();
        elseif (!is_array($values) && !$values instanceof Traversable)
            throw new InvalidArgumentException("First parameter is expected to be scalar, array or axModel, ".get_class($values)." given", 2049);
        
        foreach ($values as $value)
            $this->addCell($value, $cells_type);
            
        return $this;
    }
    
    /**
     * @brief Add cell.
     *
     * @param Traversable|array $values @optional @default{array()} 
     * @param string $cells_type @optional @default{"data"} Can be either "data" or "head"
     * @return axTableRowHelper
     */
    public function addCell ($value, $cells_type = "data") {
        $this->appendChild(axTableDataHelper::export($value, $cells_type));
        return $this;
    }
    
    /**
     * @brief Constructor static alias
     * @static
     * @param Traversable|array $values @optional @default{array()} 
     * @param string $cells_type @optional @default{"data"} Can be either "data" or "head"
     * @return axTableRowHelper
     */
    public static function export ($values = null, $cells_type = "data") {
        return new self ($values, $cells_type);
    }
}