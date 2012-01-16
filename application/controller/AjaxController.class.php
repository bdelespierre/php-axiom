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
        self::$_request->setFilter(array(
            'modules' => array(
            	'filter' => FILTER_SANITIZE_STRING,
            	'flags'  => FILTER_FORCE_ARRAY,
            ),
            'lang' => FILTER_SANITIZE_STRING
        ));
        
        if (!$lang = self::$_request->lang)
            $lang = Lang::getLocale();
        
        if ($modules = self::$_request->modules) {
            foreach ($modules as $module) {
                if (is_file($path = APPLICATION_PATH . "/module/{$module}/locale/langs/{$lang}.ini")) {
                    Lang::loadLanguage($path);
                }
                else {
                    Log::warning("Unable to find {$path}");
                }
            }
        }
        
        $translations = Lang::getTranslations();
        return compact('translations');
    }
}