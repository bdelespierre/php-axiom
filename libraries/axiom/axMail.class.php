<?php
/**
 * @brief Mail class  file
 * @file axMail.class.php
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
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */
class axMail {
    
    protected $_to;
    protected $_subject;
    protected $_parts = array();
    protected $_headers = array();
    
    public $headerSeparator = CRLF;
    
    /**
     * @brief Allowed headers
     * @property array $allowedHeaders
     */
    public static $allowedHeaders = array(
        self::HEADER_BCC,                        self::HEADER_CC,                   self::HEADER_CONTENT_DESCRIPTION,
        self::HEADER_CONTENT_TRANSFERT_ENCODING, self::HEADER_CONTENT_TYPE,         self::HEADER_DATE,
        self::HEADER_FROM,                       self::HEADER_MIME_VERSION,         self::HEADER_PRIORITY,
        self::HEADER_REPLY_TO,                   self::HEADER_SENDER,               self::HEADER_SUBJECT,
        self::HEADER_TO,                         self::HEADER_X_CONFIRM_READING_TO, self::HEADER_X_MAILER,
        self::HEADER_X_PRIORITY,                 self::HEADER_X_UNSUBSCRIBE_WEB,    self::HEADER_X_UNSUBSCRIBE_EMAIL
    );
    
    /**
     * @brief Constructor
     */
    public function __construct ($from = null, $to = null, $subject = null, $message = null, array $headers = array()) {
        if ($from && !$this->setFrom($from))
            throw new InvalidArgumentException("Invalid from: {$from}");
            
        if ($to && !$this->setTo($to))
            throw new InvalidArgumentException("Invalid to: {$to}");
            
        if ($subject)
            $this->setSubject($subject);
            
        if ($headers) {
            foreach ($headers as $header => $value) {
                if (!$this->setHeader($header, $value))
                    throw new InvalidArgumentException("Invalid header {$header} with value {$value}");
            }
        }
            
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
        $email = str_replace(array('<', '>'), '', $email);
        
        if ($offset = strrpos($email, ' ') !== false)
            return self::validateEmail(substr($email, $offset));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return false;
        
        if (function_exists('checkdnsrr')) {
            $host = substr($email, strpos($email, '@') + 1);
            return checkdnsrr($host, 'MX');
        }
    	
        return true;
    }
    
    public function setFrom ($from) {
        if (!self::validateEmail($from))
            return false;
        $this->_headers[self::HEADER_FROM] = $from;
        return $this;
    }
    
    public function setTo ($to) {
        if (is_string($to) && strpos($to, ','))
            $to = explode(',',$to);
            
        $this->_to = array();
        foreach((array)$to as $destination) {
            if (!$this->addDestination($destination))
                return false;
        }
        return $this;
    }
    
    public function addDestination ($destination) {
        if (!self::validateEmail($destination))
            return false;
        $this->_to[$destination] = $destination;
        return $this;
    }
    
    public function removeDestination ($destination) {
        unset($this->_to[$destination]);
        return $this;
    }
    
    public function setSubject ($subject) {
        $this->_subject = trim(strip_tags($subject));
        return $this;
    }
    
    public function setHeader ($header, $value) {
        if (!in_array($header, self::$allowedHeaders))
            return false;
            
        switch ($header) {
            case self::HEADER_SENDER:
            case self::HEADER_FROM:
            case self::HEADER_CC:
            case self::HEADER_BCC:
            case self::HEADER_REPLY_TO:
            case self::HEADER_X_CONFIRM_READING_TO:
            case self::HEADER_X_UNSUBSCRIBE_EMAIL:
                $clean_value = self::validateEmail($value) ? $value : false;
                break;
                
            case self::HEADER_CONTENT_TRANSFERT_ENCODING:
                $values = array('7bits','8bits','binary','quoted-printable','base64','ietf-token','x-token');
                $clean_value = in_array($value, $values) ? $value : false;
                break;
                
            case self::HEADER_DATE:
                $clean_value = ($time = strtotime($value)) ? date('r', $time) : false;
                break;
                
            case self::HEADER_PRIORITY:
                $values = array('normal', 'urgent', 'non-urgent');
                $clean_value = in_array($value, $values) ? $value : false;
                break;
                
            case self::HEADER_X_UNSUBSCRIBE_WEB:
                $clean_value = ($url = filter_var($value, FILTER_VALIDATE_URL)) ? $url : false;
                break;
            
            //! @todo These heades shouldn't be set manually
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
                $clean_value = !empty($value) ? trim($value) : false;
                break;
        }
        
        if (!isset($clean_value) || !$clean_value) {
            trigger_error("Invalid email value for header {$header} ({$value}), header will be ignored");
            unset($this->_headers[$header]);
            return false;
        }
        
        $this->_headers[$header] = $clean_value;
        return $this;
    }
    
    public function addPart ($content, array $headers = array(), & $id = null) {
        $id = uniqid('part_');
        $this->_parts[$id] = array(
            'content' => $content,
            'headers' => $headers
        );
        return $this;
    }
    
    public function removePart ($id) {
        unset($this->_parts[$id]);
        return $this;
    }
    
    public function setBody ($message, $content_type = null, $encoding = null) {
        $this->_parts = array();
        
        $headers = array();
        if ($content_type) {
            $headers[self::HEADER_CONTENT_TYPE] = $content_type;
            if ($encoding)
                $headers[self::HEADER_CONTENT_TYPE] .= "; charset={$encoding}"; 
        }
        
        return $this->addPart($message, $headers);
    }
    
    public function addAttachment ($file, $content_type = null, $filename = null, & $id = null) {
        if (!is_file($file) || !is_readable($file))
            return false;
        
        if (!$filename)
            $filename = basename($file);
            
        if (!$content_type) {
            if (function_exists('finfo_open') && $finfo = finfo_open(FILEINFO_MIME_TYPE)) {
                $content_type = finfo_file($finfo, $path);
                finfo_close($finfo);
            }
            else if (function_exists('mime_content_type'))
                $content_type = mime_content_type($path);
            else if (empty($content_type))
                $content_type = "application/octet-stream";
        }
            
        if (!$content = file_get_contents($file))
            return false;
            
        $headers = array(
            self::HEADER_INTERNAL_TYPE => 'attachment',
            self::HEADER_CONTENT_TYPE => "{$content_type}; name={$filename}",
            self::HEADER_CONTENT_TRANSFERT_ENCODING => 'base64',
        );
        
        $content = chunk_split(base64_encode($content));
        return $this->addPart($content, $headers, $id);
    }
    
    public function hasAttachments () {
        return in_array('attachment', $this->_getInternalTypes());
    }
    
    public function addHTMLPart ($content, $encoding = null, & $id = null) {
        $headers = array(
            self::HEADER_INTERNAL_TYPE => 'html',
            self::HEADER_CONTENT_TYPE => "text/html"
        );
        if ($encoding)
            $headers[self::HEADER_CONTENT_TYPE] .= "; charset={$encoding}";
            
        return $this->addPart($content, $headers, $id);
    }
    
    public function addTextPart ($content, $encoding = null, & $id = null) {
        $headers = array(
            self::HEADER_INTERNAL_TYPE => 'text',
            self::HEADER_CONTENT_TYPE => "text/plain"
        );
        if ($encoding)
            $headers[self::HEADER_CONTENT_TYPE] .= "; charset={$encoding}";
            
        return $this->addPart($content, $headers, $id);
    }
    
    public function addRichTextPart ($content) {
        $headers = array(
            self::HEADER_INTERNAL_TYPE => 'richtext',
            self::HEADER_CONTENT_TYPE => "text/richtext"
        );
        if ($encoding)
            $headers[self::HEADER_CONTENT_TYPE] .= "; charset={$encoding}";
            
        return $this->addPart($content, $headers, $id);
    }
    
    public function send ($send_mode = self::SEND_ALL, $multipart_mode = self::MULTIPART_AUTO) {
        if (!$mail = $this->_build($multipart_mode))
            throw new RuntimeException("Unable to build the mail, you may not have any part defined");
        
        list($to,$subject,$message,$headers) = array_values($mail);
            
        if ($send_mode == self::SEND_ALL) {
            return mail($to,$subject,$message,$headers);
        }
        elseif ($send_mode == self::SEND_BULK) {
            foreach (explode(',', $to) as $dest) {
                $res[$dest] = mail($dest,$subject,$message,$headers);
            }
            return $res;
        }
        else {
            throw new InvalidArgumentException("Invalid send mode: {$send_mode}");
        }
    }
    
    public function __toString () {
        if (!$mail = $this->_build(self::MULTIPART_AUTO)) {
            trigger_error("Unable to build the mail, you may not have any part defined", E_USER_WARNING);
            return "";
        }
        
        list($to,$subject,$message,$headers) = array_values($mail);
        $hs = $this->headerSeparator;
        
        return "Subject: {$subject}{$hs}" .
               "To: {$to}{$hs}" .
               $headers . $hs . $hs .
               $message;
    }
    
    public function _build ($multipart_mode) {
        if (!$internal_types = $this->_getInternalTypes())
            return false;
        
        $to      = implode(',', $this->_to);
        $subject = $this->_subject;
        $headers = $this->_headers;
        $message = "";
        $hs      = $this->headerSeparator;
        
        if (count($this->_parts) === 1) {
            
            // Single part emails
            
            $part = array_shift($this->_parts);
            $headers = $part['headers'] + $headers;
            $message = $part['content'];
        }
        elseif (in_array('attachment', $internal_types)) {
            
            // Multiplart email with attachments
            
            $sub_parts = array(
                'content'     => array(),
                'attachments' => array(),
            );
            foreach ($internal_types as $id => $type)
                $sub_parts[($type == 'attachment' ? 'attachments' : 'content')][] = $this->_parts[$id];
                
            // build sub content (as alternative subtype)
            $sub_boundary = uniqid('alt_');
            $sub_buffer   = "Content-Type: " . self::MULTIPART_ALTERNATIVE . 
            		        "; boundary={$sub_boundary}{$hs}{$hs}";
            
            $sub_pieces = array();
            foreach ($sub_parts['content'] as $part)
                $sub_pieces[] = $this->_buildPart($part);
            
            $sub_buffer .= "--{$sub_boundary}{$hs}" . implode("--{$sub_boundary}{$hs}", $sub_pieces) .
				    	   "{$hs}--{$sub_boundary}--{$hs}{$hs}";

            // build attachments
            $pieces = array($sub_buffer);
            foreach ($sub_parts['attachments'] as $part)
                $pieces[] = $this->_buildPart($part);
                
            $boundary = uniqid('mixed_');
            $headers[self::HEADER_CONTENT_TYPE] = self::MULTIPART_MIXED . "; boundary={$boundary}";
            
            $message = "--{$boundary}{$hs}" . implode("--{$boundary}{$hs}", $pieces) . "{$hs}--{$boundary}--{$hs}";
        }
        else {
            
            // Multipart email without attachments
            
            foreach ($this->_parts as $id => $part)
                $pieces[] = $this->_buildPart($part);
                
            if ($multipart_mode == self::MULTIPART_AUTO)
                $multipart_mode = $this->_determineMultipartMode();
            
            $boundary = uniqid('mixed_');
            $headers[self::HEADER_CONTENT_TYPE] = $multipart_mode . "; boundary={$boundary}";
                
            $message = "--{$boundary}{$hs}" . implode("--{$boundary}{$hs}", $pieces) . "{$hs}--{$boundary}--{$hs}";
        }
        
        unset($headers[self::HEADER_INTERNAL_TYPE]);
        foreach ($headers as $header => $value)
            $headers[$header] = "{$header}: {$value}";
        $headers = implode($hs, $headers);
        
        return compact('to', 'subject', 'message', 'headers');
    }
    
    protected function _determineMultipartMode () {
        if (!$internal_types = $this->_getInternalTypes())
            return false;
        
        if (count(array_unique($internal_types)) === 1)
            return self::MULTIPART_PARALLEL;
        if (!in_array('attachment', $internal_types))
            return self::MULTIPART_ALTERNATIVE;
        
        return self::MULTIPART_MIXED;
    }
    
    protected function _getInternalTypes () {
        if (empty($this->_parts))
            return false;
        
        foreach ($this->_parts as $id => $part) {
            $internal_types[$id] = isset($part['headers'][self::HEADER_INTERNAL_TYPE]) ? 
                $part['headers'][self::HEADER_INTERNAL_TYPE]: 'unknown';
        }
        
        return $internal_types;
    }
    
    protected function _buildPart ($part) {
        $part_buffer = "";
        
        unset($part['headers'][self::HEADER_INTERNAL_TYPE]);
        foreach ($part['headers'] as $header => $value)
            $part_buffer .= "{$header}: {$value}{$this->headerSeparator}";
            
        if (!empty($part_buffer))
            $part_buffer .= $this->headerSeparator;
            
        $part_buffer .= $part['content'];
        return $part_buffer;
    }
    
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
     * @brief This header is used only by the axMail class to recognize message parts and will not be send
     * @var string
     */
    const HEADER_INTERNAL_TYPE = "X-Internal";
    
    const MULTIPART_AUTO = "auto";
    const MULTIPART_MIXED = "multipart/mixed";
    const MULTIPART_ALTERNATIVE = "multipart/alternative";
    const MULTIPART_DIGEST = "multipart/digest";
    const MULTIPART_PARALLEL = "mutlipart/parallel";
    
    const SEND_ALL = "all";
    const SEND_BULK = "bulk";
}

defined('LF')   or define('LF',   "\n");
defined('CRLF') or define('CRLF', "\r\n");

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