<?php
/**
 * @brief Base cache class
 * @file axBaseCache.class.php
 */

/**
 * @brief Base implementation for cache classes
 *
 * This classe implements all axCache methods so you just have to implement _read, _write and _erase in your
 * concrete cache class. You may still override its methods to change the default behavior.
 *
 * @class axBaseCache
 * @author delespierre
 * @ingroup Cache
 * @since 1.2.3
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence
 */
abstract class axBaseCache implements axCache {

    /**
     * @brief The cache URI
     * @proprerty string $_uri
     */
    protected $_uri;

    /**
     * @brief The cache options
     * @property array $_options
     */
    protected $_options;

    /**
     * @brief The cache manager (if the cache is attached to a manager)
     * @property axCacheManager $_manager
     */
    protected $_manager;

    /**
     * @brief Informations about entries that are being manipulated by the cache instance
     * @property array $_metadata
     */
    protected $_metadata;

    /**
     * @brief Read (physically) an entry identified by @c $data
     *
     * Returns the entry buffer (as a string) in case of success, false on failure.
     *
     * @param string $data The data to be read
     * @return string
     */
    abstract protected function _read ($data);

    /**
     * @brief Write (physically) an entry identified by @c $data using @c $value
     *
     * Returns true in case of success, false otherwise.
     * @note According to the axBaseCache::set method, values passed to this method will always be strings.
     *
     * @param string $data The data to be written
     * @param string $value The value to be written
     * @return boolean
     */
    abstract protected function _write ($data, $value);

    /**
     * @brief Erase (physically) an entry identified by @c $data
     *
     * Returns true in case of success, false otherwise.
     *
     * @param string $data The data to be erased
     * @return boolean
     */
    abstract protected function _erase ($data);

    /**
     * @brief Constructor
     * @param string $uri The cache's media URI
     * @param arrya $options @optional @default{array()} The cache options
     * @throws InvalidArgumentException In case of invalid URI
     */
    public function __construct ($uri, array $options = array()) {
        if (!$uri || !is_string($uri))
            throw new InvalidArgumentException("Invalid URI");

        $options += array(
                'lifetime'  => 3600,
                'serialize' => false,
                'silent'    => false,
        );

        $this->_uri      = $uri;
        $this->_options  = $options;
        $this->_metadata = array();
    }

    /**
     * @copybrief axCache::get
     * @copydoc axCaceh::get
     */
    public function get ($key) {
        if (!$this->validate($key))
            return null;
        
        $value = $this->_read($key);
        if ($this->_options['serialize'])
            $value = unserialize($value);
        
        return $value;
    }

    /**
     * @copybrief axCache::set
     * @copydoc axCache::set
     */
    public function set ($key, $value, $ttl = null) {
        if ($ttl === null)
            $ttl = $this->_options['lifetime'];

        if ($this->_options['serialize']) {
            if (is_object($value) && !$value instanceof Serializable && !$this->_options['slient'])
                trigger_error("It is unsafe to store object that doesn't implement Serializable");

            $value = serialize($value);
        }

        return $this->_write($key, $value) && ($this->_metadata[$key] = array(
                'created'  => time(),
                'lifetime' => $ttl,
        )) && $this->update();
    }

    /**
     * @copybrief axCache::remove
     * @copydoc axCache::remove
     */
    public function remove ($key) {
        return $this->validate($key) && $this->_erase($key) && $this->update();
    }

    /**
     * @copybrief axCache::validate
     * @copydoc axCache::validate
     */
    public function validate ($key) {
        if (!isset($this->_metadata[$key]))
            return false;

        $ttl = isset($this->_metadata[$key]['lifetime']) ?
        $this->_metadata[$key]['lifetime'] :
        $this->_options['lifetime'];

        return !$this->_metadata[$key]['created'] || (time() - $this->_metadata[$key]['created']) < $ttl;
    }

    /**
     * @copybrief axCache::flush
     * @copydoc axCache::flush
     */
    public function flush () {
        $result = true;
        foreach (array_keys($this->_metadata) as $key)
            $result &= $this->remove($key);
        return $result;
    }

    /**
     * @copybrief axCache::getOption
     * @copydoc axCache::getOption
     */
    public function getOption ($name) {
        return isset($this->_options[$name]) ? $this->_options[$name] : null;
    }

    /**
     * @copybrief axCache::getOptions
     * @copydoc axCache::getOptions
     */
    public function getOptions () {
        return $this->_options;
    }

    /**
     * @copybrief axCache::setLifeTime
     * @copydoc axCache::setLifeTime
     */
    public function setLifeTime ($lifetime) {
        return $this->setOption('lifetime', (int)$lifetime);
    }

    /**
     * @copybrief axCache::setOption
     * @copydoc axCache::setOption
     */
    public function setOption ($name, $value) {
        $this->_options[$name] = $value;
        return $this->udpate();
    }

    /**
     * @copybrief axCache::getType
     * @copydoc axCache::getType
     */
    public function getType () {
        return 'generic';
    }

    /**
     * @copybrief axCache::update
     * @copydoc axCache::update
     */
    public function update () {
        return isset($this->_manager) ? $this->_manager->save() : true;
    }

    /**
     * @copybrief axCache::register
     * @copydoc axCache::register
     */
    public function register (axCacheManager $manager) {
        $this->_manager = $manager;
    }

    /**
     * @brief Serialize implementation
     * @see Serializable::serialize()
     * @return string
     */
    public function serialize () {
        return serialize(array(
                'uri'      => $this->_uri,
                'options'  => $this->_options,
                'metadata' => $this->_metadata
        ));
    }

    /**
     * @brief Unserialize implementation
     * @see Serializable::unserialize()
     * @param string $serialized
     * @return void
     */
    public function unserialize ($serialized) {
        $struct = unserialize($serialized);
        if (!isset($struct['uri'], $struct['options'], $struct['metadata']))
            throw new RuntimeException("Unable to unserialize: object properties are corrupted");

        $this->_uri      = $struct['uri'];
        $this->_options  = $struct['options'];
        $this->_metadata = $struct['metadata'];
    }
}