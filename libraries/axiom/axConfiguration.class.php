<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Configuration Interface
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage configuration
 */
interface axConfiguration extends IteratorAggregate {
    
    /**
     * Default constructor
     * @param string $file
     * @param string $section
     */
    public function __construct ($file, $section, $cache_dir = false);
    
    /**
     * Parameter getter
     * @param string $key
     * @return axConfiguration
     */
    public function __get ($key);
}