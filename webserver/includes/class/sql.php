<?php
#include("../function.php");
class Sql{
	private $mysqlObg;
	private $url="/valvola/";
	public function __construct($mysqlObg){
		$this->mysqlObg=$mysqlObg;
	}
	public function getLog($id){
		if($id>0){
			$queryStringCnf="SELECT * FROM log WHERE id_conf=".$id." ORDER BY data DESC LIMIT 25";
			$arrayConf=$this->mysqlObg->query($queryStringCnf);
			return $arrayConf;
		} else {
			return false;
		}
	}
	public function getConfig($id){
		if($id>0){
			$queryStringCnf="SELECT * FROM settings WHERE id=".$id;
			$arrayConf=$this->mysqlObg->query($queryStringCnf);
			return $arrayConf->fetch_assoc();
		} else {
			return array("id"=>0,"nome"=>NULL,"ip"=>NULL,"porta"=>NULL);
		}
	}
	public function saveConf($id,$ip,$porta,$nome){
		if($id>0){
			$queryStringCnf="UPDATE settings SET nome='$nome',ip='$ip',porta='$porta' WHERE id='$id'";
			$result=$this->mysqlObg->query($queryStringCnf);
			if($result){
				return true;
			} else {
				return false;
			}
		} else {
			$queryStringCnf="INSERT INTO settings (ip,porta) VALUES ('$ip','$porta')";
			$result=$this->mysqlObg->query($queryStringCnf);
			if($result){
				setcookie("Valvola-id", $this->mysqlObg->insert_id, time()+864000, $this->url); 
				return true;
			} else {
				return false;
			}
		}
	}
	public function saveActive($id){
		$queryStringCnf="UPDATE timer SET active=0 WHERE id_config='$id'";
		$result=$this->mysqlObg->query($queryStringCnf);
		if($result){
			return true;
		} else {
			return false;
		}
	}
	public function getTimer($id){
		if($id>0){
			$queryStringCnf="SELECT timer.id,timer.active,timer.frequenza,timer.ora,timer.minuti,timer.durata,settings.ip,settings.nome FROM timer INNER JOIN settings ON timer.id_config=settings.id WHERE timer.id_config=".$id;
			$arrayConf=$this->mysqlObg->query($queryStringCnf);	
			if($arrayConf){
				return $arrayConf->fetch_assoc();
			} else {
				return array("id"=>0,"active"=>NULL,"id_conf"=>0,"frequenza"=>NULL,"ora"=>NULL,"minuti"=>NULL,"durata"=>NULL);	
			}
		} else {
			return array("id"=>0,"active"=>true,"id_conf"=>0,"frequenza"=>NULL,"ora"=>NULL,"minuti"=>NULL,"durata"=>NULL);	
		}
	}
	public function saveTimer($id,$id_conf,$fr,$hh,$mm,$durata,$active){
		if($id>0){
			$queryStringCnf="UPDATE timer SET frequenza='$fr',ora='$hh',minuti='$mm',durata='$durata',active='$active' WHERE id='$id'";
		} else {
			$queryStringCnf="INSERT INTO timer (id_config,frequenza,ora,minuti,durata,active) VALUES ('$id_conf','$fr','$hh','$mm','$durata','$active')";
			}
		$result=$this->mysqlObg->query($queryStringCnf);
		if($result){
			$ar_fetch=$this->getTimer($id_conf);
			$data=http_build_query($ar_fetch);
			$opts = array('http' =>
			  array(
				'method'  => 'POST',
				'header'  => "Content-Type: text/html\r\n",
				'content' => $data,
				'timeout' => 60
			  )
			);
			$context  = stream_context_create($opts);;
			$url = "http://".$ar_fetch['ip']."/save?hash=".getHash("sha256",$ar_fetch['active'].$ar_fetch['frequenza'].$ar_fetch['ora'].$ar_fetch['minuti'].$ar_fetch['durata']);
			$response=@file_get_contents($url, false, $context);
			if($response!=false){
				return true;
			} else {
				false;
			}
		} else {
			return false;
		}
	}
	public function setIdCookie(){
		$queryStringCnf="SELECT id FROM settings ORDER BY id ASC LIMIT 1";
		$arrayConf=$this->mysqlObg->query($queryStringCnf);
		if($arrayConf->num_rows){
			$idf=$arrayConf->fetch_assoc();
			setcookie("Valvola-id", $idf['id'], time()+864000, $this->url); 
		} else {
			setcookie("Valvola-id", 0, time()+864000, $this->url); 
		}
	}
}
?>