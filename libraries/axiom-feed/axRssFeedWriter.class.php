<?php
/**
 * @brief RSS feed writer class file
 * @file axRssFeedWriter.class.php
 */

/**
 * @brief RSS Feed Writer Class
 *
 * @class axRssFeedWriter
 * @author Delespierre
 * @ingroup Feed
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axRssFeedWriter extends axFeedWriter {
    
    /**
     * @brief RSS element
     * @property DOMElement $_rss
     */
    protected $_rss;
    
    /**
     * @brief Channel element
     * @internal
     * @property DOMElement $_channel
     */
    protected $_channel;
    
    /**
     * @brief Constructor
     * @param axFeed $feed
     */
    public function __construct (axFeed $feed) {
        parent::__construct($feed);
        
        $this->_rss = $this->appendChild(new DOMElement('rss'));
        $this->_rss->setAttribute('version', '2.0');
        $this->_channel = $this->appendChild(new DOMElement('channel'));
        
        $this->buildFeedInfo();
        $this->buildItems();
    }
    
    /**
     * @copydoc axFeedWriter::buildFeedInfo()
     */
    protected function buildFeedInfo () {
        $this->_channel->appendChild(new DOMElement('title', $this->_feed->getTitle()));
        $this->_channel->appendChild(new DOMElement('link', $this->_feed->getLink()));
        $this->_channel->appendChild(new DOMElement('description', $this->_feed->getDescription()));
        $this->_channel->appendChild(new DOMElement('language', $this->_feed->getLang()));
        $this->_channel->appendChild(new DOMElement('lastBuildDate', $this->_feed->getDate()));
        $this->_channel->appendChild(new DOMElement('copyright'), $this->_feed->getCopyright());
    }
    
    /**
     * @copydoc axFeedWriter::buildItems()
     */
    protected function buildItems () {
        foreach ($this->_feed->getEntries() as $entry) {
            
            //! @todo add entry validation here.
            
            $item = $this->_channel->appendChild(new DOMElement('item'));
            $item->appendChild(new DOMText($entry->getContent()));
            $item->appendChild(new DOMElement('title', $entry->getTitle()));
            $item->appendChild(new DOMElement('link', $entry->getLink()));
            $item->appendChild(new DOMElement('description', $entry->getDescription()));
            
            if ($author = $entry->getAuthor()) {
                if (isset($author['mail']))
                    $item->appendChild(new DOMElement('author', $author['email']));
                elseif (isset($author['name']))
                    $item->appendChild(new DOMElement('author', $author['name']));
                elseif (isset($author['uri']))
                    $item->appendChild(new DOMElement('author', $author['uri']));
            }
            
            if ($date = $entry->getDate())
                $item->appendChild(new DOMElement('pubDate', $date));
            
            if ($comments = $entry->getComments())
                $item->appendChild(new DOMElement('comments', $comments));
            
            if ($id = $entry->getId())
                $item->appendChild(new DOMElement('guid', $id));
        }
    }
}