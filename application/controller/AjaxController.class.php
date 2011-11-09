<?php

class AjaxController extends BaseController {
    
    public static function _init (Request &$request, Response &$response) {
        parent::_init($request, $response);
        self::$_response->setOutputFormat('json');
    }
    
    public static function index () {
        /* NOOP */
    }
    
    public static function translations () {
        $translations = Lang::getTranslations();
        return compact('translations');
    }
}