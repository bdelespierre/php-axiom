<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * INI Configuration file parser
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage configuration
 */
class Axiom_IniConfiguration implements Axiom_Configuration {

    /**
     * INI structure cache
     * @var array
     */
    protected $_ini;

    /**
     * INI Tree structure
     * @var Axiom_ConfigurationItem
     */
    protected $_tree;

    /**
     * Default constructor
     * @param string $file
     * @param string $section [optional]
     * @throws Axiom_MissingFileException
     * @throws RuntimeException
     */
    public function __construct ($file, $section) {
        if (!is_file($file))
            throw new Axiom_MissingFileException($file);

        if (!$ini = parse_ini_file($file, true))
            throw new RuntimeException("Cannot parse $file");

        foreach (array_keys($ini) as $key) {
            if (($offset = strpos($key, ':')) !== false && isset($ini[trim(substr($key, $offset+1))]))
                $ini[$key] += $ini[trim(substr($key, $offset+1))];
            if (strpos(trim($key), $section) === 0)
                $section = $key;
        }

        $this->_ini = $ini;
        $this->_generateTree($section);
    }

    /**
     * Generates the tree structure using the INI structure
     * @param string $section
     * @throws RuntimeException
     * @return void
     */
    protected function _generateTree ($section) {
        if (!isset($this->_ini[$section]))
            throw new RuntimeException("Unable to find section $section");
        	
        $this->_tree = new Axiom_ConfigurationItem;
        foreach ($this->_ini[$section] as $key => $value) {
            $p = explode('.', $key);
            $c = $this->_tree;
            foreach ($p as $k)
                $c = $c->__get($k);
            $c->setValue($value);
        }
    }

    /**
     * (non-PHPdoc)
     * @seeAxiom_Configuration::__get()
     */
    public function __get ($key) {
        return $this->_tree->$key;
    }

    /**
     * Switch between INI sections
     * @param string $section
     * @return IniConfiguration
     */
    public function switchSection ($section) {
        $this->_generateTree($section);
        return $this;
    }
}