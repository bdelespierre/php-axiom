<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

define('AXIOM_VERSION', '1.1.4');
define('AXIOM_LIB_PATH', dirname(dirname(dirname(__FILE__))) . '/libraries');
define('AXIOM_APP_PATH', dirname(dirname(dirname(__FILE__))) . '/application');

require_once AXIOM_LIB_PATH . '/axiom/Axiom.class.php';
require_once AXIOM_LIB_PATH . '/axiom/axClassNotFoundException.class.php';
require_once AXIOM_LIB_PATH . '/axiom/axExtensionFilterIterator.class.php';
require_once AXIOM_LIB_PATH . '/axiom/axLibrary.class.php';

Axiom::library()->add('axiom')->add('browser');

var_dump(class_exists('axHelper', true));
var_dump(class_exists('axColHelper', true));




