<?php
class Uri{
    static function js($name, $exName='.js'){ return htmlentities(Root.Path::js.$name.$exName); }
    static function css($name, $exName='.css'){ return htmlentities(Root.Path::css.$name.$exName); }
    static function img($name){ return htmlentities(Root.Path::img.$name); }
    static function auth($name, $exName=''){ return htmlentities(Root.Path::auth.$name.$exName); }
    static function api($name, $exName=''){ return htmlentities(Root.Path::api.$name.$exName); }
    // static function root($name){ return htmlentities(Root.$name); }
    static function page($name, $exName=''){ return htmlentities(Root.$name.$exName); }
    static function plugin($name){ return htmlentities(Root.Path::plugin.$name); }
}