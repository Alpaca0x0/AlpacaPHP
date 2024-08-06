<?php
class Uri{
    static function js($name, $exName='.js'){ return htmlentities(ROOT.Path::js.ltrim($name,'/').$exName); }
    static function css($name, $exName='.css'){ return htmlentities(ROOT.Path::css.ltrim($name,'/').$exName); }
    static function img($name){ return htmlentities(ROOT.Path::img.ltrim($name,'/')); }
    static function auth($name, $exName=''){ return htmlentities(ROOT.Path::auth.ltrim($name,'/').$exName); }
    static function api($name, $exName=''){ return htmlentities(ROOT.Path::api.ltrim($name,'/').$exName); }
    static function page($name, $exName=''){ return htmlentities(ROOT.ltrim($name,'/').$exName); }
    static function plugin($name){ return htmlentities(ROOT.Path::plugin.ltrim($name,'/')); }
}