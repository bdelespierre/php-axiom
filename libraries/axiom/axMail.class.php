<?php
/**
 */

/**
 * @brief Mail Class
 *
 * @todo Mail class long description 
 * @warning You may add as many parts as you want into the mail using axMail::addPart but keep the text part as first 
 * part.
 * @warning This class has several bugs that needs to be fixed !
 * @class axMail
 * @author Delespierre
 * @ingroup Mail
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axMail {
    
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
     * @brief Allowed headers
     * @property array $allowed_headers
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
     * @brief Header separator
     * @property string $_header_separator
     */
    protected $_header_separator = self::HEADER_SEPARATOR_CRLF;
    
    /**
     * @brief Sender
     * @property string $_from
     */
    protected $_from;
    
    /**
     * @brief Recipients
     * @property array $_to
     */
    protected $_to = array();
    
    /**
     * @brief Subject
     * @property string $_subject
     */
    protected $_subject;
    
    /**
     * @brief Body parts
     * @property array $_message_parts
     */
    protected $_message_parts = array();
    
    /**
     * @brief Headers
     * @property array $_headers
     */
    protected $_headers = array();
    
    /**
     * @brief Constructor.
     *
     * The @ $to parameter can be either a string representing one destination or an array representing multiple 
     * destinations.
     * Headers passed to this method follows this format:
     * @code
     * $header = array('<header_name>' => '<header_value>' ...)
     * @endcode
     * Invalid headers names or values will trigger InvalidArgumentException.
     *
     * @param string $from The sender
     * @param array $to The recipient(s)
     * @param string $subject @optional @default{"NO Subject"} The subject
     * @param string $message @optional @default{null} The body (can be defined later)
     * @param array $headers @optional @default{array()} The headers (can be defined later)
     * @param array $options @optional @default{array()} TO BE IMPLEMENTED 
     * @throws InvalidArgumentException If the @c $from parameter is not a valid email
     */
    public function __construct ($from, $to, $subject = "No Subject", $message = null, array $headers = array()) {
        if (!self::validateEmail($from))
            throw new InvalidArgumentException('First parameter is expected to be a valid email', 2014);
            
        $this->_from = $from;
            
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
     * @brief Validates email address.
     * 
     * If possible, will check the dnsrr for the mail's host.
     * 
     * @static
     * @param string $email The mail address to validate
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
     * @brief Add a destination to the mail.
     *
     * @throws InvalidArgumentException If the destination is invalid.
     * @param string $to The destination
     * @return void
     */
    public function addDestination ($to) {
        if (!self::validateEmail($to))
            throw new InvalidArgumentException("Invalid destination", 2015);
        
        if (!array_keys($this->_to, $to))
            $this->_to[] = $to;
    }
    
    /**
     * @brief Remove a destination
     * @param string $to
     * @return void
     */
    public function removeDestination ($to) {
        foreach (array_keys($this->_to, $to) as $key)
            unset($this->_to[$key]);
    }
    
    /**
     * @brief Add an header 
     * 
     * Header must be part of axMail::$valid_headers. Value will be checked accordingly to each header and an 
     * InvalidArgumentException will be thrown in case of invalid value.
     *
     * @note Any date value will be parsed using strtotime so make sure your dates apply to a well recognized format
     * or simply set a timestamp.
     * @param string $header The header
     * @param scalar $value The header's value
     * @throws InvalidArgumentException If the header or its value is invalid
     * return axMail
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
                if (!in_array($value, array('7bits', '8bits', 'binary', 'quoted-printable', 'base64', 'ietf-token', 
                	'x-token')))
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
        return $this;
    }
    
    /**
     * @brief Set the header separator.
     *
     * According to rfc1341, header has to be separated by a CRLF (\r\n)  but some mailboxes like Hotmail doesn't
     * recognize it and expects a LF (\n).
     *
     * @link http://www.w3.org/Protocols/rfc1341/7_2_Multipart.html
     * @param string $glue The separator
     * @throws InvalidArgumentException If the separator is not CRLF nor LF
     * @return axMail
     */
    public function setHeaderSeparator ($glue) {
        if (!in_array($glue, array(self::HEADER_SEPARATOR_CRLF, self::HEADER_SEPARATOR_LF)))
            throw new InvalidArgumentException("Unrecognized separator", 2023);
        $this->_header_separator = $glue;
        return $this;
    }
    
    /**
     * @brief Subject getter
     * @return string
     */
    public function getSubject () {
        return $this->_subject;
    }
    
    /**
     * @brief Subject setter
     * @param string $subject
     * @return void
     */
    public function setSubject ($subject) {
        $this->_subject = strip_tags(trim($subject));
    }
    
    /**
     * @brief Add a new part to the message.
     *
     * If the content type parameter is left to null, no header will be apended to the message part.
     * This method will add a message part to the list and return the key (useful  for part removal with 
     * axMail::removeMessagePart()).
     *
     * @param string $message The part's body
     * @param string $content_type @optional @default{null} The part content type
     * @param string $charset @optional @default{"utf-8"} The part charset
     * @return string
     */
    public function addMessagePart ($message, $content_type = null, $charset = "utf-8") {
        $part  = $content_type ? "Content-Type: {$content_type}; charset={$charset}": "";
        $part .= $this->_header_separator;
        $part .= $this->_header_separator;
        $part .= $message;
        $part .= $this->_header_separator;
        $this->_message_parts[$key = uniqid("part-")] = $part;
        
        return $key;
    }
    
    /**
     * @brief Attach a file to the mail.
     *
     * If the filename parameter is left empty, the file's name will be determined automaticaly.
     * Returns the message part key (just like axMail::addMessagePart does)
     *
     * @param string $path The file path
     * @param string $content_type @optional @default{null} The file's content type
     * @param string $filename @optional @default{null} Will be calculated from @c $path if null
     * @throws axMissingFileException If the file cannot be found
     * @throws InvalidArgumentException If the file is not a regular file (directory or link for instance)
     * @return string
     */
    public function addAttachment ($path, $content_type = null, $filename = null) {
        if (!is_file($path))
            throw new axMissingFileException($path, 2024);
            
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
            $content_type = "application/octet-stream";
            
        if (!$content = file_get_contents($path, false))
            throw new RuntimeException("Cannot read $path", 2027);
            
        $part  = "Content-Type: $content_type; name=$filename";

		$part .= $this->_header_separator;
        $part .= "Content-transfer-encoding: base64";
		$part .= $this->_header_separator;
        $part .= $this->_header_separator;
        $part .= chunk_split(base64_encode($content));
        $part .= $this->_header_separator;
        $this->_message_parts[$key = uniqid("part-")] = $part;
        
        return $key;
    }
    
    /**
     * @brief Remove a message part
     * @param string $key
     * @return void
     */
    public function removeMessagePart ($key) {
        unset($this->_message_parts[$key]);
    }
    
    /**
     * @brief Alias of axMail::removeMessagePart()
     * @see axMail::removeMessagePart
     * @param string $key
     * @return voic
     */
    public function removeAttachement ($key) {
        return $this->removeMessagePart($key);
    }
    
    /**
     * @brief Send the mail.
     *
     * Returns an array where keys are destinations and values are booleans representing the send status.
     *
     * @throws RuntimeException If called whith no mail body defined
     * @return array
     */
    public function send () {
        if (empty($this->_message_parts))
            throw new RuntimeException("Cannot send mail with empty body");
        
        $message = (string)$this;
        $headers = "";
        $results = array();
        
        if (empty($message))
            throw new RuntimeException("Cannot send empty mail");

        foreach ($this->_headers as $name => $value)
            $headers .= "{$name}: {$value}{$this->_header_separator}";
        $headers .= $this->_header_separator;
        
        foreach ($this->_to as $destination)
            $results[$destination] = mail($destination, $this->_subject, $message, $headers);
        
        return $results;
    }
    
    /**
     * @brief Get the message body as string.
     *
     * @note This method will determine the type of mail and eventualy set its header  to multipart/mixed if more 
     * than one part is found.
     * @note this method will set the from header for ease purpose.
     *
     * @return string
     */
    public function __toString () {
        try {
            $c = count($this->_message_parts);
            $this->setHeader(self::HEADER_FROM, $this->_from);
            
            if ($c > 1) {
                $boundary = md5(uniqid(microtime(), true));
                $this->setHeader(self::HEADER_CONTENT_TYPE, "multipart/mixed;boundary=$boundary");
				$str  = "--" . $boundary;
				$str .= $this->_header_separator;
				$str .= implode("--" . $boundary . $this->_header_separator, $this->_message_parts);
				$str .= "--" . $boundary . "--";
                return $str;
            }
            
            return $c == 1 ? array_shift($this->_message_parts) : "";
        }
        catch (Exception $e) {
            return "";
        }
    }
}

/**
 * @brief Mail Module
 * 
 * This module contains classes for mail manipulation.
 * 
 * @defgroup Mail
 * @author Delespierre
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @copyright http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

