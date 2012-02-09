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

// OLD
define('LIBRARY_PATH',     AXIOM_LIB_PATH);
define('APPLICATION_PATH', AXIOM_APP_PATH);


require dirname(__FILE__) . '/bootstrap/settings.php';

require dirname(__FILE__) . '/bootstrap/util.php';

require dirname(__FILE__) . '/bootstrap/autoload.php';

require dirname(__FILE__) . '/bootstrap/log.php';

require dirname(__FILE__) . '/bootstrap/session.php';

require dirname(__FILE__) . '/bootstrap/locale.php';

require dirname(__FILE__) . '/bootstrap/connection.php';

require dirname(__FILE__) . '/bootstrap/routes.php';

require dirname(__FILE__) . '/bootstrap/modules.php';

require dirname(__FILE__) . '/bootstrap/views.php';

require dirname(__FILE__) . '/bootstrap/feed.php';

require dirname(__FILE__) . '/bootstrap/upload.php';

require dirname(__FILE__) . '/bootstrap/captcha.php';
