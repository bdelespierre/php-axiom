<?php
/**
 * @brief Table helper class file
 * @file axTableHelper.class.php
 */

/**
 * @brief Table Helper Class
 *
 * @class axTableHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axTableHelper extends axBaseHelper {
    
    /**
     * @brief thead element
     * @property axTableRowGroupHelper $head
     */
    public $head = null;
    
    /**
     * @brief tfoot element
     * @property axTableRowGroupHelper $foot
     */
    public $foot = null;
    
    /**
     * @brief tbody element
     * @property axTableRowGroupHelper $body
     */
    public $body = null;
    
    /**
     * @brief Constructor
     * @param string $caption @optional @default{false} The table caption value
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
     * @brief Set the table column's name (and optionaly use them as a filter for any row added to the body)
     * @see axTableHelper::setHead()
     * @param array $columns
     * @param boolean $filter @optional @default{true} Use these columns as filter
     * @return axTableHelper
     */
    public function setColumnNames (array $columns, $filter = true) {
        if ($filter)
            $this->body->setFilter(array_keys($columns));
        return $this->setHead(array($columns));
    }
    
    /**
     * @brief Set the header
     *
     * If previous header was set, it will be discarded.
     *
     * @param Traversable|array $rows @optional @default{null} The rows to append
     * @return axTableHelper
     */
    public function setHead ($rows = null) {
        $this->_children['head'] = axTableRowGroupHelper::export('head');
        if (!empty($rows))
            $this->head->addRows($rows);
        return $this;
    }
    
    /**
     * @brief Set the footer
     *
     * If previous footer was set, it will be discarded.
     *
     * @param Traversable|array $rows @optional @default{null} The rows to append
     * @return axTableHelper
     */
    public function setFoot ($rows = null) {
        $this->_children['foot'] = axTableRowGroupHelper::export('foot');
        if (!empty($rows))
            $this->foot->addRows($rows);
        return $this;
    }
    
    /**
     * @brief Set the body.
     *
     * If previous body was set, it will be discarded.
     *
     * @param Traversable $rows @optional @default{null} The rows to append
     * @return axTableHelper
     */
    public function setBody ($rows = null) {
        $this->_children['body'] = axTableRowGroupHelper::export('body');
        if (!empty($rows))
            $this->body->addRows($rows);
        return $this;
    }
    
    /**
     * @brief Add a row to the given table row group.
     *
     * The @c $to parameter can be either:
     * @li head or thead
     * @li foot or tfoot
     * @li body or tbody
     * If the $to parameter is left to false, the row will be added to the body section.
     *
     * @param Traversable $row
     * @param string $to @optional @default{false} The section to append this row to
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
     * @brief Add multiple rows at once
     * @see axTableHelper::addRow()
     * @param Traversable|array $rows
     * @param string $to @optional @default{false} The section to append this row to
     * @return axTableHelper
     */
    public function addRows ($rows, $to = false) {
        foreach ($rows as $row)
            $this->addRow($row, $to);
        
        return $this;
    }
    
    /**
     * @brief Add a colgroup to the table and return it
     * @return axColGroupHelper
     */
    public function addColGroup () {
        return $this->appendChild(axColGroupHelper::export());
    }
    
    /**
     * @copydoc axBaseHelper::__toString()
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
     * @copydoc axTableHelper::__construct()
     * @static
     * @brief Constructor static alias
     * @return axTableHelper
     */
    public static function export ($caption = false) {
        return new self ($caption);
    }
}