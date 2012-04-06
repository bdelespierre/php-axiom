<?php
/**
 * @brief Cache manager class
 * @file axCacheManager.class.php
 */

/**
 * @brief Cache manager class
 *
 * This class is intended to manipulate caches in a way that many cache can be created and attached to this instance
 * (just like a factory). This class is also responsible to serialize and cache its registered cache instances for
 * later use.
 *
 * @class axCacheManager
 * @author delespierre
 * @ingroup Cache
 * @since 1.2.3
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence
 */
class axCacheManager {

    /**
     * @brief The manager cache file (in fact, where caches themselves are persisted)
     * @var string
     */
    protected $_cacheFile;

    /**
     * @brief Registered caches
     * @var array
     */
    protected $_caches;

    /**
     * @brief Constructor
     *
     * The @c $cache_file parameter indicates to the manager where the cache objects have to be persisted so we
     * don't need to instanciate them at every request.
     *
     * @param string $cache_file The manager's cache file
     */
    public function __construct ($cache_file) {
        $this->_cacheFile = $cache_file;
        $this->_caches    = array();

        $this->_load();
    }

    /**
     * @brief Tells whenever a cache exists
     * @param string $name The cache name
     * @return boolean
     */
    public function hasCache ($name) {
        return isset($this->_caches[$name]);
    }

    /**
     * @brief Get a cache identified by its name
     *
     * Will return null if the cache doesn't exists.
     *
     * @param string $name The cache name
     * @return axCache
     */
    public function getCache ($name) {
        return $this->hasCache($name) ? $this->_caches[$name] : null;
    }

    /**
     * @brief Create a new cache and attach it to the manager (that is a factory method)
     *
     * Return the cache in case of success, false on failure.
     *
     * @param string $name The cache name
     * @param string $type The cache type (file, session, memory or any class that implements axCache)
     * @param string $uri The cache media URI
     * @param arrya $options @optional @default{array()} The cache options
     * @throws RuntimeException If the cache already exists
     * @throws InvalidArgumentException If the name is invalid
     * @throws axMissingClassException If the cache class to be used was not found
     * @return axCache
     */
    public function setCache ($name, $type, $uri, array $options = array()) {
        if ($this->hasCache($name))
            throw new RuntimeException("Cache $cache already exists");

        if (!$name || !is_string($name))
            throw new InvalidArgumentException("Invalid name");

        switch ($type) {
            case 'file':    $type = 'axFileCache';    break;
            case 'session': $type = 'axSessionCache'; break;
            case 'memory':  $type = 'axMemoryCache';  break;
        }

        if (!class_exists($type, true))
            throw new axMissingClassException($type);

        if (!in_array('axCache', class_implements($type)))
            throw new RuntimeException("Class $type doesn't implement axCache");

        $cache = new $type($uri, $options);
        $cache->register($this);

        return $this->_caches[$name] = $cache;
    }

    /**
     * @brief Save the manager's current state
     * @internal
     * @return boolean
     */
    public function save () {
        return (bool)file_put_contents($this->_cacheFile, serialize($this->_caches));
    }

    /**
     * @brief Load the previous state according the @c _cacheFile property
     * @return void
     */
    protected function _load () {
        if (!is_file($this->_cacheFile) || !is_writable($this->_cacheFile))
            return;

        if (!$buffer = file_get_contents($this->_cacheFile))
            return;

        if (!is_array($this->_caches = unserialize($buffer))) {
            unset($this->_caches);
            return;
        }

        foreach ($this->_caches as $cache)
            $cache->register($this);
    }
}