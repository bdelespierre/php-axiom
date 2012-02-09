<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Extention filter iterator
 * 
 * Filter iterators that filters files according
 * to their extensions. Multiple extensions separated by 
 * comma are supported.
 * 
 * @author Delespierre
 * @since 1.1.4
 * @package libaxiom
 * @subpackage iterator
 */
class axExtensionFilterIterator extends FilterIterator {
	
	/**
	 * Extensions to filter
	 * @var array
	 */
	protected $_ext;
	
	/**
	 * Defualt constructor
	 * 
	 * You may pass as many extensions as you want by separating them
	 * with a comma character.
	 * 
	 * @param Iterator $iterator
	 * @param string $extension
	 */
	public function __construct (Iterator $iterator, $extension) {
		parent::__construct($iterator);
		$this->_ext = (strpos($extension, ',') !== false) ? explode(',', $extension) : (array)$extension;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see FilterIterator::accept()
	 */
	public function accept () {
		foreach ($this->_ext as $ext) {
			if (strpos((string)$this->current(), $ext) !== false)
				return true;
		}
		return false;
	}
}