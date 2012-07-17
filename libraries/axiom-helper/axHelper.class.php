<?php
/**
 * @brief Helper interface file
 * @file axHelper.class.php
 */

/**
 * @brief Helper Interface
 *
 * Interface to be implemented by all HTML helpers.
 * 
 * @interface axHelper
 * @author Delespierre
 * @ingroup Helper
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
interface axHelper {

    /**
     * @brief Set any number of attributes at once
     * @param array $attributes
     * @return axHelper
     */
    public function setAttributes ($attributes);

    /**
     * @brief Set the node's value
     * @param mixed $value
     * @return axHelper
     */
    public function setValue ($value);

    /**
     * @brief Get the node's value (if any)
     * @return mixed
     */
    public function getValue ();

    /**
     * @brief Appends a node to current node and return it
     * @param mixed $node An axHelper instance or a string (or anything with a __toString implementation)
     * @return mixed
     */
    public function appendChild ($node);
    
    /**
     * @brief Prepend a node to the current node and return it
     * @param mixed $node An axHelper instance or a string (or anything with a __toString implementation)
     * @return mixed
     */
    public function prependCHild ($node);

    /**
     * @brief __toString implementation
     * 
     * Get a string represenation of current node and its children.
     * 
     * @return string
     */
    public function __toString ();
}

/**
 * @brief Helper Module
 * 
 * This module contains all render helpers classes and interfaces. A render helper aims to ease the description of
 * complex HTML structures (such as Form or Tables) by providing methods to accelerate view development.
 * 
 * @defgroup Helper
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */