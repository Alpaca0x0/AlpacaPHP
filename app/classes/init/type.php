<?php
class Type{
    static private function convert($type,$data){
        $replace = isset(func_get_args()[2]) ? func_get_args()[2] : null;
        $type = trim(strtolower($type));
        try {
            if($type==='json'){
                $replace = isset(func_get_args()[2]) ? $replace : [];
                if(is_array($data)){ return $data; }
                $ret = @json_decode($data);
                return is_array($ret) ? $ret : $replace;
            }
            else if(@settype($data,$type) !== true){ throw new Exception('type convert error'); }
        } catch (\Throwable $th) {
            //throw $th;
            $data = $replace;
        }
        return $data;
    }

    static function int(){ return self::convert('int', ...func_get_args()); }
    static function string(){ return self::convert('string', ...func_get_args()); }
    static function bool(){ return self::convert('bool', ...func_get_args()); }
    static function json(){ return self::convert('json', ...func_get_args()); }
    static function object(){ return self::convert('object', ...func_get_args()); }
    static function array(){ return self::convert('array', ...func_get_args()); }
}