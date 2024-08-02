<?php
class DB{
	static private $connect = false;
	static private $query = false; // self::Query->execute([...])
	static private $message = '';
	static private $status = [
		'error' => null, // true when error
		'success' => null, // true when success
	];
	static private $config = [
		'type' => null,
		'host' => null,
		'port' => null,
		'user' => null,
		'pass' => null,
		'name' => null,
	];

	function __construct(){}

	static function isConnected(){ return self::$connect; }

	// $config : type, host, user, pass, name
	static function connect($config=[]){
		if(self::isConnected() && isset($config['name']) && $config['name']===self::$config['name']){ self::success(true); return true; }
		#
		$temp = Inc::config('db'); // default
		foreach($config as $key => $val){ $temp[$key] = $val; }
		$config = $temp; unset($temp);
		#
		$config['type'] = isset($config['type']) ? $config['type'] : 'mysql';
		foreach(['type', 'host', 'port', 'user', 'pass', 'name'] as $key){
			if(!isset($config[$key])){ self::error(true); return false; }
			self::$config[$key] = $config[$key];
		}
		#
		try{
			self::$connect = new PDO($config['type'].":host=".$config['host'].";port=".$config['port'].";dbname=".$config['name'].";charset=utf8mb4", $config['user'], $config['pass']);
			if(!self::$connect){ self::error(true); return false; }
			// setting PDO
			self::$connect->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			// dont convert column to string
			self::$connect->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
			// error throw to try catch
			self::$connect->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		}catch(Exception $e){ self::$message=$e; self::error(true); return false; }
		self::success(true); return true;
	}

	// show message
	static function message(){ return self::$message; }

	// reset all status
	static function unstatus($keys=false, $set=false){
		$keys = $keys === false ? self::$status : $keys;
		foreach($keys as $key => $val){ self::$status[$key] = $set; }
		return self::class;
	}
	# set status
	static function error($status=null){
		if(!is_null($status)){
			self::unstatus();
			self::$status['error'] = $status;
			return self::class;
		}return self::$status['error'];
	}
	static function success($status=null){
		if(!is_null($status)){ 
			self::unstatus();
			self::$status['success'] = $status;
			return self::class;
		}return self::$status['success'];
	}

	# get database name
	static function database(){
		if(!self::isConnected()){ return !self::error(true); }
		if(self::$config['name']){ return self::$config['name']; }
		#
		$ret = self::query('SELECT database();')::execute()::fetch();
		return self::error() || !isset($ret['database()']) ? !self::error(true) : $ret['database()'];
	}

	static function query($sql){
		// e.g. $sql = "SELECT * FROM `account` WHERE `id`=:example;";
		if(!self::isConnected()){ return self::error(true); }
		try{
			self::$query = self::$connect->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			return self::success(true);
		}catch (Exception $e) { self::$message=$e; return self::error(true); }
	}

	static function execute($values=[]){
		if(!self::isConnected() || self::error()){ return self::error(true); }
		try{
		    if(self::$query->execute($values) === false){ // e.g. [':example'=>'value']
		    	return self::error(true); // self::$query->debugDumpParams();
			}else{ return self::success(true); }
		}catch (Exception $e) { self::$message=$e; return self::error(true); }
	}

	static function sentence(){ return self::$query->queryString; }
	static function debugDumpParams(){ return self::$query->debugDumpParams(); }

	static function fetch($type='assoc'){
		if(!self::isConnected() || self::error()){ self::error(true); return false; }
		#
		$type = strtolower(trim($type));
		$result = self::$query;
		# array: Both number and column name save as index
		# assoc: Save column name as index
		# row: Save number as index
		if($type==='assoc'){ return $result->fetch(PDO::FETCH_ASSOC); }
		if($type==='array'){ return $result->fetch(PDO::FETCH_BOTH); }
		if($type==='row'){ return $result->fetch(PDO::FETCH_NUM); }
		if($type==='class'){ return $result->fetch(PDO::FETCH_CLASS); }
		self::error(true); return false;
	}

	static function fetchAll($type='assoc'){
		if(!self::isConnected() || self::error()){ self::error(true); return false; }
		#
		$type = strtolower(trim($type));
		$result = self::$query;
		# array: Both number and column name save as index
		# assoc: Save column name as index
		# row: Save number as index
		if($type==='assoc'){ return $result->fetchAll(PDO::FETCH_ASSOC); }
		if($type==='array'){ return $result->fetchAll(PDO::FETCH_BOTH); }
		if($type==='row'){ return $result->fetchAll(PDO::FETCH_NUM); }
		if($type==='class'){ return $result->fetchAll(PDO::FETCH_CLASS); }
		self::error(true); return false;
	}

	static function rowCount(){
		if(!self::isConnected() || self::error()){ return false; }
		return self::$query->rowCount();
	}

    static function lastInsertId(){
		if(!self::isConnected() || self::error()){ return false; }
		return (int)self::$connect->lastInsertId();
	}

	static function beginTransaction(){ self::$connect->beginTransaction(); }
	static function commit(){ self::$connect->commit(); }
	static function rollback(){ self::$connect->rollback(); }
}
