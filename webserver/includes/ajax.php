<?php
include("function.php");
include("../config.php");
include("class/sql.php");
if((isset($_POST['user']))&&(isset($_POST['pass']))){
	if(($_POST['user']==USER)&&($_POST['pass']==PASS)){
		setcookie("Valvola", "admin", time()+864000, URL_BASE); 
		echo "yes";
	} else {
		echo "no";
	}
} elseif(isset($_POST['logout'])){
	setcookie("Valvola", USER, time()-10, URL_BASE); 
	echo "yes";
} elseif((isset($_POST['temp']))&&(isset($_POST['ip']))&&(checkIp($_POST['ip']))){
	$url="http://".$_POST['ip']."/temp";
	$response=@file_get_contents($url);	
	if($response!=false){
		echo $response;
	} else {
		echo "errore";
	}
	
} elseif((isset($_POST['ntp']))&&(isset($_POST['ip']))&&(checkIp($_POST['ip']))){
	$url="http://".$_POST['ip']."/ntp";
	$response=@file_get_contents($url);	
	if($response!=false){
		echo $response;
	} else {
		echo "errore";
	}
	
} elseif((isset($_POST['offset']))&&(is_numeric($_POST['offset']))&&(isset($_POST['ip']))&&(checkIp($_POST['ip']))){
	$url="http://".$_POST['ip']."/ntpsave?offset=".$_POST['offset']."&hash=".getHash("sha256",$_POST['offset']);
	$response=@file_get_contents($url);	
	if($response=="ok"){
		echo "yes";
	} else {
		echo "no";
	}
	
} elseif((isset($_POST['open']))&&(isset($_POST['ip']))&&(checkIp($_POST['ip']))){
	$i=rand(0, 255);
	$url="http://".$_POST['ip']."/open?rand=".$i."&durata=".$_POST['durata']."&hash=".getHash("sha256",$i);
	$response=file_get_contents($url);	
	if($response=="aperto"){
		echo "yes";
	} else {
		echo "no";
	}
} elseif((isset($_POST['close']))&&(isset($_POST['ip']))&&(checkIp($_POST['ip']))){
	$i=rand(0, 255);
	$url="http://".$_POST['ip']."/close?rand=".$i."&hash=".getHash("sha256",$i);
	$response=file_get_contents($url);	
	if($response=="chiuso"){
		echo "yes";
	} else {
		echo "no";
	}
} elseif((isset($_POST['stato']))&&(isset($_POST['ip']))&&(checkIp($_POST['ip']))){
	$url="http://".$_POST['ip']."/";
	$response=@file_get_contents($url);	
	if($response!=false){
		echo $response;
	} else {
		echo "errore";
	}
} elseif((isset($_POST['ip']))&&(is_numeric($_POST['id']))&&(is_numeric($_POST['porta']))&&(checkIp($_POST['ip']))){
	$msql=new Sql($vldb);
	if ($msql->saveConf($_POST['id'],$_POST['ip'],$_POST['porta'],$_POST['nome'])){
		echo "yes";
	} else {
		echo "no";
	}
} elseif((isset($_POST['ric']))&&(is_numeric($_POST['id']))&&(is_numeric($_POST['id_conf']))&&(is_numeric($_POST['hh']))&&(is_numeric($_POST['mm']))&&(is_numeric($_POST['durata']))&&(is_numeric($_POST['active']))){
	$msql=new Sql($vldb);
	if ($msql->saveTimer($_POST['id'],$_POST['id_conf'],$_POST['ric'],$_POST['hh'],$_POST['mm'],$_POST['durata'],$_POST['active'])){
		echo "yes";
	} else {
		echo "no";
	}
}
?>