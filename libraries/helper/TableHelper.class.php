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
 * @version $Rev$
 * @subpackage TableHelper
 */
class TableHelper extends BaseHelper {
    
    /**
     * thead element
     * @var TableRowGroupHelper
     */
    public $head = null;
    
    /**
     * tfoot element
     * @var TableRowGroupHelper
     */
    public $foot = null;
    
    /**
     * tbody element
     * @var TableRowGroupHelper
     */
    public $body = null;
    
    /**
     * Default constructor
     * @param string $caption = false
     */
    public function __construct ($caption = false) {
        parent::__construct('table');
        if ($caption)
            $this->appendChild(CaptionHelper::export($caption));
            
        $this->head = &$this->_children['head'];
        $this->foot = &$this->_children['foot'];
        $this->body = &$this->_children['body'];
            
        $this->setHead();
        $this->setFoot();
        $this->setBody();
    }
    
    /**
     * Alias of TableHelper::setHead
     * @param array $columns
     * @return TableHelper
     */
    public function setColumnNames (array $columns) {
        return $this->setHead(array($columns));
    }
    
    /**
     * Set the header.
     *
     * If previous header was set, it will be discarded.
     *
     * @param Traversable $rows = null
     * @return TableHelper
     */
    public function setHead ($rows = null) {
        $this->_children['head'] = TableRowGroupHelper::export('head');
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
     * @return TableHelper
     */
    public function setFoot ($rows = null) {
        $this->_children['foot'] = TableRowGroupHelper::export('foot');
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
     * @return TableHelper
     */
    public function setBody ($rows = null) {
        $this->_children['body'] = TableRowGroupHelper::export('body');
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
     * @return TableHelper
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
     * @see TableHelper::addRow
     * @param Traversable $rows
     * @param strign $to
     * @return TableHelper
     */
    public function addRows ($rows, $to = false) {
        foreach ($rows as $row)
            $this->addRow($row, $to);
        
        return $this;
    }
    
    /**
     * Add a colgroup to the table and return it
     * @return ColGroupHelper
     */
    public function addColGroup () {
        return $this->appendChild(ColGroupHelper::export());
    }
    
    /**
     * (non-PHPdoc)
     * @see BaseHelper::__toString()
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
                case $node instanceof CaptionHelper: $children['caption'][] = $node; break;
                case $node instanceof ColGroupHelper: $children['colgroup'][] = $node; break;
                case $node instanceof ColHelper: $children['col'][] = $node; break;
                case $node instanceof TableRowGroupHelper:
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
     * @return TableHelper
     */
    public static function export ($caption = false) {
        return new self ($caption);
    }
}