<?php
/**
 * Axiom: a lightweight PHP framework
 *
 * @copyright Copyright 2010-2011, Benjamin Delespierre (http://bdelespierre.fr)
 * @licence http://www.gnu.org/licenses/lgpl.html Lesser General Public Licence version 3
 */

class UploadController extends BaseController {
    
    public static function index () {
        self::$_response->setResponseView('upload');
        return self::upload();
    }
    
    public static function upload () {
        self::$_response->setOutputFormat('json');
        
        $uploader = new qqFileUploader();
        $result = $uploader->handleUpload(dirname(dirname(__FILE__)) . '/webroot/upload/');
        
        return compact('result');
    }
}