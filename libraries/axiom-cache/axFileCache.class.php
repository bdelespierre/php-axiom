<?php
/**
 * @brief File cache class
 * @file axFileCache.class.php
 */

/**
 * @brief File Cache class
 *
 * This class uses files to store cache entries and creates one new file per entry using the following pattern:
 * @code
 * <path>/<prefix><entry name><extension>
 * @endcode
 * where <path> is defined by the cache's URI. <prefix> and <extension> are set as options.
 *
 * Valid options are:
 * @li string @c prefix default @c '' The prefix for created files
 * @li string @c extension default @c '.cache' The extension for created files
 * @li boolean @c base64 default @c false Whenever or not to encode files in base64
 * @li integer @c lifetime default 3600 The cache entries' default lifetime
 * @li boolean @c serialize default false Whenever or not to serialize/unserialize data that are being cached
 * @li boolean @c silent default false Whenever or not to desactivate warnings
 *
 * @class axFileCache
 * @author delespierre
 * @ingroup Cache
 * @since 1.2.3
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence
 */
class axFileCache extends axBaseCache {

    /**
     * @brief Constructor
     *
     * The @c $uri parameter must be a valid writeable folder to store files in. You may use the following forms:
     * @li relative/path/
     * @li /absolute/path
     * @li file:///any/path
     *
     * @param string $uri The cache media URI
     * @param array $options @optional @default{array()} The cache options
     * @throws InvalidArgumentException If URI is invalid
     * @throws RuntimeException If the path is not writeable
     */
    public function __construct ($uri, array $options = array()) {
        $options += array(
                'prefix'    => '',
                'extension' => '.cache',
                'base64'    => false,
        );

        parent::__construct($uri, $options);

        if (strpos($this->_uri, 'file://') !== false)
            $this->_uri = substr($this->_uri, 7);

        if (!is_dir($this->_uri))
            throw new InvalidArgumentException("{$this->_uri} is not a valid directory");

        if (!is_writable($this->_uri))
            throw new RuntimeException("{$this->_path} is not writeable");
    }

    /**
     * @copybrief axCache::getType
     * @copydoc axCache::getType
     */
    public function getType () {
        return 'file';
    }

    /**
     * @copybrief axBaseCache::_read
     * @copydoc axBaseCache::_read
     */
    protected function _read ($data) {
        $filename = $this->_getPath($data);
        if (!is_file($filename) || !is_readable($filename))
            return false;

        if (!$buffer = file_get_contents($filename))
            return false;

        return $this->_options['base64'] ? base64_decode($buffer) : $buffer;
    }

    /**
     * @copybrief axBaseCache::_write
     * @copydoc axBaseCache::_write
     */
    protected function _write ($data, $value) {
        $filename = $this->_getPath($data);

        if ($this->_options['base64'])
            $value = base64_encode((string)$value);

        return (bool)file_put_contents($filename, $value);
    }

    /**
     * @copybrief axBaseCache::_erase
     * @copydoc axBaseCache::_erase
     */
    protected function _erase ($data) {
        $filename = $this->_getPath($data);
        if (!is_file($filename))
            return true;

        return unlink($filename);
    }

    /**
     * @brief Get the complete filename according to instance's parameters
     * @internal
     * @param string $name The data to be recorded name
     * @return string
     */
    protected function _getPath ($name) {
        return rtrim($this->_uri, '/') . "/{$this->_options['prefix']}{$name}{$this->_options['extension']}";
    }
}