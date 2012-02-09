<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Class not found exception
 * 
 * Exception thrown if a class load fails.
 * 
 * @author Delespierre
 * @since 1.1.4
 * @package libaxiom
 * @subpackage exception
 */
class axClassNotFoundException extends Exception {
	
	/**
	 * Defualt constructor
	 * @param string $class the class name
	 */
	public function __construct ($class) {
		parent::__construct("Class {$class} not found");
	}
}