<?php
/**
 * @file axDummy.class.php
 * @brief Dummy class
 */

/**
 * @brief Dummy Class
 *
 * INTERNAL PURPOSE ONLY, DO NOT USE THIS CLASS
 *
 * @class axDummy
 * @author Delespierre
 * @ingroup core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
final class axDummy {
    
    /**
     * Call anything, does nothing
     * @param string $m
     * @param array $a
     * @return axDummy
     */
    public function __call ($m,$a) {
        return new self;
    }
    
    /**
     * Get anything, get nothing
     * @param string $k
     * @return axDummy
     */
    public function __get ($k) {
        return new self;
    }
    
    /**
     * Set anything, does nothing
     * @param string $k
     * @param any $v
     */
    public function __set ($k,$v) {
        /** noop **/
    }
    
    /**
     * Retruns an empty string
     * @return string
     */
    public function __toString() {
        return "";
    }
}