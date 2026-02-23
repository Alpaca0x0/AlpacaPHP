<?php
class Type{
    static private function convert($type,$data){
        $replace = isset(func_get_args()[2]) ? func_get_args()[2] : null;
        $type = trim(strtolower($type));
        try {
            if($type==='json'){
                $replace = isset(func_get_args()[2]) ? $replace : [];
                if(is_array($data)){ return $data; }
                $ret = @json_decode($data, true);
                return is_array($ret) ? $ret : $replace;
            }else if(in_array($type, ['datetime', 'date', 'time', 'timestamp'])){
                $formats = [
                    'datetime' => 'Y-m-d H:i:s',
                    'date' => 'Y-m-d', 
                    'time' => 'H:i:s',
                ];
                $dt = new DateTimeImmutable($data ?? 0);
                if(!$dt){ return $replace; }
                // ts
                if(in_array($type, ['timestamp'])){ return $dt->getTimestamp(); }
                // 
                $format = $formats[$type];
                return str_starts_with($dt->format('Y') ?? '-', '-') ? $replace : $dt->format($format);
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
    static function float(){ return self::convert('float', ...func_get_args()); }
    static function datetime(){ return self::convert('datetime', ...func_get_args()); }
    static function date(){ return self::convert('date', ...func_get_args()); }
    static function time(){ return self::convert('time', ...func_get_args()); }
    static function timestamp(){ return self::convert('timestamp', ...func_get_args()); }
    static function ts(){ return self::convert('timestamp', ...func_get_args()); }
}