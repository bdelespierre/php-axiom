<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Feed Entry Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage feed
 */
class FeedEntry {
    
    /**
     * Entry ID
     * @var integer
     */
	protected $_id;
	
	/**
	 * Entry title
	 * @var string
	 */
	protected $_title;
	
	/**
	 * Entry date (RFC 2822)
	 * @var string
	 */
	protected $_date;
	
	/**
	 * Entry author
	 * @var string
	 */
	protected $_author;
	
	/**
	 * Entry description
	 * @var string
	 */
	protected $_decription;
	
	/**
	 * Entry content
	 * @var string
	 */
	protected $content;

	/**
	 * Entry link
	 * @var string
	 */
	protected $link;
    
	/**
	 * Entry comments URL
	 * @var string
	 */
	protected $comments;
	
	/**
	 * Default constructor.
	 *
	 * You may pass an array of attributes to initialize the FeedEntry propreties.
	 *
	 * @param array $values
	 */
	public function __construct (array $values = array()) {
	    if (!empty($values)) {
	        foreach ($values as $key => $value) {
                $this->$key = $value;
	        }
	    }
	}
	
	/**
	 * Getter functions helper
	 * @param string $key
	 * @throws InvalidArgumentException
	 * @return mixed
	 */
    public function __get ($key) {
        if (method_exists($this, $method = "get" . ucfirst($key)))
            return $this->$method();
        else
            throw new InvalidArgumentException("$key does not exist", 4001);
    }
    
    /**
     * Setter functions helper
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        if (method_exists($this, $method = "get" . ucfirst($key)))
            return $this->$method($value);
        else
            throw new InvalidArgumentException("$key does not exist", 4001);
    }
    
    /**
     * Get id
     * @return integer
     */
    public function getId () {
        return $this->_id;
    }
    
    /**
     * Set id.
     *
     * First parameter $id will be converted to integer
     *
     * @param mixed $id
     */
    public function setId ($id) {
        $this->_id = (int)$id;
    }
    
    /**
     * Get title
     * @return string
     */
    public function getTitle () {
        return $this->_title;
    }
    
    /**
     * Set title
     * @param string $title
     * @return void
     */
    public function setTitle ($title) {
        $title = strip_tags($title);
        $this->_title = $title;
    }
    
    /**
     * Get date (RFC 2822)
     * @return string
     */
    public function getDate () {
        return $this->_date;
    }
    
    /**
     * Set date.
     *
     * Date parameter may be integer or string;
     * it will be converted to RFC 2822 date.
     *
     * @param mixed $date
     * @return void
     */
    public function setDate ($date) {
        if ($time = strtotime($date))
            $date = date('r', $time);
        else
            throw new InvalidArgumentException("Invalid date format", 4002);
            
        $this->_date = $date;
    }
    
    /**
     * Get author
     * @return string
     */
    public function getAuthor () {
        return $this->_author;
    }
    
    /**
     * Set author
     * @throws InvalidArgumentException
     * @param array $author
     * @return void
     */
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
    
    /**
     * Get description
     * @return string
     */
    public function getDescription () {
        return $this->_description;
    }
    
    /**
     * Set description
     * @param string $description
     * @return void
     */
    public function setDescription ($description) {
        $description = strip_tags($description);
        $this->_description = $description;
    }
    
    /**
     * Get content
     * @return string
     */
    public function getContent () {
        return $this->content;
    }
    
    /**
     * Set content
     * @param string $content
     * @return void
     */
    public function setContent ($content) {
        $this->content = $content;
    }
    
    /**
     * Get link
     * @return string
     */
    public function getLink () {
        return $this->link;
    }
    
    /**
     * Set link
     * @param string $url
     * @throws InvalidArgumentException
     * @return void
     */
    public function setLink ($url) {
        if (!$url = filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URL", 4007);
        $this->link = $url;
    }
    
    /**
     * Get comments url
     * @return string
     */
    public function getComments () {
        return $this->comments;
    }
    
    /**
     * Sets comments url
     * @param string $url
     * @throws InvalidArgumentException
     * @return void
     */
    public function setComments ($url) {
        if (!$url = filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URL", 4007);
        $this->comments = $url;
    }
}