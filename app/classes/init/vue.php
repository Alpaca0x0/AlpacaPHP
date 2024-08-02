<?php
class Vue{
    const version = '3.3.4';
    static function uri(){
        return Uri::js('vue/'.self::version.'/'.(DEV ? 'vue.esm-browser.min' : 'vue.esm-browser.prod.min'));
    }
}