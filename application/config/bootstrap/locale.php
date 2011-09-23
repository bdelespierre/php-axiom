<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/Lang.class.php';

Lang::setConfig(array(
    'locale' => 'fr',
    'locales' => array('en', 'fr'),
    'base_url' => '/axiom/',
));

Lang::loadLanguage();
