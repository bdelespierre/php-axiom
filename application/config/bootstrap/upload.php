<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/uploader/qqFileUploader.class.php';

qqFileUploader::setConfig(array(
    'allowed_extensions' => array('jpeg', 'jpg', 'gif', 'png', 'svg', 'csv', 'xls', 'doc')
));