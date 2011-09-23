<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/ViewManager.class.php';

ViewManager::setConfig(array(
    'header' => 'html',
    'layout_file' => 'default',
    'layout_content_var' => 'content',
));

ViewManager::addLayoutVars(array(
    'lang' => Lang::getLocale(),
    'description' => 'Sample Description',
    'keywords' => array('foo', 'bar'),
    'title' => 'Default Title',
));