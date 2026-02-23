<?php
class Is{
    static public function timestamp($timestamp) {
        return (filter_var($timestamp, FILTER_VALIDATE_INT) !== false && $timestamp >= 0);
    }
    static public function ts(){ return self::timestamp(...func_get_args()); }
}
