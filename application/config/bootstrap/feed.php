<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/feeds/Feed.class.php';

Feed::setConfig(array(
    'type' => 'Rss',
));

Feed::setMetaInf(array(
    'title' => 'Axiom Feed',
    'date' => date('r'),
    'author' => array(
    	'name' => 'Benjamin DELESPIERRE',
    	'mail' => 'benjamin.delespierre@gmail.com'),
    'lang' => Lang::getLocale(),
    'description' => 'Axiom Generic Feed',
));