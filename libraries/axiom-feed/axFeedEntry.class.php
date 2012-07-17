<?php
/**
 * @brief Feed entry class file
 * @file axFeedEntry.class.php
 */

/**
 * @brief Feed Entry Class
 *
 * @todo axFeedEntry long description
 * @class axFeedEntry
 * @author Delespierre
 * @ingroup Feed
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axFeedEntry {
    
    /**
     * @biref Entry ID
     * @property integer $_id
     */
	protected $_id;
	
	/**
	 * @brief Entry title
	 * @property string $_title
	 */
	protected $_title;
	
	/**
	 * @brief Entry date (RFC 2822)
	 * @property string $_date
	 */
	protected $_date;
	
	/**
	 * @brief Entry author
	 * @property string $_author
	 */
	protected $_author;
	
	/**
	 * @brief Entry description
	 * @property string $_description
	 */
	protected $_decription;
	
	/**
	 * @brief Entry content
	 * @property string $content
	 */
	protected $content;

	/**
	 * @brief Entry link
	 * @property string $link
	 */
	protected $link;
    
	/**
	 * @brief Entry comments URL
	 * @property string $comments
	 */
	protected $comments;
	
	/**
	 * @brief Constructor.
	 *
	 * You may pass an array of attributes to initialize the axFeedEntry propreties.
	 *
	 * @param array $values @optional @default{array()}
	 */
	public function __construct (array $values = array()) {
	    if (!empty($values)) {
	        foreach ($values as $key => $value) {
                $this->$key = $value;
	        }
	    }
	}
	
	/**
	 * @brief Getter
	 * 
	 * @param string $key
	 * @throws InvalidArgumentException If @c $key doesn't exists
	 * @return mixed
	 */
    public function __get ($key) {
        if (method_exists($this, $method = "get" . ucfirst($key)))
            return $this->$method();
        else
            throw new InvalidArgumentException("$key does not exist", 4001);
    }
    
    /**
     * @brief Setter
     * 
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
     * @brief Get id
     * @return integer
     */
    public function getId () {
        return $this->_id;
    }
    
    /**
     * @brief Set id.
     *
     * First parameter @c $id will be converted to integer
     *
     * @param mixed $id
     * @return axFeedEntry
     */
    public function setId ($id) {
        $this->_id = (int)$id;
        return $this;
    }
    
    /**
     * @brief Get title
     * 
     * @return string
     */
    public function getTitle () {
        return $this->_title;
    }
    
    /**
     * @brief Set title
     * 
     * @param string $title
     * @return void
     */
    public function setTitle ($title) {
        $title = strip_tags($title);
        $this->_title = $title;
        return $this;
    }
    
    /**
     * @brief Get date (RFC 2822)
     * @return string
     */
    public function getDate () {
        return $this->_date;
    }
    
    /**
     * @brief Set date.
     *
     * Date parameter may be integer or string; it will be converted to RFC 2822 date.
     *
     * @param mixed $date
     * @return axFeedEntry
     */
    public function setDate ($date) {
        if ($time = strtotime($date))
            $date = date('r', $time);
        else
            throw new InvalidArgumentException("Invalid date format", 4002);
            
        $this->_date = $date;
        return $this;
    }
    
    /**
     * @brief Get author
     * 
     * @return string
     */
    public function getAuthor () {
        return $this->_author;
    }
    
    /**
     * @brief Set author

     * @throws InvalidArgumentException If author's mail, name or URI is invalid
     * @param array $author
     * @return axFeedEntry
     */
    public function setAuthor (array $author) {
        $author = array_intersect_key($author, array_flip(array('mail', 'name', 'uri')));
        
        if (isset($author['mail']) && !axMail::validateEmail($author['mail']))
            throw new InvalidArgumentException("Invalid author email", 4003);
            
        if (isset($author['name']) && !$author['name'] = filter_var($author['name'], FILTER_SANITIZE_ENCODED))
            throw new InvalidArgumentException("Invalid author name", 4004);
            
        if (isset($author['uri']) && !filter_var($author['uri'], FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URI for author", 4005);
            
        if (empty($author))
            throw new InvalidArgumentException("Author description must contain at least a name or email or URI", 4006);
            
        $this->_author = $author;
        return $this;
    }
    
    /**
     * @brief Get description
     * 
     * @return string
     */
    public function getDescription () {
        return $this->_description;
    }
    
    /**
     * @brief Set description
     * 
     * @param string $description
     * @return axFeedEntry
     */
    public function setDescription ($description) {
        $description = strip_tags($description);
        $this->_description = $description;
        return $this;
    }
    
    /**
     * @brief Get content
     * 
     * @return string
     */
    public function getContent () {
        return $this->content;
    }
    
    /**
     * @brief Set content
     * 
     * @param string $content
     * @return axFeedEntry
     */
    public function setContent ($content) {
        $this->content = $content;
        return $this;
    }
    
    /**
     * @brief Get link
     * 
     * @return string
     */
    public function getLink () {
        return $this->link;
    }
    
    /**
     * @brief Set link
     * 
     * @param string $url
     * @throws InvalidArgumentException If @c $url parameter is not a valid URL
     * @return axFeedEntry
     */
    public function setLink ($url) {
        if (!$url = filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URL", 4007);
        $this->link = $url;
        return $this;
    }
    
    /**
     * @brief Get comments url
     * 
     * @return string
     */
    public function getComments () {
        return $this->comments;
    }
    
    /**
     * @brief Sets comments url
     * 
     * @param string $url
     * @throws InvalidArgumentException If @c $url parameter is not a valid URL 
     * @return axFeedEntry
     */
    public function setComments ($url) {
        if (!$url = filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URL", 4007);
        $this->comments = $url;
        return $this;
    }
}