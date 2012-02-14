<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Generic Feed Class
 *
 * @author Delespierre
 * @package libaxiom
 * @subpackage feed
 */
class axFeed extends ArrayIterator {
    
    /**
     * Feed meta informations
     * @var array
     */
    protected $_meta_inf = array();
    
    /**
     * Feed configuration
     * @var array
     */
    protected $_type;
    

    
    /**
     * Set all feeds meta informations
     * 
     * E.G.
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
     * 
     * @param array $meta_inf
     * @return void
     */
    public static function setMetaInf (array $meta_inf = array()) {
        $this->_meta_inf = $meta_inf;
    }
    
    /**
     * Default constructor
     * @param array $items
     */
    public function __construct (array $items = array(), $type) {
        parent::__construct(array(
            'meta' => $this->_meta_inf,
            'items' => $items,
        ));
        
        $this->_type = $type;
    }
    
    /**
     * Getter helper
     *
     * Allow the use of every $feed->getXXX() as $feed->XXX
     *
     * @param string $key
     * @throws InvalidArgumentException
     * @return mixed
     */
    public function __get ($key) {
        if (method_exists($this, $method = "get" . ucfirst($key)))
            return $this->$method();
        else
            throw new InvalidArgumentException("$key does not exist", 4008);
    }
    
    /**
     * Setter helper
     *
     * Allow the use of $feed->setXXX(YYY) as $feed->XXX = YYY
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
     * Constructor static helper
     * @param array $items
     * @return Feed
     */
    public static function export (array $items = array()) {
        return new self($items);
    }
    
    /**
     * Add a feed entry and return it to allow chaining calls.
     *
     * The first parameter is optionnal, if you don't set
     * it manually, an empty axFeedEntry will be created
     * and returned so you can manipulate it.
     *
     * @param axFeedEntry $entry
     * @return axFeedEntry
     */
    public function add (axFeedEntry $entry = null) {
        if (!$entry)
            $entry = new axFeedEntry;
            
        return $this['items'][] = $entry;
    }
    
    /**
     * Get all feed entries attached to the feed
     * @return array
     */
    public function getEntries () {
        return $this['items'];
    }
    
    /**
     * Get meta-inf id parameter
     * @return string
     */
    public function getId () {
        return $this['meta']['id'];
    }
    
    /**
     * Set meta-inf id parameter
     * @throws InvalidArgumentException
     * @param string $id
     * @return void
     */
    public function setId ($id) {
        if (!$id = filter_var($id, FILTER_SANITIZE_ENCODED))
            throw new InvalidArgumentException("Invalid ID", 4009);
            
        $this['meta']['id'] = $id;
    }
    
    /**
     * Get meta-inf title
     * @return string
     */
    public function getTitle () {
        return $this['meta']['title'];
    }
    
    /**
     * Set meta-inf title
     * @param string $title
     * @return void
     */
    public function setTitle ($title) {
        $title = strip_tags($title);
        $this['meta']['title'] = $title;
    }
    
    /**
     * Get meta-inf date
     * @return string
     */
    public function getDate () {
        return $this['meta']['date'];
    }
    
    /**
     * Set meta-inf date.
     *
     * This method accepts both strings and integers.
     *
     * @throws InvalidArgumentException
     * @param mixed $date
     * @return void
     */
    public function setDate ($date) {
        if ($time = strtotime($date))
            $date = date('r', $time);
        else
            throw new InvalidArgumentException("Invalid date format", 4010);
            
        $this['meta']['date'] = $date;
    }
    
    /**
     * Get meta-inf author
     * @return array
     */
    public function getAuthor () {
        return $this['meta']['author'];
    }
    
    /**
     * Set meta-inf author
     *
     * @throws InvalidArgumentException
     * @param array $author
     * @return void
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
    }
    
    /**
     * Get meta-inf lang
     * @return string
     */
    public function getLang () {
        return $this['meta']['lang'];
    }
    
    /**
     * Set meta-inf lang
     * @param string $lang
     * @return void
     */
    public function setLang ($lang) {
        $this['meta']['lang'] = $lang;
    }
    
    /**
     * Get meta-inf description
     * @return string
     */
    public function getDescription () {
        return $this['meta']['description'];
    }
    
    /**
     * Set meta-inf description
     * @param string $description
     * @return void
     */
    public function setDescription ($description) {
        $description = strip_tags($description);
        $this['meta']['description'] = $description;
    }
    
    /**
     * Get meta-inf copyright
     * @return string
     */
    public function getCopyright () {
        return $this['meta']['copyright'];
    }
    
    /**
     * Set meta-inf copyright
     * @param string $copyright
     * @return void
     */
    public function setCopyright ($copyright) {
        $this['meta']['copyright'] = $copyright;
    }
    
    /**
     * Get meta-inf link
     * @return string
     */
    public function getLink () {
        return $this['meta']['link'];
    }
    
    /**
     * Set meta-inf link
     * @throws InvalidArgumentException
     * @param string $url
     * @return void
     */
    public function setLink ($url) {
        if (!$url = filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException("Invalid URL", 4015);
            
        $this['meta']['link'];
    }
    
    /**
     * Build the feed using a feed writer conector
     * @param string $type
     * @throws RuntimeException
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