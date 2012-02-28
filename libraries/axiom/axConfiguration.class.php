<?php
/**
 * @brief Configuration interface file
 * @file axConfiguration.class.php
 */

/**
 * @brief Configuration Interface
 *
 * Class to be implemented by configuration classes in order to be used along with Axiom class
 * 
 * @see Axiom::configuration()
 * @class axConfiguration
 * @author Delespierre
 * @since 1.2.0
 * @ingroup Configuration
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
interface axConfiguration extends IteratorAggregate {
    
    /**
     * @brief Constructor
     * @param string $file The configuration file (or URI)
     * @param string $section The configuration section to be used
     * @param string $cache_dir @optional @default{false} The cache directory for the configuration parameters or false
     * if you don't want to use cache
     */
    public function __construct ($file, $section, $cache_dir = false);
    
    /**
     * @brief Parameter getter
     * 
     * All configuration item should be accessible through the @c -> operator 
     * 
     * @param string $key The parameter's name
     * @return axConfiguration
     */
    public function __get ($key);
}

/**
 * @brief Configuration Module
 * 
 * This module contains all the classes and interfaces necessary for configuration manipulation.
 * 
 * @defgroup Configuration
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */