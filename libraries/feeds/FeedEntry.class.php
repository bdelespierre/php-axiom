<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

class FeedEntry {
    
	protected $_id;
	
	protected $_title;
	
	protected $_date;
	
	protected $_author;
	
	protected $_description;
	
	protected $content;

	protected $link;

	protected $comments;
	
	public function __construct ($values = array()) {
	    if (!empty($values)) {
	        foreach ($values as $key => $value) {
                $this->$key = $value;
	        }
	    }
	}
	
    public function __get ($key) {
        if (method_exists($this, $method = "get" . ucfirst($key)))
            return $this->$method();
        else
            throw new InvalidArgumentException("$key does not exist", 4001);
    }
    
    public function __set ($key, $value) {
        if (method_exists($this, $method = "get" . ucfirst($key)))
            return $this->$method($value);
        else
            throw new InvalidArgumentException("$key does not exist", 4001);
    }
    
    public function getId () {
        return $this->_id;
    }
    
    public function setId ($id) {
        $this->_id = (int)$id;
    }
    
    public function getTitle () {
        return $this->_title;
    }
    
    public function setTitle ($title) {
        $title = strip_tags($title);
        $this->_title = $title;
    }
    
    public function getDate () {
        return $this->_date;
    }
    
    public function setDate ($date) {
        if ($time = strtotime($date))
            $date = date('r', $time);
        else
            throw new InvalidArgumentException("Invalid date format", 4002);
            
        $this->_date = $date;
    }
    
    public function getAuthor () {
        return $this->_author;
    }
    
    public function setAuthor (array $author) {
        $author = array_intersect_key($author, array_flip(array('mail', 'name', 'uri')));
        
        if (isset($author['mail']) && !Mail::validateEmail($author['mail']))
            throw new InvalidArgumentException("Invalid author email", 4003);
            
        if (isset($author['name']) && !$author['name'] = filter_var($author['name'], FILTER_SANITIZE_ENCODED))
            throw new InvalidArgumentException("Invalid author name", 4004);
            
        if (isset($author['uri']) && !filter_var($author['uri'], FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URI for author", 4005);
            
        if (empty($author))
            throw new InvalidArgumentException("Author description must contain at least a name or email or URI", 4006);
            
        $this->_author = $author;
    }
    
    public function getDescription () {
        return $this->_description;
    }
    
    public function setDescription ($description) {
        $description = strip_tags($description);
        $this->_description = $description;
    }
    
    public function getContent () {
        return $this->content;
    }
    
    public function setContent ($content) {
        $this->content = $content;
    }
    
    public function getLink () {
        return $this->link;
    }
    
    public function setLink ($url) {
        if (!$url = filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URL", 4007);
        $this->link = $url;
    }
    
    public function getComments () {
        return $this->comments;
    }
    
    public function setComments ($url) {
        if (!$url = filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URL", 4007);
        $this->comments = $url;
    }
}