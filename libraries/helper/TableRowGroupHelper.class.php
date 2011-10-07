<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Table Row Group Helper Class
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage TableRowGroupHelper
 */
class TableRowGroupHelper extends BaseHelper {
    
    /**
     * Inner type.
     * Can be either:
     * - head
     * - foot
     * - body
     * @var string
     */
    protected $_type;
    
    /**
	 * Row filter
	 * @var array
     */
    protected $_filter = array();
    
    /**
     * Default constructor.
     *
     * The $type parameter can be either
     * - head or thead
     * - foot or tfoot
     * - body or tbody
     *
     * @param string $type
     */
    public function __construct ($type) {
        switch (strtolower($type)) {
            case 'head':
            case 'thead':
                $this->_type = "head";
                parent::__construct('thead');
                break;
                
            case 'foot':
            case 'tfoot':
                $this->_type = "foot";
                parent::__construct('tfoot');
                break;
                
            case 'body':
            case 'tbody':
            default:
                $this->_type = "body";
                parent::__construct('tbody');
        }
    }
    
    /**
     * Type getter
     * @return string
     */
    public function getType () {
        return $this->_type;
    }
    
    /**
     * Filter getter
     * @return array
     */
    public function getFilter () {
        return $this->_filter;
    }
    
    /**
     * Filter setter
     * @param array $filter
     * @return TableRowGroupHelper
     */
    public function setFilter (array $filter) {
        $this->_filter = $filter;
        return $this;
    }
    
    /**
     * Add multiple rows at once.
     *
     * The $cell_type parameter can be either data, head or auto.
     *
     * If the $cell_type is left to auto, it will be set to head if
     * current TableGroupHelper is header, data otherwise.
     *
     * @see TableRowGroupHelper::addRow
     * @param Traversable $rows
     * @param string $cell_type = "auto"
     * @return TableGroupHelper
     */
    public function addRows ($rows, $cell_type = "auto") {
        if (!is_array($rows) && !$rows instanceof Traversable)
            throw new InvalidArgumentException("First parameter is expected to be array or Traversable, " . gettype($rows) . " given", 3003);
        
        if ($cell_type == "auto")
            $cell_type = ($this->_type == "thead" || $this->_type == "head") ? "head" : "data";
            
        foreach ($rows as $row) {
            $this->addRow($row, $cell_type);
        }
        return $this;
    }
    
    /**
     * Add row.
     *
     * The $cell_type parameter can be either data, head or auto.
     *
     * If the $cell_type is left to auto, it will be set to head if
     * current TableGroupHelper is header, data otherwise.
     *
     * @param Traversable $values
     * @param string $cell_type = "auto"
     * @return TableGroupHelper
     */
    public function addRow ($values, $cell_type = "auto") {
        if (is_scalar($values))
            $values = array($values);
        elseif ($values instanceof Model)
            $values = $values->getData();
        elseif (!is_array($values) && !$values instanceof Traversable)
            throw new InvalidArgumentException("First parameter is expected to be scalar, array or Model, ".get_class($values)." given", 3002);
            
        if ($cell_type == "auto")
            $cell_type = ($this->_type == "thead" || $this->_type == "head")  ? "head" : "data";
            
        if (!empty($this->_filter))
            $values = array_intersect_key($values, array_flip($this->_filter));
        
        $this->appendChild(TableRowHelper::export($values, $cell_type));
        return $this;
    }
    
    /**
     * (non-PHPdoc)
     * @see BaseHelper::__toString()
     */
    public function __toString () {
        $attr = array();
        foreach ($this->_attributes as $name => $value) {
            $attr[] = "$name=\"$value\"";
        }
        $node = "<{$this->_node_name} " . implode(' ', $attr) . ">";

        if (count($this->_children))
            $node .= implode($this->_children);

        return $node . "</{$this->_node_name}>";
    }
    
    /**
     * Constructor static alias
     * @param string $type
     * @return TableRowGroupHelper
     */
    public static function export ($type) {
        return new self ($type);
    }
}