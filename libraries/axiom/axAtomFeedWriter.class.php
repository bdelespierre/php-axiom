<?php
/**
 * @brief Atom feed writer class file
 * @file axAtomFeedWriter.class.php
 */

/**
 * @brief Atom Feed Writer.
 * 
 * Instances of this class are intended to be attached an axFeed instance to display this feed according to the Atom
 * format.
 * 
 * @class axAtomFeedWriter
 * @author Delespierre
 * @ingroup Feed
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @copyright http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axAtomFeedWriter extends axFeedWriter {
    
    /**
     * @brief Constructor
     * @param Feed $feed The feed to build
     */
    public function __construct (axFeed $feed) {
        parent::__construct($feed);
        
        $this->_atom = $this->appendChild(new DOMElement('feed'));
        $this->_atom->setAttribute('xmlns', 'http://www.w3.org/2005/atom');
        $this->_atom->setAttribute('xml:lang', $this->_feed->getLang());
        
        $this->buildFeedInfo();
        $this->buildItems();
    }
    
    /**
     * @copydoc axFeedWriter::buildFeedInfo()
     */
    protected function buildFeedInfo () {
        $this->_atom->appendChild(new DOMElement('id', $this->_feed->getId()));
        $this->_atom->appendChild(new DOMElement('title', $this->_feed->getTitle()));
        $this->_atom->appendChild(new DOMElement('updated', $this->_feed->getDate()));
        
        if ($link = $this->_feed->getLink()) {
            $link_el = $this->_atom->appendChild(new DOMElement('link'));
            $link_el->setAttribute('rel', 'alternate');
            $link_el->setAttribute('href', $url);
        }
        
        if ($author = $this->_feed->getAuthor()) {
            $author_el = $this->_atom->appendChild(new DOMElement('author'));
            
            if (!empty($author['name']))
                $author_el->appendChild(new DOMElement('name', $author['name']));
                
            if (!empty($author['mail']))
                $author_el->appendChild(new DOMElement('email', $author['email']));
                
            if (!empty($author['uri']))
                $author_el->appendChild(new DOMElement('uri', $author['uri']));
        }
    }
    
    /**
     * @copydoc axFeedWriter::buildItems()
     * @todo Add entry validation here
     */
    protected function buildItems () {
        foreach ($this->_feed->getEntries() as $entry) {
            $item = $this->_atom->appendChild(new DOMElement('entry'));
            
            $item->appendChild(new DOMElement('id', $entry->getId()));
            $item->appendChild(new DOMElement('title', $entry->getTitle()));
            $item->appendChild(new DOMElement('updated', date('c', strtotime($entry->getDate()))));
            
            if ($author = $entry->getAuthor()) {
                $author_el = $item->appendChild(new DOMElement('author'));
                
                if (!empty($author['name']))
                    $author_el->appendChild(new DOMElement('name', $author['name']));
                    
                if (!empty($author['mail']))
                    $author_el->appendChild(new DOMElement('email', $author['email']));
                    
                if (!empty($author['uri']))
                    $author_el->appendChild(new DOMElement('uri', $author['uri']));
            }
            
            if ($content = $entry->getContent()) {
                $content_el = $item->appendChild(new DOMElement('content'));
                $content_el->appendChild(new DOMCdataSection($content));
            }
            
            if ($link = $entry->getLink()) {
                $link_el = $item->appendChild(new DOMElement('link'));
                $link_el->setAttribute($link);
            }
        }
    }
}