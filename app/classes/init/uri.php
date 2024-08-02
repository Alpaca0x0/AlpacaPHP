<?php
class Uri{
    static function js($name, $exName='.js'){ return htmlentities(Root.Path::js.ltrim($name,'/').$exName); }
    static function css($name, $exName='.css'){ return htmlentities(Root.Path::css.ltrim($name,'/').$exName); }
    static function img($name){ return htmlentities(Root.Path::img.ltrim($name,'/')); }
    static function auth($name, $exName=''){ return htmlentities(Root.Path::auth.ltrim($name,'/').$exName); }
    static function api($name, $exName=''){ return htmlentities(Root.Path::api.ltrim($name,'/').$exName); }
    static function page($name, $exName=''){ return htmlentities(Root.ltrim($name,'/').$exName); }
    static function plugin($name){ return htmlentities(Root.Path::plugin.ltrim($name,'/')); }
}