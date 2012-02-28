<?php
/**
 * @brief Table row group helper class file
 * @file axTableRowGroupHelper.class.php
 */

/**
 * @brief Table Row Group Helper Class
 *
 * @class axTableRowGroupHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axTableRowGroupHelper extends axBaseHelper {
    
    /**
     * @brief Type
     * 
     * Can be either:
     * @li head
     * @li foot
     * @li body
     * 
     * @property string $_type
     */
    protected $_type;
    
    /**
	 * @brief Row filter
	 * @property array $_filter
     */
    protected $_filter = array();
    
    /**
     * @brief Cell display callbacks
     * @internal
     * @property array $_callbacks
     */
    protected $_callbacks = array();
    
    /**
     * @brief Content columns to be added before values
     * @property mixed $_before_content
     */
    protected $_before_content;
    
    /**
     * @brief Content columns to be added before values replacement callback
     * @property callback $_before_callback
     */
    protected $_before_callback;
    
    /**
     * @brief Content columns to be added after values
     * @property mixed $_after_content
     */
    protected $_after_content;
    
    /**
     * @brief Content columns to be added after values callback
     * @property callback $_after_callback
     */
    protected $_after_callback;
    
    /**
     * @brief Constructor
     *
     * The @c $type parameter can be either
     * @li head or thead
     * @li foot or tfoot
     * @li body or tbody
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
     * @brief Type getter
     * @return string
     */
    public function getType () {
        return $this->_type;
    }
    
    /**
     * @brief Filter getter
     * @return array
     */
    public function getFilter () {
        return $this->_filter;
    }
    
    /**
     * @brief Filter setter
     * @param array $filter
     * @return axTableRowGroupHelper
     */
    public function setFilter (array $filter) {
        $this->_filter = $filter;
        return $this;
    }
    
    /**
     * @brief Get any defined column callback
     * @param string $key
     */
    public function getColumnCallback ($key) {
        return isset($this->_callbacks[$key]) ? $this->_callbacks[$key] : null;
    }
    
    /**
     * @brief Set a column display transformation callback
     *
     * @param string $key
     * @param callback $callback
     * @return TableGroupHelper
     */
    public function setColumnCallback ($key, $callback) {
        if (is_string($callback) && !is_callable($callback))
            $callback = callback($callback);
        
        if (!is_callable($callback))
            throw new InvalidArgumentException("Invalid callback provided");
            
        $this->_callbacks[$key] = $callback;
        return $this;
    }
    
    /**
     * @brief Set multiple callbacks at once
     * @param array $callbacks
     * @return TableGroupHelper
     */
    public function setColumnCallbacks (array $callbacks) {
        try {
            foreach ($callbacks as $key => $callback)
                $this->setColumnCallback($key, $callback);
        }
        catch (Exception $e) {
            $this->_callbacks = array();
            throw new RuntimeException("Cannot set callbacks");
        }
        return $this;
    }
    
    /**
     * @brief Add column(s) before row content.
     *
     * You may pass a callback to transform those cells and/or to change replace parameters. The callback must take as 
     * first parameter the cell(s) you are inserting (as array if multiple cells) and the rows to be inserted as 
     * second parameter. The second parameter will be provided during the array construction.
     *
     * Example:
     * @code
     * // add a message with a provided parameter before each table row
     * $table->body->before("Id : %d", 'function ($cell, $values) { return sprintf($cell[0], $values["id"]); }');
     * @endcode
     *
     * @param mixed $content
     * @param callback $replace_callback @optional @default{null}
     * @return axTableRowGroupHelper
     */
    public function before ($content, $replace_callback = null) {
        if ($replace_callback) {
            if (is_string($replace_callback) && !is_callable($replace_callback))
                $replace_callback = callback($replace_callback);
            
            if (!is_callable($replace_callback))
                throw new InvalidArgumentException("Provided callback is invalid");
        }
        
        $this->_before_content = (array)$content;
        $this->_before_callback = $replace_callback;
        return $this;
    }
    
    /**
     * @brief Works exactly as axTableRowGroupHelper::before does but add new column(s) after content.
     *
     * @see axTableRowGroupHelper::before()
     * @param mixed $content
     * @param callback $replace_callback @optional @default{null}
     * @return axTableRowGroupHelper
     */
    public function after ($content, $replace_callback = null) {
        if ($replace_callback) {
            if (is_string($replace_callback) && !is_callable($replace_callback))
                $replace_callback = callback($replace_callback);
            
            if (!is_callable($replace_callback))
                throw new InvalidArgumentException("Provided callback is invalid");
        }
            
        $this->_after_content = (array)$content;
        $this->_after_callback = $replace_callback;
        return $this;
    }
    
    /**
     * @brief Add multiple rows at once.
     *
     * The @c $cell_type parameter can be either 'data', 'head' or 'auto'. If the @c $cell_type is left to auto, it 
     * will be set to 'head' if current TableGroupHelper is header, 'data' otherwise.
     *
     * @see axTableRowGroupHelper::addRow()
     * @param Traversable|array $rows
     * @param string $cell_type @optional @default{"auto"}
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
     * @brief Add row.
     *
     * The @brei $cell_type parameter can be either data, head or auto.
     *
     * The @c $cell_type parameter can be either 'data', 'head' or 'auto'. If the @c $cell_type is left to auto, it 
     * will be set to 'head' if current TableGroupHelper is header, 'data' otherwise.
     *
     * @param Traversable|array $values
     * @param string $cell_type @optional @default{"auto"}
     * @return TableGroupHelper
     */
    public function addRow ($values, $cell_type = "auto") {
        if (is_scalar($values))
            $values = array($values);
        elseif ($values instanceof axModel)
            $values = $values->getData();
        elseif (!is_array($values) && !$values instanceof Traversable) {
            throw new InvalidArgumentException(
            	"First parameter is expected to be scalar, array or axModel, ".get_class($values)." given", 3002
            );
        }
            
        if ($cell_type == "auto")
            $cell_type = ($this->_type == "thead" || $this->_type == "head")  ? "head" : "data";
            
        if (!empty($this->_filter))
            $values = array_intersect_key($values, array_flip($this->_filter));
        
        foreach (array_intersect_key($values, $this->_callbacks) as $key => $value) {
            $values[$key] = $this->_callbacks[$key]($value);
        }
        
        if (!empty($this->_before_content)) {
            if (!empty($this->_before_callback)) {
                $alpha = $this->_before_callback;
                $before = (array)$alpha($this->_before_content, $values);
            }
            else
                $before = $this->_before_content;
                
            $values = array_merge($before, array_values($values));
        }
        
        if (!empty($this->_after_content)) {
            if (!empty($this->_after_callback)) {
                $alpha = $this->_after_callback;
                $after = (array)$alpha($this->_after_content, $values);
            }
            else
                $after = $this->_after_content;
                
            $values = array_merge(array_values($values), $after);
        }
        
        $this->appendChild(axTableRowHelper::export($values, $cell_type));
        return $this;
    }
    
    /**
     * @copydoc axBaseHelper::__toString()
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
     * @copydoc axTableRowGroupHelper::__construct()
     * @static
     * @brief Constructor static alias
     * @return axTableRowGroupHelper
     */
    public static function export ($type) {
        return new self ($type);
    }
}