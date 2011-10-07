<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Table Row Helper
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage TableRowHelper
 */
class TableRowHelper extends BaseHelper {
    
    /**
     * Default constructor
     *
     * The $cell_type parameter can be either "data" or "head"
     *
     * @param Traversable $values = null
     * @param string $cells_type
     */
    public function __construct ($values = null, $cells_type = "data") {
        parent::__construct('tr');
        
        if (!empty($values))
            $this->addCells($values, $cells_type);
    }
    
    /**
     * Add multiple cells at once.
     *
     * The $cell_type parameter can be either "data" or "head"
     *
     * @param Traversable $values
     * @param string $cells_type = "data"
     * @return TableRowHelper
     */
    public function addCells ($values, $cells_type = "data") {
        if (is_scalar($values))
            $values = array($values);
        elseif (is_object($values) && $values instanceof Model)
            $values = $values->getData();
        elseif (!is_array($values) && !$values instanceof Traversable)
            throw new InvalidArgumentException("First parameter is expected to be scalar, array or Model, ".get_class($values)." given", 2049);
        
        foreach ($values as $value)
            $this->addCell($value, $cells_type);
            
        return $this;
    }
    
    /**
     * Add cell.
     *
     * The $cell_type parameter can be either "data" or "head"
     *
     * @param scalar $value
     * @param string $cells_type
     * @return TableRowHelper
     */
    public function addCell ($value, $cells_type = "data") {
        $this->appendChild(TableDataHelper::export($value, $cells_type));
        return $this;
    }
    
    /**
     * Constructor static alias
     *
     * The $cell_type parameter can be either "data" or "head"
     *
     * @param Traversable $values = null
     * @param string $cells_type = "data"
     * @return TableRowHelper
     */
    public static function export ($values = null, $cells_type = "data") {
        return new self ($values, $cells_type);
    }
}