<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Table Helper Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage helper
 */
class axTableHelper extends axBaseHelper {
    
    /**
     * thead element
     * @var axTableRowGroupHelper
     */
    public $head = null;
    
    /**
     * tfoot element
     * @var axTableRowGroupHelper
     */
    public $foot = null;
    
    /**
     * tbody element
     * @var axTableRowGroupHelper
     */
    public $body = null;
    
    /**
     * Default constructor
     * @param string $caption = false
     */
    public function __construct ($caption = false) {
        parent::__construct('table');
        if ($caption)
            $this->appendChild(axCaptionHelper::export($caption));
            
        $this->head = &$this->_children['head'];
        $this->foot = &$this->_children['foot'];
        $this->body = &$this->_children['body'];
            
        $this->setHead();
        $this->setFoot();
        $this->setBody();
    }
    
    /**
     * Alias of axTableHelper::setHead
     * @param array $columns
     * @param boolean $filter use these columns as filter
     * @return axTableHelper
     */
    public function setColumnNames (array $columns, $filter = true) {
        if ($filter)
            $this->body->setFilter(array_keys($columns));
        return $this->setHead(array($columns));
    }
    
    /**
     * Set the header.
     *
     * If previous header was set, it will be discarded.
     *
     * @param Traversable $rows = null
     * @return axTableHelper
     */
    public function setHead ($rows = null) {
        $this->_children['head'] = axTableRowGroupHelper::export('head');
        if (!empty($rows))
            $this->head->addRows($rows);
        return $this;
    }
    
    /**
     * Set the footer.
     *
     * If previous footer was set, it will be discarded.
     *
     * @param Traversable $rows = null
     * @return axTableHelper
     */
    public function setFoot ($rows = null) {
        $this->_children['foot'] = axTableRowGroupHelper::export('foot');
        if (!empty($rows))
            $this->foot->addRows($rows);
        return $this;
    }
    
    /**
     * Set the body.
     *
     * If previous body was set, it will be discarded.
     *
     * @param Traversable $rows = null
     * @return axTableHelper
     */
    public function setBody ($rows = null) {
        $this->_children['body'] = axTableRowGroupHelper::export('body');
        if (!empty($rows))
            $this->body->addRows($rows);
        return $this;
    }
    
    /**
     * Add a row to the given table row group.
     *
     * The $to parameter can be either:
     * - head or thead
     * - foot or tfoot
     * - body or tbody
     * If the $to parameter is left to false, the
     * row will be added to the body section.
     *
     * @param Traversable $row
     * @param string $to = false
     * @return axTableHelper
     */
    public function addRow ($row, $to = false) {
        switch (strtolower($to)) {
            case 'head': case 'thead': $rowgroup = 'head'; break;
            case 'foot': case 'tfoot': $rowgroup = 'foot'; break;
            case 'body': case 'tbody': default: $rowgroup = 'body';
        }
        $this->$rowgroup->addRow($row);
        return $this;
    }
    
    /**
     * Add multiple rows at once
     * @see axTableHelper::addRow
     * @param Traversable $rows
     * @param strign $to
     * @return axTableHelper
     */
    public function addRows ($rows, $to = false) {
        foreach ($rows as $row)
            $this->addRow($row, $to);
        
        return $this;
    }
    
    /**
     * Add a colgroup to the table and return it
     * @return axColGroupHelper
     */
    public function addColGroup () {
        return $this->appendChild(axColGroupHelper::export());
    }
    
    /**
     * (non-PHPdoc)
     * @see axBaseHelper::__toString()
     */
    public function __toString () {
        // Order the children elements according to xhtml DTD
        $children = array(
            'caption' => array(),
            'colgroup' => array(),
            'col' => array(),
            'thead' => array(),
            'tfoot' => array(),
            'tbody' => array(),
        );
        foreach ($this->_children as $node) {
            switch ($node) {
                case $node instanceof axCaptionHelper: $children['caption'][] = $node; break;
                case $node instanceof axColGroupHelper: $children['colgroup'][] = $node; break;
                case $node instanceof axColHelper: $children['col'][] = $node; break;
                case $node instanceof axTableRowGroupHelper:
                    switch($node->getType()) {
                        case 'head': $children['thead'][] = $node; break;
                        case 'foot': $children['tfoot'][] = $node; break;
                        case 'body': $children['tbody'][] = $node; break;
                    }
                    break;
            }
        }
        $this->_children = call_user_func_array('array_merge', $children);
        return parent::__toString();
    }
    
    /**
     * Constructor static alias
     * @param string $caption
     * @return axTableHelper
     */
    public static function export ($caption = false) {
        return new self ($caption);
    }
}