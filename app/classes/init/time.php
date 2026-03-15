<?php
class Time{
    private $timestamp;
    private $datetime;
    function __construct($timestamp=null){
        $timestamp = is_null($timestamp) ? microtime(true) : $timestamp;
        $this->timestamp = $timestamp;
        $this->datetime = DateTime::createFromFormat('U.u', $timestamp, new DateTimeZone('UTC'));
        $this->datetime->setTimezone(new DateTimeZone(date_default_timezone_get()));
    }
    function format($format="Y-m-d H:i:s.v"){ return $this->datetime->format($format); }
}