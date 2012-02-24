<?php
/**
 * @brief Extension filter iterator class file
 * @file axExtensionFilterIterator
 */

/**
 * @brief Extention filter iterator
 * 
 * Iterator that filters files according to their extensions. Multiple extensions separated by comma are supported.
 * 
 * @class axExtensionFilterIterator
 * @author Delespierre
 * @since 1.1.4
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axExtensionFilterIterator extends FilterIterator {
	
	/**
	 * @brief Extensions to filter
	 * @property array $_ext;
	 */
	protected $_ext;
	
	/**
	 * @brief Constructor
	 * 
	 * You may pass as many extensions as you want by separating them with a comma character.
	 * 
	 * @param Iterator $iterator
	 * @param string $extension
	 */
	public function __construct (Iterator $iterator, $extension) {
		parent::__construct($iterator);
		$this->_ext = (strpos($extension, ',') !== false) ? explode(',', $extension) : (array)$extension;
	}
	
	/**
     * @brief FilterIterator::accept() implementation
     * @todo The filter validation is really poor and need to be reworked
     * @link http://www.php.net/manual/en/filteriterator.accept.php
     * @return boolean
     */
	public function accept () {
		foreach ($this->_ext as $ext) {
			if (strpos((string)$this->current(), $ext) !== false)
				return true;
		}
		return false;
	}
}