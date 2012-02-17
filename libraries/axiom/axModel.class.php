<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Interface for all model entities
 * 
 * A model entity consist in a single row, extracted from an RDBMS table.
 * 
 * IMPORTANT: You MUST implement this interface if you want your classes to be generated throught the `axDatabase` 
 * factory.
 * 
 * @author Delespierre
 * @since 1.2.0
 * @package libaxiom
 * @subpackage model
 */
interface axModel {
    
    /**
     * Default constructor
     * 
     * @param PDO $pdo The database connection instance
     * @param mixed $id [optional] [default `null`] The ID of the row to match
     */
    public function __construct (PDO $pdo, $id = null);
    
    /**
     * Create a record
     * 
     * Returns the current record in case of success (instance of `axModel`) or false on failure.
     * 
     * @param array $data The data to be recorded
     * @return axModel
     */
    public function create (array $data);
    
    /**
     * Fetches data from a record according to its id
     * 
     * Returns the current record in case of success (instance of `axModel`) or false on failure.
     * 
     * @param mixed $id The ID of the record
     * @return axModel
     */
    public function retrieve ($id);
    
    /**
     * Update a record, optionaly added with the `$data` parameter 
     * 
     * Returns the current record in case of success (instance of `axModel`) or false on failure.
     * 
     * @param array $data [optional] [default `array()`] The data to add to the record
     * @return axModel
     */
    public function update (array $data = array());
    
    /**
     * Delete a record
     * 
     * Returns the deletion status.
     * 
     * @return boolean
     */
    public function delete ();
    
    /**
     * Get a list of records, optionaly filtered by `$search_params` and `$options` parameters
     * 
     * Returns the list as an instance of `PDOStatementIterator` in case of success, false on failure.
     * 
     * @param PDO $pdo
     * @param array $search_params [optional] [default `array()`] The filtering parameters
     * @param array $options [optional] [default `array()`] The options parameters
     * @return axPDOStatementIterator
     */
    public static function all (PDO $pdo, array $search_params = array(), array $options = array());
    
    /**
     * Get the record's original table name
     * @return string
     */
    public function getTable ();
    
    /**
     * Get the record's original table columns
     * @return array
     */
    public function getColumns ();
        
    /**
     * Get the record's native data
     * @return array
     */
    public function getData ();
    
}