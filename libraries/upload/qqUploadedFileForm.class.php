<?php

/**
 * Multipar/form-data File Uploader Class
 *
 * Handle file uploads via regular form post (uses the $_FILES array)
 *
 * @author valums
 * @link http://valums.com/ajax-upload/
 * @package libaxiom
 * @subpackage upload
 */
class qqUploadedFileForm {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}