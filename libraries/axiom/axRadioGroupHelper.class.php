<?php
/**
 * @brief Radio group helper class file
 * @file axRadioGroupHelper.class.php
 */

/**
 * @brief Radio Group Helper Class
 *
 * @class axRadioGroupHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axRadioGroupHelper extends axBaseHelper {

    /**
     * @brief Inner input's name
     * @property string $_name
     */
    protected $_name;

    /**
     * @brief Current position
     * @internal
     * @property integer $_count
     */
    protected static $_count = 1;

    /**
     * @brief Constructor
     * 
     * See axRadioGroupHelper::addOptions() for more details about the @c $values parameter.
     * 
     * @param string $name
     * @param array $values @optional @default{array()}
     */
    public function __construct ($name, $values = array()) {
        parent::__construct('span');
        $this->_name = $name;

        if (!empty($values))
            $this->addOptions($values);
    }

    /**
     * @brief Add an option (a checkbox) to the group
     * @param string $label
     * @param scalar $value
     * @return axRadioGroupHelper
     */
    public function addOption ($label, $value) {
        $this->_children[] = axInputHelper::export($this->_name,'radio',$value)->setId($id='radio'.(self::$_count ++));
        $this->_children[] = axLabelHelper::export($label, $id);
        return $this;
    }

    /**
     * @brief Add multiple options at once.
     * 
     * The @c $values parameter is an associative array which keys are label values and value are checkbox value.
     * Example:
     * @code
     * $radio_group = new RadioGroupHelper('test');
     * $radio_group->addOptions(array(
     *     'Label A' => 1,
     *     'Label B' => 2,
     *     'Label C' => 3,
     * ));
     * echo $radio_group; // Will generate the following HTML
     * // <span>
     * //   <label for="radio1">Label A</label><input type="radio" name="test" value="1" id="radio1" />
     * //   <label for="radio2">Label B</label><input type="radio" name="test" value="2" id="radio2" />
     * //   <label for="radio3">Label C</label><input type="radio" name="test" value="3" id="radio3" />
     * // </span>
     * @encode
     * 
     * @param array $values
     * @return axRadioGroupHelper
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
     * @copydoc axRadioGroupHelper::__construct()
     * @brief Constructor static alias
     * @return axRadioGroupHelper
     */
    public static function export ($name, $values = array()) {
        return new self ($name, $values);
    }
}