<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

require_once LIBRARY_PATH . '/feed/Axiom_Feed.class.php';

Axiom_Feed::setConfig(array(
    'type' => 'Rss',
));

Axiom_Feed::setMetaInf(array(
    'title' => 'Axiom Feed',
    'date' => date('r'),
    'author' => array(
    	'name' => 'Benjamin DELESPIERRE',
    	'mail' => 'benjamin.delespierre@gmail.com'),
    'lang' => Axiom_Lang::getLocale(),
    'description' => 'Axiom Generic Feed',
));