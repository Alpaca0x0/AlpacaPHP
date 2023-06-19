<?php
function ID($path){
	$path = trim($path);
	$path = $path?$path:$_SERVER['SCRIPT_NAME'];
	return hash('sha256',$path);
}