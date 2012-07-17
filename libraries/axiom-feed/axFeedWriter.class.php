<?php
/**
 * @brief Feed writer class file
 * @file axFeedWriter.class.php
 */

/**
 * @brief Feed Writer abstract class
 *
 * Base class that all feed writer have to implement in order to be able to build feed via axFeed class.
 *
 * @class axFeedWriter
 * @author Delespierre
 * @ingroup Feed
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
abstract class axFeedWriter extends DOMDocument {
    
    /**
     * @brief Internal Feed instance
     * @property Feed $_feed
     */
    protected $_feed;
    
    /**
     * @brief Constructor
     * @param Feed $feed
     */
    public function __construct (axFeed $feed) {
        parent::__construct('1.0', 'utf-8');
        $this->_feed = $feed;
    }
    
    /**
     * @brief Build Feed Info
     * @abstract
     * @return void
     */
    abstract protected function buildFeedInfo ();
    
    /**
     * @brief Build items
     * @return void
     */
    abstract protected function buildItems ();
}