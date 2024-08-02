<?php
class Resp{
    static private $buffer;

    static function header($headers='Content-Type: application/json; charset=utf-8'){
        if(headers_sent()){ self::error('header_send', 'Headers already been sent.'); }
        header($headers);
    }

    static private function write(){
        $args = func_get_args();
        #
        $type = isset($args[0]) ? $args[0] : 'error';
        $status = isset($args[1]) ? $args[1] : 'INIT';
        $data = isset($args[2], $args[3]) ? $args[2] : null;
        $message = isset($args[2]) ? $args[2] : null;
        $message = isset($args[3]) ? $args[3] : $message;
        #
        self::$buffer = [
            'type' => $type, # [string] info, success, warning, error
            'status' => $status, # [string] response status
            'data' => $data, # [*] it could be array or any types
            'message' => $message, # [string] description
        ]; return self::class;
    }

    static private function resp(){ die(json_encode(self::$buffer));}

    static function reset(){ return self::write(); }
    static function info(){ self::write('info', ...func_get_args())::resp(); }
    static function success(){ self::write('success', ...func_get_args())::resp(); }
    static function warning(){ self::write('warning', ...func_get_args())::resp(); }
    static function error(){ self::write('error', ...func_get_args())::resp(); }

}