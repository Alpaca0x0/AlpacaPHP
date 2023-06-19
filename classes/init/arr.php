<?php
class Arr{
    static function every($array){
        $args = func_get_args();
        array_shift($args);
        foreach ($args as $arg) { if(!array_key_exists($arg, $array)) return false; }
        return true;
    }

    static function includes($array){
        $args = func_get_args();
        array_shift($args);
        foreach ($args as $arg) { if(array_key_exists($arg, $array)) return true; }
        return false;
    }

    // string to N-D arrays
	static function nd($result, $splitSign='.'){
		$ret = [];
		foreach($result as $keysStr => $val){
			$keys = explode($splitSign, $keysStr);
			$current = &$ret;
			foreach ($keys as $key) {
				$current[$key] = array_key_exists($key, $current) ? $current[$key] : [];
				$current = &$current[$key];
			} $current = $val;
		}
		// return
		return $ret;
	}
}