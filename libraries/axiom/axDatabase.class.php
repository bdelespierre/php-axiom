<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Database class
 * 
 * TODO long description
 * 
 * @author Delespierre
 * @since 1.2.0
 * @package libaxiom
 * @subpackage model
 */
class axDatabase extends PDO {
    
    /**
     * Create an `axModel` instance
     * 
     * If `$model` is a tablename, a generic `axMySQLObject` will be returne, if it's an `axModel` class name, an 
     * instance of this class will be returned.
     * Will return false in case of error or if the class identified by `$model` doesn't implement the `axModel`
     * interface and an `E_USER_WARNING` is emitted (we don't know its constructor and thus cannot create the object).
     * 
     * @see axModel::__construct
     * @param string $model The model class to use or the table name
     * @param mixed $id [optional] [default `null`] See the `axModel` constructor for more details about this parameter
     * @return `axModel` 
     */
    public function factory ($model, $id = null) {
        try {
            if (is_string($model) && strpos($mode, '.') !== false) {
                return new axMySQLObject($this, $model, $id);
            }
            elseif (class_exists($model, true)) {
                if (!in_array('axModel', class_implements($model)))
                    throw new RuntimeException("Class `{$model}` doesn't implements `axModel`");
                return new $model($this, $id);
            }
            else {
                return new axMySQLObject($this, $model, $id);
            }
        }
        catch (Exception $e) {
            trigger_error("Cannot construct the {$model} model object: " . $e->getMessage(), E_USER_WARNING);
            return false;
        }
    }
    
    /**
     * Invoke the `all` method over the specified model
     * 
     * If `$model` is a tablename, `axMySQLObject::all` implementation will be used, if it's an `axModel` class name, 
     * the class `all` implementation will be used.
     * Will return false in case of error or if the class identified by `$model` doesn't implement the `axModel`
     * interface and an `E_USER_WARNING` is emitted (we don't know whenever it has `all` or its prototype).
     * 
     * @see axModel::all
     * @param string $model The model class to use or the table name
     * @param array $search_params [optional] [default `array()`]
     * @param unknown_type $options [optional] [default `array()`]
     * @return axPDOStatementIterator 
     */
    public function fetchAll ($model, array $search_params = array(), array $options = array()) {
        try {
            if (is_string($model) && strpos($mode, '.') !== false) {
                return axMySQLObject::all($this, $search_params, $options, $model);
            }
            elseif (class_exists($model, true)) {
                if (!in_array('axModel', class_implements($model)))
                        throw new RuntimeException("Class `{$model}` doesn't implements `axModel`");
                return call_user_func(array($model, 'all'), $this, $search_params, $options);
            }
            else {
                return axMySQLObject::all($this, $search_params, $options, $model); 
            }
        }
        catch (Exception $e) {
            trigger_error("Cannot retrieve {$model} list: " . $e->getMessage(), E_USER_WARNING);
            return false;
        }
    }
}