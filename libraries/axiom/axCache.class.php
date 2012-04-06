<?php
/**
 * @brief Cache interface file
 * @file axCache.class.php
 */

/**
 * @brief Cache interface
 *
 * All cache objects must implements this interface
 *
 * @class axCache
 * @author delespierre
 * @ingroup Cache
 * @since 1.2.3
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence
 */
interface axCache extends Serializable {

    /**
     * @brief Constructor
     * @param string $uri The media URI
     * @param array $options @optional @default{array()} The cache options
     */
    public function __construct ($uri, array $options = array());

    /**
     * @brief Get a cached entry identified by its key
     *
     * Return the entry on cache hit, false on cache miss.
     *
     * @param string $key The entry key
     * @return mixed
     */
    public function get ($key);

    /**
     * @brief Store an entry in cache and give it a key and optionaly a lifetime
     *
     * If @c $ttl parameter is left to @c null, the cache's default lifetime will be used. An entry with a 0 lifetime
     * will never expire.
     * Will return true if the data was successfuly stored, false otherwise.
     *
     * @warning If the cache serialize option is set to true and you try to store and object that doesn't implement
     * the Serializable interface a warning will be raised.
     *
     * @param string $key The entry key
     * @param mixed $value The entry value
     * @param integer $ttl The lifetime of the entry
     * @return boolean
     */
    public function set ($key, $value, $ttl = null);

    /**
     * @brief Remove a cache entry
     * @param string $key The entry key
     * @return boolean
     */
    public function remove ($key);

    /**
     * @brief Tells if a cache entry is valid (entry exists and is not outdated)
     * @param string $key The entry value
     */
    public function validate ($key);

    /**
     * @brief Flush the cache (erase all its entries)
     * @return boolean
     */
    public function flush ();

    /**
     * @brief Get a cache option identified by its name
     * @param string $name The option name
     * @return mixed
     */
    public function getOption ($name);

    /**
     * @brief Get the cache options
     * @return array
     */
    public function getOptions ();

    /**
     * @brief Set the default cache lifetime (see axCache::set)
     *
     * @note Calling this method won't affect existing cache entries lifetime.
     *
     * @param integer $lifetime The default lifetime for cached entries
     * @return boolean
     */
    public function setLifeTime ($lifetime);

    /**
     * @brief Set an option
     * @param string $name The option's name
     * @param mixed $value The option's value
     * @return boolean
     */
    public function setOption ($name, $value);

    /**
     * @brief Get the cache type as string
     * @return string
     */
    public function getType ();

    /**
     * @brief Notify the manager that the cache has changed (new entry stored, option changed.)
     * @warning This method exists for internal puroposes and should not be called directly unless you know what you're
     * doing.
     * @internal
     * @return boolean
     */
    public function update ();

    /**
     * @brief Register the cache's manager
     * @warning This method exists for internal puroposes and should not be called directly unless you know what you're
     * doing.
     * @internal
     * @param axCacheManager $manager The cache's manager
     * @return void
     */
    public function register (axCacheManager $manager);
}

/**
 * @brief Cache Module
 *
 * This module provides caching facilities.
 *
 * @defgroup Cache
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */