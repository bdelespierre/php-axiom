<?php
/**
 * @brief Module manager class file
 * @file axModuleManager.class.php
 */

/**
 * @brief Module Manager
 *
 * @todo Module manager long description
 * @class axModuleManager
 * @author Delespierre
 * @ingroup Core
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axModuleManager {
    
    /**
     * @brief Cache file
     * @var string
     */
    const CACHE_FILE = "module.cache.php";
    
    /**
     * @brief Modules path
     * @property string $_path
     */
    protected $_path;
    
    /**
     * @brief Axiom version
     * @property string $_axiomVersion
     */
    protected $_axiomVersion;
    
    /**
     * @brief Options
     * @property array $_options
     */
    protected $_options;
    
    /**
     * @brief Module descriptors
     * @property array $_modules
     */
    protected $_modules;
    
    /**
     * @brief Constructor
     * 
     * Options:
     * @li check_dependencies [boolean] wherever to check dependencies or not
     * @li cache_dir [string | false] false will disable caching
     * 
     * @param string $path The path to the modules directory
     * @param string $axiom_version Used during dependencies check
     * @param array $options @optional @default{array()} see above
     */
    public function __construct ($path, $axiom_version, array $options = array()) {
        $default = array(
            'check_dependencies' => true,
            'cache_dir' => false,
        );
        
        if (!$this->_path = realpath($path)) {
            throw new axMissingFileException($path);
        }
        
        $this->_axiomVersion = $axiom_version;
        $this->_options      = $options + $default;
        $this->_modules      = array();
    }
    
    /**
     * @brief Get available modules
     * @return array
     */
    public function getModules () {
        if (empty($this->_modules)) {
            if ($this->_options['cache_dir'] && is_readable($c = $this->_options['cache_dir'] . '/' . self::CACHE_FILE)) {
                require $c;
                $this->_modules = $modules;
            }
            else {
                $directories = new DirectoryIterator($this->_path);
                $iterator    = new axDirectoryFilterIterator($directories, array('.', '..', '.svn', '.git'));
                foreach ($iterator as $item) {
                    $this->getInformations((string)$item);
                }
                $this->_cache();
            }
        }
        return $this->_modules;
    }
    
	/**
     * @brief Check if the module exists
     * @param string $module
     * @return boolena
     */
    public function exists ($module) {
        return array_key_exists($module, $this->getModules()) && $this->_modules[$module];
    }
    
    /**
     * @brief Get module meta-inf.
     * 
     * Wil return false in case of error.
     *  
     * @param string $module
     * @return array
     */
    public function getInformations ($module) {
        if (!empty($this->_modules[$module]))
            return $this->_modules[$module];
        
        if (!is_file($p = $this->_path . "/$module/module.ini"))
            return false;
        
        if (($meta = parse_ini_file($p, false)) === false)
            return false;
            
        $meta['path'] = $this->_path . "/$module";
        return $this->_modules[$module] = $meta;
    }
    
    /**
     * @brief Load the given module
     * @param string $module
     * @return boolean
     */
    public function load ($module) {
        if (!$this->exists($module))
            return false;
            
        if (!$meta = $this->getInformations($module))
            throw new RuntimeException("Module {$module} informations are not avaialble, check that module.ini isn't missing", 2050);
        
        if ($this->_options['check_dependencies'] && !$this->_checkDependencies($module))
            throw new RuntimeException("Module {$module} dependencies check failed");

        if (!$this->_loadDependencies($module))
            throw new RuntimeException("Cannot load dependency module for {$module}");
        
        return (boolean)require_once $meta['path'] . "/config/bootstrap.php";
    }
    
    /**
     * @brief Check if updates are available for the given module
     * @param string $module
     * @return boolean
     */
    public function checkUpdates ($module) {
        // TODO axModuleManager::checkUpdates
    }
    
    /**
     * @brief Get the given module dependencies
     * @param string $module
     * @throws RuntimeException If the module doesn't exists
     * @return array
     */
    protected function _getDependencies ($module) {
        if (!isset($this->_modules[$module]) || !$this->_modules[$module])
            throw new RuntimeException("Unknown module {$module}");
            
        return isset($this->_modules[$module]['dependencies']) ? 
            $this->_modules[$module]['dependencies'] : array();
    }
    
    /**
     * @brief Check dependencies according to version numbers
     * @param string $module
     * @throws RuntimeException If a module's meta-infs cannot be found
     * @return boolean
     */
    protected function _checkDependencies ($module) {
        foreach ($this->_getDependencies($module) as $dep) {
            list($dep_module_name, $dep_module_version) = explode('-', $dep);
            
            if ($dep_module_name == 'axiom') {
                if (!self::_compareVersions($this->_axiomVersion, $dep_module_version))
                    return false;
                continue;
            }
            if (!$this->exists($dep_module_name))
                return false;
            
            if (!$dep_meta = $this->getInformations($dep_module_name))
                throw new RuntimeException("Cannot load dependencie module information");
                
            if (!self::_compareVersions($dep_meta['version'], $dep_module_version))
                return false;
        }
        return true;
    }
    
    /**
     * @brief Load dependencies for the given module
     * @param string $module
     * @return boolean
     */
    protected function _loadDependencies ($module) {
        foreach ($this->_getDependencies($module) as $module => $dep) {
            list($dep_module_name,$dep_module_version) = explode('-', $dep);
            
            if ($dep_module_name == 'axiom')
                continue;
            
            try {
                if (!$this->load($dep_module_name)) {
                    return false;
                }
            }
            catch (Exception $e) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * @brief Store modules informations for later use
     * 
     * Will do nothing if cache is disabled
     * 
     * @return boolean
     */
    protected function _cache () {
        if (!$this->_options['cache_dir'])
			return false;
		
		$buffer = '<?php $modules=' . var_export($this->_modules, true) . '; ?>';
		return (boolean)file_put_contents($this->_options['cache_dir'] . '/' . self::CACHE_FILE, $buffer);
    }
    
    /**
     * @brief Parses a version to an integer
     *
     * @param string $version
     * @return interger
     */
    protected static function _parseModuleVersion ($version) {
        list($maj,$min,$build) = explode('.', $version);
        return $maj * 10000 + $min * 100 + $build;
    }
    
    /**
     * @brief Check if the left version is higher than the right version
     *
     * Both parameters can be either strings or integer so these calls are equivalents:
     * @code
     * self::_compareVersion('1.2.3', '1.0.2');
     * self::_compareVersion(100203, 100002);
     * @endcode
     *
     * @param mixed $version_a
     * @param mixed $version_b
     * @return boolean
     */
    protected static function _compareVersions ($version_a, $version_b) {
        if (is_string($version_a))
            $version_a = self::_parseModuleVersion($version_a);
            
        if (is_string($version_b))
            $version_b = self::_parseModuleVersion($version_b);
            
        return $version_a >= $version_b;
    }
}