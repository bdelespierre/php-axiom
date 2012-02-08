<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/feed/axFeed.class.php';

axFeed::setConfig(array(
    'type' => 'Rss',
));

axFeed::setMetaInf(array(
    'title' => 'Axiom Feed',
    'date' => date('r'),
    'author' => array(
    	'name' => 'Benjamin DELESPIERRE',
    	'mail' => 'benjamin.delespierre@gmail.com'),
    'lang' => axLang::getLocale(),
    'description' => 'Axiom Generic Feed',
));