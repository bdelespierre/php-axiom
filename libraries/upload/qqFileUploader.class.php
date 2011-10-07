<?php

class qqFileUploader {
    
    private static $_config;
    
    private $allowedExtensions;
    
    private $sizeLimit;
    
    private $file;
    
    public static function setConfig (array $config = array()) {
        $defaults = array(
            'allowed_extensions' => array(),
            'size_limit' => 10485760,
        );
        
        self::$_config = $config + $defaults;
    }

    public function __construct () {
        $this->allowedExtensions = & self::$_config['allowed_extensions'];
        $this->sizeLimit = & self::$_config['size_limit'];
        $this->checkServerSettings();

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false;
        }
    }
    
    private function checkServerSettings(){
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            throw new RuntimeException("Increase post_max_size and upload_max_filesize to $size", 2047);
        }
    }
    
    private static function toBytes($str){
        $val = trim($str);
        $last = strtolower(substr($str, -1));
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    public function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        //$filename = $pathinfo['filename'];
        $filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            return array('success' => true, 'filename' => $filename . '.' . $ext);
        }
        else {
            return array('error'=> 'Could not save uploaded file. The upload was cancelled, or server error encountered');
        }
    }
}