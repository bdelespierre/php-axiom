<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

abstract class FeedWriter extends DOMDocument {
    
    protected $_feed;
    
    public function __construct (Feed $feed) {
        parent::__construct('1.0', 'utf-8');
        $this->_feed = $feed;
    }
    
    abstract protected function buildFeedInfo ();
    
    abstract protected function buildItems ();
}