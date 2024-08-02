<?php
class Data{
    static function get($var, $replace=null){ return isset($_GET[$var]) ? $_GET[$var] : $replace; }
    static function post($var, $replace=null){ return isset($_POST[$var]) ? $_POST[$var] : $replace; }
}
