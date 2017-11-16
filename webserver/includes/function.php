<?php
function isLogged(){
	if (isset($_COOKIE['Valvola'])){
		return true;
	} else {
		false;
	}
}
function checkIp($ip){
	$pattern="/192.168.[0-9]{1,3}.[0-9]{1,3}/";
	return preg_match($pattern, $ip);
}
function getHash($algo,$data){
	return hash_hmac($algo, $data, 'secret');
}
?>