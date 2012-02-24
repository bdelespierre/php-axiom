<?php
/**
 * @brief Database class file
 * @file axDatabase.class.php
 */

/**
 * @brief Database class
 * 
 * This class extends the native PHP PDO class so all PDO methods are available through axDatabase instances.
 * 
 * @link http://php.net/manual/en/book.pdo.php
 * @class axDatabase
 * @author Delespierre
 * @since 1.2.0
 * @ingroup Model
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axDatabase extends PDO {
    
    /**
     * @brief Create an axModel instance
     * 
     * If @c $model is a tablename, a generic axMySQLObject will be returned, if it's an axModel class, an  instance of 
     * this class will be returned.
     * Will return false in case of error or if the class identified by @c $model doesn't implement the axModel
     * interface and a @c E_USER_WARNING is emitted (we don't know its constructor and thus cannot create the object).
     * 
     * @see axModel::__construct
     * @param string $model The model class to use or the table name
     * @param mixed $id @optional @default{null} See axModel::__construct() more details about this parameter
     * @return axModel
     */
    public function factory ($model, $id = null) {
        try {
            if (is_string($model) && strpos($model, '.') !== false) {
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
     * @brief Invoke the axModel::all() method over the specified model
     * 
     * If @c $model is a tablename, axMySQLObject::all() implementation will be used, if it's an axModel class, 
     * the all() implementation of this class will be used.
     * Will return false in case of error or if the class identified by @c $model doesn't implement the axModel
     * interface and a @c E_USER_WARNING is emitted (we don't know whenever it has all() method or its prototype).
     * 
     * @see axModel::all
     * @param string $model The model class to use or the table name
     * @param array $search_params @optional @default{array()}
     * @param unknown_type $options @optional @default{array()}
     * @return axPDOStatementIterator 
     */
    public function fetchAll ($model, array $search_params = array(), array $options = array()) {
        try {
            if (is_string($model) && strpos($model, '.') !== false) {
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