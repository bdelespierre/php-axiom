<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

/**
 * Mail Class
 *
 * @author Delespierre
 * @version $Rev$
 * @subpackage Mail
 */
class Mail {
    
    /**
     * Constants
     * @var string
     */
    const HEADER_BCC = "Bcc";
    const HEADER_CC = "Cc";
    const HEADER_CONTENT_DESCRIPTION = "Content-Description";
    const HEADER_CONTENT_TYPE = "Content-Type";
    const HEADER_CONTENT_TRANSFERT_ENCODING = "Content-Transfer-Encoding";
    const HEADER_DATE = "Date";
    const HEADER_FROM = "From";
    const HEADER_MIME_VERSION = "MIME-Version";
    const HEADER_PRIORITY = "Priority";
    const HEADER_REPLY_TO = "Reply-To";
    const HEADER_SENDER = "Sender";
    const HEADER_SUBJECT = "Subject";
    const HEADER_TO = "To";
    const HEADER_X_CONFIRM_READING_TO = "X-Confirm-Reading-To";
    const HEADER_X_MAILER = "X-Mailer";
    const HEADER_X_PRIORITY = "X-Priority";
    const HEADER_X_UNSUBSCRIBE_EMAIL = "X-Unsubscribe-Email";
    const HEADER_X_UNSUBSCRIBE_WEB = "X-Unsubscribe-Web";
    
    const HEADER_SEPARATOR_CRLF = "\r\n";
    const HEADER_SEPARATOR_LF = "\n";
    
    /**
     * Allowed headers
     * @var array
     */
    public static $allowed_headers = array(
        self::HEADER_BCC,                        self::HEADER_CC,                   self::HEADER_CONTENT_DESCRIPTION,
        self::HEADER_CONTENT_TRANSFERT_ENCODING, self::HEADER_CONTENT_TYPE,         self::HEADER_DATE,
        self::HEADER_FROM,                       self::HEADER_MIME_VERSION,         self::HEADER_PRIORITY,
        self::HEADER_REPLY_TO,                   self::HEADER_SENDER,               self::HEADER_SUBJECT,
        self::HEADER_TO,                         self::HEADER_X_CONFIRM_READING_TO, self::HEADER_X_MAILER,
        self::HEADER_X_PRIORITY,                 self::HEADER_X_UNSUBSCRIBE_WEB,    self::HEADER_X_UNSUBSCRIBE_EMAIL
    );
    
    /**
     * Header separator
     * @var string
     */
    protected $_header_separator = self::HEADER_SEPARATOR_CRLF;
    
    /**
     * Sender
     * @var string
     */
    protected $_from;
    
    /**
     * Recipients
     * @var array
     */
    protected $_to = array();
    
    /**
     * Subject
     * @var string
     */
    protected $_subject;
    
    /**
     * Body parts
     * @var array
     */
    protected $_message_parts = array();
    
    /**
     * Headers
     * @var array
     */
    protected $_headers = array();
    
    /**
     * Default constructor.
     *
     * The $to parameter can be either a string representing
     * one destination or an array representing multiple
     * destinations.
     *
     * Headers passed to this method follows this format:
     *  $header = array('<header_name>' => '<header_value>' ...)
     * Invalid headers or values will trigger InvalidArgumentException.
     *
     * @param string $from
     * @param array $to
     * @param string $subject
     * @param string $message
     * @param array $headers
     * @param array $options
     * @throws InvalidArgumentException
     */
    public function __construct ($from, $to, $subject = "No Subject", $message = null, array $headers = array()) {
        if (!self::validateEmail($from))
            throw new InvalidArgumentException('First parameter is expected to be a valid email', 2014);
            
        $this->_from = $subject;
            
        $to = (array)$to;
        foreach ($to as $destination)
            $this->addDestination($destination);
        
        if (!empty($subject))
            $this->setSubject($subject);
            
        if (!empty($message))
            $this->addMessagePart($message);
        
        foreach ($headers as $header => $value)
            $this->setHeader($header, $value);
    }
    
    /**
     * Validates email address.
     * @param string $email
     * @return boolean
     */
    public static function validateEmail ($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
     
        if (function_exists('checkdnsrr')) {
            $host = substr($email, strpos($email, '@') + 1);
            return checkdnsrr($host, 'MX');
        }
    	
        return true;
    }
    
    /**
     * Constructor static alias
     * @see Mail::__construct
     * @param string $from
     * @param mixed $to
     * @param string $subject
     * @param array $headers
     * @return Mail
     */
    public static function export ($from, $to, $subject, array $headers = array()) {
        return new self ($from, $to, $subject, $headers);
    }
    
    /**
     * Add a destination to the mail.
     *
     * Will throw an InvalidArgumentException
     * if the destination is invalid.
     *
     * @throws InvalidArgumentException
     * @param unknown_type $to
     * @return void
     */
    public function addDestination ($to) {
        if (!self::validateEmail($to))
            throw new InvalidArgumentException("Invalid destination", 2015);
        
        if (!array_keys($this->_to, $to))
            $this->_to[] = $to;
    }
    
    /**
     * Remove a destination
     * @param string $to
     * @return void
     */
    public function removeDestination ($to) {
        foreach (array_keys($this->_to, $to) as $key)
            unset($this->_to[$key]);
    }
    
    /**
     * Add an header to the headers list.
     *
     * Header must be part of Mail::$valid_headers.
     * Value will be checked accordingly to each header
     * and an InvalidArgumentException will be thrown
     * in cas of invalid value.
     *
     * Note: Date parameter is compatible with
     * strtotime definition.
     *
     * @param string $header
     * @param scalar $value
     * @throws InvalidArgumentException
     * return void
     */
    public function setHeader ($header, $value) {
        if (!in_array($header, self::$allowed_headers))
            throw new InvalidArgumentException("Invalid Header $header", 2016);
            
        switch ($header) {
            case self::HEADER_SENDER:
            case self::HEADER_FROM:
            case self::HEADER_CC:
            case self::HEADER_BCC:
            case self::HEADER_REPLY_TO:
            case self::HEADER_X_CONFIRM_READING_TO:
            case self::HEADER_X_UNSUBSCRIBE_EMAIL:
                if (!self::validateEmail($value))
                    throw new InvalidArgumentException("Invalid email for $header", 2017);
                break;
                
            case self::HEADER_CONTENT_TRANSFERT_ENCODING:
                if (!in_array($value, array('7bits', '8bits', 'binary', 'quoted-printable', 'base64', 'ietf-token', 'x-token')))
                    throw new InvalidArgumentException("Invalid value for $header", 2018);
                break;
                
            case self::HEADER_DATE:
                if ($time = strtotime($value))
                    $value = date('r', $time);
                else
                    throw new InvalidArgumentException("Invalid date format for $header", 2019);
                break;
                
            case self::HEADER_PRIORITY:
                if (!in_array($value, array('normal', 'urgent', 'non-urgent')))
                    throw new InvalidArgumentException("Invalid priority for $header", 2020);
                break;
                
            case self::HEADER_X_UNSUBSCRIBE_WEB:
                if (!$value = filter_var($value, FILTER_VALIDATE_URL))
                    throw new InvalidArgumentException("Invalid url for $header", 2021);
                break;
                
            case self::HEADER_MIME_VERSION:
            case self::HEADER_CONTENT_TYPE:
            case self::HEADER_CONTENT_DESCRIPTION:
            case self::HEADER_REPLY_TO:
            case self::HEADER_SENDER:
            case self::HEADER_SUBJECT:
            case self::HEADER_TO:
            case self::HEADER_X_CONFIRM_READING_TO:
            case self::HEADER_X_MAILER:
            case self::HEADER_X_PRIORITY:
                $value = trim($value);
                if (empty($value))
                    throw new InvalidArgumentException("A non empty value is mandatory for $header", 2022);
                break;
        }
        
        $this->_headers[$header] = $value;
    }
    
    /**
     * Set the header separator.
     *
     * According to the <missing RFC>,
     * header has to be separated by a CRLF (\r\n)
     * but some mailboxes like Gmail doesn't
     * recognized it and expects a LF (\n).
     *
     * @param string $glue
     * @throws InvalidArgumentException
     * @return void
     */
    public function setHeaderSeparator ($glue) {
        if (!in_array($glue, array(self::HEADER_SEPARATOR_CRLF, self::HEADER_SEPARATOR_LF)))
            throw new InvalidArgumentException("Unrecognized separator", 2023);
        $this->_header_separator = $glue;
    }
    
    /**
     * Subject getter
     * @return string
     */
    public function getSubject () {
        return $this->_subject;
    }
    
    /**
     * Subject setter
     * @param string $subject
     * @return void
     */
    public function setSubject ($subject) {
        $this->_subject = filter_var(trim($subject), FILTER_SANITIZE_ENCODED);
    }
    
    /**
     * Add a new part to the message.
     *
     * If the content type parameter is left
     * to null, no header will be apended to
     * the message part.
     *
     * This method will add a message part to
     * the list and return the key (useful
     * for part removal with Mail::removeMessagePart).
     *
     * @param string $message
     * @param string $content_type = null
     * @param string $charset = "utf-8"
     * @return string
     */
    public function addMessagePart ($message, $content_type = null, $charset = "utf-8") {
        $part  = $content_type ? "Content-Type: $content_type; charset=$charset" . $this->_header_separator: "";
        $part .= $message . $this->_header_separator;
        $this->_message_parts[$key = uniqid("part-")] = $part;
        
        return $key;
    }
    
    /**
     * Attach a file to the mail.
     *
     * If the filename parameter is left
     * empty, the file's name will be
     * determined automaticaly.
     *
     * Returns the message part key.
     *
     * @param string $path
     * @param string $content_type = null
     * @param string $filename = null
     * @throws InvalidArgumentException
     * @return string
     */
    public function addAttachment ($path, $content_type, $filename = null) {
        if (!file_exists($path))
            throw new MissingFileException($path, 2024);
            
        if (is_dir($path))
            throw new InvalidArgumentException("First parameter is expected to be regular file, directory given", 2025);
            
        if (!$filename)
            $filename = basename($path);
            
        if (function_exists('finfo_open') && $finfo = finfo_open(FILEINFO_MIME_TYPE)) {
            $content_type = finfo_file($finfo, $path);
            finfo_close($finfo);
        }
        else if (function_exists('mime_content_type'))
            $content_type = mime_content_type($path);
        else if (empty($content_type))
            throw new LogicException("Could not determine content-type for $path", 2026);
            
        if (!$content = file_get_contents($path, false))
            throw new RuntimeException("Cannot read $path", 2027);
            
        $part  = "Content-Type: $content_type; name=$filename" . $this->_header_separator;
        $part .= "Content-transfer-encoding: base64" . $this->_header_separator;
        $part .= chunk_split(base64_encode($content));
        $part .= $this->_header_separator;
        $this->_message_parts[$key = uniqid("part-")] = $part;
        
        return $key;
    }
    
    /**
     * Remove a message part
     * @param string $key
     * @return void
     */
    public function removeMessagePart ($key) {
        unset($this->_message_parts[$key]);
    }
    
    /**
     * Alias of Mail::removeMessagePart
     * @see Mail::removeMessagePart
     * @param string $key
     * @return voic
     */
    public function removeAttachement ($key) {
        return $this->removeMessagePart($key);
    }
    
    /**
     * Send the mail.
     *
     * Returns an array where keys are destinations
     * and values are booleans representing the send
     * status.
     *
     * If called withou a mail body, will throw a
     * RuntimeException.
     *
     * @throws RuntimeException
     * @return array
     */
    public function send () {
        if (empty($this->_message_parts))
            throw new RuntimeException("Cannot send mail with empty body");
        
        $message = (string)$this;
        $headers = "";
        $results = array();

        foreach ($this->_headers as $name => $value)
            $headers .= "{$name}: {$value}{$this->_header_separator}";
        
        foreach ($this->_to as $destination)
            $results[$destination] = mail($destination, $this->_subject, $message, $headers);
        
        return $results;
    }
    
    /**
     * Get the message body as string.
     *
     * Note: this method will determine the type
     * of mail and eventualy set its header
     * to multipart/mixed if more than one
     * part is found.
     *
     * Note: this method will set the
     * from header for ease purpose.
     *
     * @return string
     */
    public function __toString () {
        $c = count($this->_message_parts);
        $this->setHeader(self::HEADER_FROM, $this->_from);
        
        if ($c > 1) {
            $boundary = md5(uniqid(microtime(), true));
            $this->setHeader(self::HEADER_CONTENT_TYPE, "multipart/mixed;boundary=$boundary");
            return implode($s = "--{$boundary}{$this->_header_separator}", $this->_message_parts) . $s;
        }
        
        return $c == 1 ? array_shift($this->_message_parts) : "";
    }
}