<?php
/**
 * @brief Class not found exception class file
 * @file axClassNotFoundException.class.php
 */

/**
 * @brief Exception thrown if a class load fails
 * 
 * @class axClassNotFoundException
 * @author Delespierre
 * @since 1.1.4
 * @ingroup Exception
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axClassNotFoundException extends Exception {
	
	/**
	 * @brief Constructor
	 * @param string $class The class name
	 */
	public function __construct ($class) {
		parent::__construct("Class {$class} not found");
	}
}

/**
 * @brief Exception Module
 * 
 * This module contains custom exception classes used by the framework.
 * 
 * @defgroup Exception
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @copyright http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */