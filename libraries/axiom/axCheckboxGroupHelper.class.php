<?php
/**
 * @brief Checkbox group helper class file
 * @file axCheckboxGroupHelper.class.php
 */

/**
 * @brief Checkbox Group Helper Class
 *
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axCheckboxGroupHelper extends axBaseHelper {

    /**
     * @brief Inner input's name
     * @property string $_name
     */
    protected $_name;

    /**
     * @brief Current position
     * @property integer $_count
     */
    protected static $_count = 1;

    /**
     * @brief Constructor
     * @param string $name Group name (HTML name attribute value)
     * @param array $values @optional @default{array()} Values list (one per checkbox), keys will be used as label
     */
    public function __construct ($name, array $values = array()) {
        parent::__construct('span');
        $this->_name = $name;

        if (!empty($values))
            $this->addOptions($values);
    }

    /**
     * @brief Add an option (a checkbox) to the group
     * @param string $label
     * @param scalar $value
     * @return axCheckboxGroupHelper
     */
    public function addOption ($label, $value) {
        $this->_children[] = axInputHelper::export($this->_name, 'checkbox', $value)->setId($id = 'checkbox' . (self::$_count ++));
        $this->_children[] = axLabelHelper::export($label, $id);
        return $this;
    }
    
    /**
     * @brief Add multiple options at once.
     * @see axCheckboxGroupHelper::addOption()
     * @param array $values Values list (one per checkbox), keys will be used as label
     * @return axCheckboxGroupHelper
     */
    public function addOptions (array $values) {
        foreach ($values as $key => $value)
            $this->addOption($key, $value);
        return $this;
    }

    /**
     * @copydoc axBaseHelper::setValue()
     */
    public function setValue ($value) {
        foreach ($this->_children as &$node) {
            if (($node instanceof axInputHelper) && $node->getValue() == $value) {
                $node->setChecked('checked');
            }
        }
        
        return $this;
    }
    
    /**
     * @brief Get group's name
     * @return string
     */
    public function getName () {
        return $this->_name;
    }
    
    /**
     * @brief Constructor static alias
     * @see axCheckboxGroupHelper::__construct
     * @param string $name
     * @param array $values @optional @default{array()}
     * @return axCheckboxGroupHelper
     */
    public static function export ($name, array $values = array()) {
        return new self ($name, $values);
    }
}