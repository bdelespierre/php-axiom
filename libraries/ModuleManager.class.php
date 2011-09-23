<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Module Manager
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage ModuleManager
 */
class ModuleManager {
    
    /**
     * Module list cache
     * @var array
     */
    protected static $_module_list = array();
    
    protected static $_loaded_modules = array();
    
    protected static $_modules_infs = array();
    
    /**
     * Internal configuration
     * @var array
     */
    protected static $_config;
    
    /**
     * Set configuration
     * @param arrat $config = array()
     * @return void
     */
    public static function setConfig ($config = array()) {
        $default = array(
            'module_path' => APPLICATION_PATH . '/module',
            'check_dependencies_versions' => true,
        );
        self::$_config = array_merge($default, $config);
        
        if (empty(self::$_module_list)) {
            foreach (self::getAvailableModules() as $fileinfo) {
                if ($fileinfo->isDir())
                    self::$_module_list[] = $fileinfo->getFilename();
            }
        }
    }
    
    /**
     * Get available modules
     * @return DirectoryFilterIterator
     */
    public static function getAvailableModules () {
        $dir = new DirectoryIterator(self::$_config['module_path']);
        return new DirectoryFilterIterator($dir);
    }
    
    /**
     * Get module meta-inf.
     * @param string $module
     * @return array
     */
    public static function getInformations ($module) {
        if (isset(self::$_modules_infs[$module]))
            return self::$_modules_infs[$module];
        
        if (($infs = parse_ini_file(self::$_config['module_path'] . "/$module/module.ini", false)) !== false) {
            return self::$_modules_infs[$module] = $infs;
        }
        return false;
    }
    
    /**
     * Load the given module
     * @param strign $module
     * @return boolean
     */
    public static function load ($module) {
        if (!$module_inf = self::getInformations($module))
            throw new RuntimeException("Module {$module} informations are not avaialble, check that module.ini isn't missing", 2050);
            
        Log::debug("Loading module {$module}");
        Log::debug("Module infs " . json_encode($module_inf));
            
        if (!empty($module_inf['dependencies'])) {
            foreach ($module_inf['dependencies'] as $dep_module) {
                list($dep_module_name, $dep_module_version) = explode('-', $dep_module);
                
                if (isset(self::$_loaded_modules[$dep_module_name]))
                    continue;
                
                if (self::$_config['check_dependencies_versions']) {
                    if ($dep_module_name == 'axiom') {
                        if (!self::_compareVersions(AXIOM_VERSION, $dep_module_version))
                            throw new RuntimeException("Module {$module} is not compatible with current Axiom version", 2052);
                    
                        continue;
                    }
                    
                    if (!$dep_module_inf = self::getInformations($dep_module_name))
                        throw new RuntimeException("Cannot retrieve dependency module {$dep_module_name} informations", 2051);
                    
                    if (!self::_compareVersions($dep_module_inf['version'], $dep_module_version)) {
                        throw new RuntimeException("Dependency module {$dep_module_name} version ".
                        						   "mismatch the required version for {$module}");
                    }
                }
                
                self::load($dep_module_name);
            }
        }
        
        if (@include_once self::$_config['module_path'] . "/$module/config/bootstrap.php") {
            self::$_loaded_modules[$module] = $module;
            return true;
        }
        return false;
    }
    
    /**
     * Check if the module exists
     * @param string $module
     * @return boolena
     */
    public static function exists ($module) {
        return (bool)array_keys(self::$_module_list, $module, true);
    }
    
    /**
     * Check if updates are available for the given module
     * @param string $module
     * @return boolean
     */
    public static function checkUpdates ($module) {
        // TODO ModuleManager::checkUpdates
    }
    
    /**
     * Parses a version to an integer
     *
     * @param string $version
     * @return interger
     */
    protected static function _parseModuleVersion ($version) {
        list($maj,$min,$build) = explode('.', $version);
        return $maj * 10000 + $min * 100 + $build;
    }
    
    /**
     * Check if the left version is higher than the right version
     *
     * Both parameters can be either strings or integer so
     * these calls are equivalents:
     *   self::_compareVersion('1.2.3', '1.0.2');
     *   self::_compareVersion(100203, 100002);
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