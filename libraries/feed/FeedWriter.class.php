<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Feed Writer Class
 *
 * @abstract
 * @author Delespierre
 * @package libaxiom
 * @subpackage feed
 */
abstract class FeedWriter extends DOMDocument {
    
    /**
     * Internal Feed instance
     * @var Feed
     */
    protected $_feed;
    
    /**
     * Default constructor
     * @param Feed $feed
     */
    public function __construct (Feed $feed) {
        parent::__construct('1.0', 'utf-8');
        $this->_feed = $feed;
    }
    
    /**
     * Build Feed Info
     * @return void
     */
    abstract protected function buildFeedInfo ();
    
    /**
     * Build items
     * @return void
     */
    abstract protected function buildItems ();
}