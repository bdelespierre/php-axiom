<?php
/**
 * @brief Feed class file
 * @file axFeed.class.php
 */

/**
 * @brief Generic Feed Class
 *
 * @todo axFeed long description
 * @todo This class needs to be tested
 * @class axFeed
 * @author Delespierre
 * @ingroup Feed
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axFeed extends ArrayIterator {
    
    /**
     * @brief Feed meta informations
     * @property array $_meta_inf
     */
    protected $_meta_inf = array();
    
    /**
     * @brief Feed configuration
     * @property array $_type
     */
    protected $_type;
        
    /**
     * @brief Set all feeds meta informations
     * 
     * @c $meta_inf parmaeter is structured as follow
     * @code
     * array(
     *       'title' => 'Axiom Generic Feed',
     *       'date' => date('r'),
     *       'author' => array(
     *       	'name' => 'Benjamin DELESPIERRE',
     *       	'mail' => 'benjamin.delespierre@gmail.com'),
     *       'lang' => axLang::getLocale(),
     *       'description' => 'Axiom Generic Feed',
     *       'copyright' => null,
     *       'link' => url('feed'),
     *       'id' => uniqid('ax'),
     * );
     * @endcode
     * 
     * @param array $meta_inf @optional @default{array()}
     * @return axFeed
     */
    public function setMetaInf (array $meta_inf = array()) {
        $this['meta'] = $meta_inf;
        return $this;
    }
    
    /**
     * @brief Constructor
     * 
     * @param string $type The feed type, possible values are @c 'Atom' or @c 'Rss'
     * @param array $items @optional @default{array()} An array of axFeedItem
     * @param array $meta_infs @optional @default{array()} See axFeed::setMetaInf
     */
    public function __construct ($type, array $items = array(), array $meta_inf = array()) {
        parent::__construct(array(
            'meta'  => $meta_inf,
            'items' => $items,
        ));
        
        $this->_type = $type;
    }
    
    /**
     * @brief Getter
     *
     * Allow the use of every $feed->getXXX() as $feed->XXX
     *
     * @param string $key
     * @throws InvalidArgumentException If the @c $key doesn't exists
     * @return mixed
     */
    public function __get ($key) {
        if (method_exists($this, $method = "get" . ucfirst($key)))
            return $this->$method();
        else
            throw new InvalidArgumentException("$key does not exist", 4008);
    }
    
    /**
     * @brief Setter helper
     *
     * Allow the use of @c $feed->setXXX(YYY) as @c $feed->XXX = YYY
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set ($key, $value) {
        if (method_exists($this, $method = "get" . ucfirst($key)))
            return $this->$method($value);
        else
            throw new InvalidArgumentException("$key does not exist", 4008);
    }
    
    /**
     * @brief Add a feed entry and return it to allow chaining calls.
     *
     * The first parameter is optionnal, if you don't set it manually, an empty axFeedEntry will be created and 
     * returned so you can manipulate it.
     *
     * @param axFeedEntry $entry @optional @default{null}
     * @return axFeedEntry
     */
    public function add (axFeedEntry $entry = null) {
        if (!$entry)
            $entry = new axFeedEntry;
            
        return $this['items'][] = $entry;
    }
    
    /**
     * @brief Get all feed entries attached to the feed
     * 
     * @return array
     */
    public function getEntries () {
        return $this['items'];
    }
    
    /**
     * @brief Get meta-inf id parameter
     * 
     * @return string
     */
    public function getId () {
        return $this['meta']['id'];
    }
    
    /**
     * @brief Set meta-inf id parameter
     * 
     * @throws InvalidArgumentException If @c $id is invalid
     * @param string $id
     * @return axFeed
     */
    public function setId ($id) {
        if (!$id = filter_var($id, FILTER_SANITIZE_ENCODED))
            throw new InvalidArgumentException("Invalid ID", 4009);
            
        $this['meta']['id'] = $id;
        return $this;
    }
    
    /**
     * @brief Get meta-inf title
     * 
     * @return string
     */
    public function getTitle () {
        return $this['meta']['title'];
    }
    
    /**
     * @brief Set meta-inf title
     * 
     * @param string $title
     * @return axFeed
     */
    public function setTitle ($title) {
        $title = strip_tags($title);
        $this['meta']['title'] = $title;
        return $this;
    }
    
    /**
     * @brief Get meta-inf date
     * 
     * @return string
     */
    public function getDate () {
        return $this['meta']['date'];
    }
    
    /**
     * @brief Set meta-inf date.
     *
     * This method accepts both strings and integers (timestamp).
     *
     * @throws InvalidArgumentException If @c $date parameter is invalid
     * @param mixed $date
     * @return axFeed
     */
    public function setDate ($date) {
        if ($time = strtotime($date))
            $date = date('r', $time);
        else
            throw new InvalidArgumentException("Invalid date format", 4010);
            
        $this['meta']['date'] = $date;
        return $this;
    }
    
    /**
     * @brief Get meta-inf author
     * 
     * @return array
     */
    public function getAuthor () {
        return $this['meta']['author'];
    }
    
    /**
     * @brief Set meta-inf author
     *
     * @todo Refactor this method to use the filter_var_array function
     * @throws InvalidArgumentException If @c $author mail, name or URI is invalid
     * @param array $author
     * @return axFeed
     */
    public function setAuthor (array $author) {
        $author = array_intersect_key($author, array_flip(array('mail', 'name', 'uri')));
        
        if (isset($author['mail']) && !axMail::validateEmail($author['mail']))
            throw new InvalidArgumentException("Invalid author email", 4011);
            
        if (isset($author['name']) && !$author['name'] = filter_var($author['name'], FILTER_SANITIZE_ENCODED))
            throw new InvalidArgumentException("Invalid author name", 4012);
            
        if (isset($author['uri']) && !filter_var($author['uri'], FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URI for author", 4013);
            
        if (empty($author))
            throw new InvalidArgumentException("Author description must contain at least a name or email or URI", 4014);
            
        $this['meta']['author'] = $author;
        return $this;
    }
    
    /**
     * @brief Get meta-inf lang
     * 
     * @return string
     */
    public function getLang () {
        return $this['meta']['lang'];
    }
    
    /**
     * @brief Set meta-inf lang
     * 
     * @param string $lang
     * @return axFeed
     */
    public function setLang ($lang) {
        $this['meta']['lang'] = $lang;
        return $this;
    }
    
    /**
     * @brief Get meta-inf description
     * 
     * @return string
     */
    public function getDescription () {
        return $this['meta']['description'];
    }
    
    /**
     * @brief Set meta-inf description
     * 
     * @param string $description
     * @return axFeed
     */
    public function setDescription ($description) {
        $description = strip_tags($description);
        $this['meta']['description'] = $description;
        return $this;
    }
    
    /**
     * @brief Get meta-inf copyright
     * 
     * @return string
     */
    public function getCopyright () {
        return $this['meta']['copyright'];
    }
    
    /**
     * @brief Set meta-inf copyright
     * 
     * @param string $copyright
     * @return axFeed
     */
    public function setCopyright ($copyright) {
        $this['meta']['copyright'] = $copyright;
        return $this;
    }
    
    /**
     * @brief Get meta-inf link
     * 
     * @return string
     */
    public function getLink () {
        return $this['meta']['link'];
    }
    
    /**
     * @brief Set meta-inf link
     * 
     * @throws InvalidArgumentException If @c $url is not a valid URL
     * @param string $url
     * @return axFeed
     */
    public function setLink ($url) {
        if (!$url = filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URL", 4015);
            
        $this['meta']['link'];
        return $this;
    }
    
    /**
     * @brief Build the feed using a feed writer conector
     * 
     * If the @c $type parameter is not present, will use the default type (passed to the constructor)
     * 
     * @param string $type @optional @default{null}
     * @throws RuntimeException If the feed writer for this type was not found
     * @return axFeedWriter
     */
    public function build ($type = null) {
        if (!$type)
            $type = ucfirst(strtolower($this->_type));
        
        if (!axAutoloader::load($class = "ax{$type}FeedWriter"))
            throw new RuntimeException("$type feed writer not found", 4016);
            
        $reflection = new ReflectionClass($class);
        if (!$reflection->isInstanciable())
            throw new RuntimeException("$class cannot be instanciated", 4017);
            
        if (!$reflextion->isSubclassOf("axFeedWriter"))
            throw new RuntimeException("$class must extends axFeedWriter", 4018);
        
        return new $class($this);
    }
}

/**
 * @brief Feed Module
 * 
 * The Feed module provides class for Atom and RSS feed manipulations.
 * 
 * @defgroup Feed
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @copyright http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */