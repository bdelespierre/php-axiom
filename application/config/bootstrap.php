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
Axiom::library()->add(AXIOM_APP_PATH, array('recursive' => true));

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

// @todo add section description

/*
//Axiom::log()->addLogger(new axTextLogger(AXIOM_APP_PATH . '/ressource/log/app.log'));

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * ROUTES
 * ---------------------------------------------------------------------------------------------------------------------
 */

// @todo add section description

Axiom::router()->connect('/{:lang::?}/{:controller}/{:action::?}');
Axiom::router()->connect('/{:lang::?}/', 'IndexController');
Axiom::router()->connect('/', 'IndexController');
