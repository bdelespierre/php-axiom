<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * This is the bootstrap file of your application, and is loaded immediately after the front controller (`index.php`)
 * is invoked. This file initializes your Axiom application library, configuration and routes. All the other parts
 * of the application (such as localization, caching, logging etc.) are loaded 'on demand' when the application uses
 * them to save performances during the boostrap step. The Axiom main libraries are accessible through the Axiom static
 * class, they are configured using the parameters located in your configuration file (by default `config.ini`).
 */

/**
 * This is the path to the libraries used by application and must contain a copy of the `axiom` library.
 * @var string
 */
define('AXIOM_LIB_PATH', dirname(dirname(dirname(__FILE__))) . '/libraries');

/**
 * This is the application directory path.
 * @var string
 */
define('AXIOM_APP_PATH', dirname(dirname(dirname(__FILE__))) . '/application');

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * LIBRARIES
 * ---------------------------------------------------------------------------------------------------------------------
 */

/**
 * This section defines the libraries to be used by application.
 *
 * Libraries must be located in `/libraries` or `/application/libraries` or in the PHP include path if you're adding
 * the library by its name using `Axiom::library()->add(...)`.
 * You may also pass a path pointing to the library to this method to add it.
 * If you recently added a library and yet found the library innacessible, you may have to delete the cache file
 * (located at `/application/ressource/cache/library.cache.php`) to refresh the library structure.
 */
require_once AXIOM_LIB_PATH . '/util/functions.php';
require_once AXIOM_LIB_PATH . '/axiom/Axiom.class.php';
require_once AXIOM_LIB_PATH . '/axiom/axClassNotFoundException.class.php';
require_once AXIOM_LIB_PATH . '/axiom/axExtensionFilterIterator.class.php';
require_once AXIOM_LIB_PATH . '/axiom/axLibrary.class.php';

/**
 * Uncomment this line to disable cache globally
 */
//Axiom::$cache = false;

/**
 * Adding the Axiom core library
 */
Axiom::library()->add('axiom');

/**
 * Add the application
 */
Axiom::library()->add('controller');
Axiom::library()->add('model');

/**
 * Adding the browser capabilities detection library.
 */
//Axiom::library()->add('browser');

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * CONFIGURATION
 * ---------------------------------------------------------------------------------------------------------------------
 */

/**
 * This section initialize the configuration object used by application.
 *
 * The second parameter to the `Axiom::configuration` method correspond to the configuration section to be used.
 * Don't forget to change it to 'prod' when deploying the project on its production environment. The configuration
 * parameters can be read anytime using the following syntax.
 *
 * Usage:
 * * Axiom::configuration()->key->subkey->subsubkey
 * * // or (to get the native value)
 * * Axiom::configuration()->key->subkey->subsubkey->getValue();
 *
 * Note: object returned by the the configuration (no matter which depth level) are instances of axTreeItem. These
 * objects have __toString implementation so you can always convert them using the cast operator:
 * * $option = (string)Axiom::configuration()->option->suboption;
 * Another possibility to get the native parameter type is to use the getValue method on the axTreeItem:
 * * $option = Axiom::configuration()->option->suboption->getValue();
 *
 * See axIniConfiguration for more details about this behavior.
 */
Axiom::configuration(AXIOM_APP_PATH . '/config/config.ini', 'dev');

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * LOGGING
 * ---------------------------------------------------------------------------------------------------------------------
 */

/**
 * This section initialize the logger
 *
 * No parameter is required to initialiez the log manager (`Axiom::log()`).
 * You may register log entries using
 * * `Axiom::log()->debug($message)`
 * * `Axiom::log()->notice($message)`
 * * `Axiom::log()->warning($message)`
 * * `Axiom::log()->error($message)`
 * * `Axiom::log()->message($message, $level)`
 *
 * Using the last form, the `$level` parameter is an integer that describe the error level, you may use
 * * axLogger::ERR     for errors         (value 1)
 * * axLogger::NOTICE  for notices        (value 2)
 * * axLogger::WARNING for warnings       (value 4)
 * * axLogger::DEBUG   for debug messages (value 8)
 * Or, at your option, any greater value that is a power of 2 (16,32,64,128 and so on), in this case, the severity
 * level will be set to 'User' by default (see `axLogger::message` for more details about this behavior).
 *
 * Note: All loggers registered through `Axiom::log()->addLogger()` are connected each other forming a chain, thus the
 * message that is recieved by `Axiom::log()->xxx($message)` will get through all the registered loggers, it'll be
 * recorded by them only if the level matches the logger's mask (see `axLogger::__construct`).
 *
 * To disable the logs, simply comment these lines or set the `log` key to `Off` in config.ini.
 * You may add as many loggers you need by calling `Axiom::log()->addLogger()`.
 */

/**
 * General log
 */
Axiom::log()->addLogger('axTextLogger', AXIOM_APP_PATH . '/ressource/log/app.log');

/**
 * Error log
 */
Axiom::log()->addLogger('axTextLogger', AXIOM_APP_PATH . '/ressource/log/error.log', 5);

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * ROUTES
 * ---------------------------------------------------------------------------------------------------------------------
 */

// @todo add section description

Axiom::router()->connect('/{:lang::?}/{:controller}/{:action::?}');
Axiom::router()->connect('/{:lang::?}/', 'IndexController');
Axiom::router()->connect('/', 'IndexController');
