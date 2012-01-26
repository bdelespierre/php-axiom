<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/core/Axiom_ViewManager.class.php';

Axiom_ViewManager::setConfig(array(
    'header' => 'html',
    'layout_file' => 'default',
    'layout_content_var' => 'content',
));

Axiom_ViewManager::addLayoutVars(array(
    'lang' => Axiom_Lang::getLocale(),
    'description' => 'Sample Description',
    'keywords' => array('foo', 'bar'),
    'title' => 'Default Title',
    'base_url' => getenv('REWRITE_BASE')
));