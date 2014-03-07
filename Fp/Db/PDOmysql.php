<?php
namespace Fp\Db;

use \Exception;

/**
* Copyright Desgranges Mickael 
* mickael@4publish.com
* 
* Ce logiciel est un programme informatique servant à la création d'application web. 
* 
* Ce logiciel est régi par la licence CeCILL-B soumise au droit français et
* respectant les principes de diffusion des logiciels libres. Vous pouvez
* utiliser, modifier et/ou redistribuer ce programme sous les conditions
* de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA 
* sur le site "http://www.cecill.info".
* 
* En contrepartie de l'accessibilité au code source et des droits de copie,
* de modification et de redistribution accordés par cette licence, il n'est
* offert aux utilisateurs qu'une garantie limitée.  Pour les mêmes raisons,
* seule une responsabilité restreinte pèse sur l'auteur du programme,  le
* titulaire des droits patrimoniaux et les concédants successifs.
* 
* A cet égard  l'attention de l'utilisateur est attirée sur les risques
* associés au chargement,  à l'utilisation,  à la modification et/ou au
* développement et à la reproduction du logiciel par l'utilisateur étant 
* donné sa spécificité de logiciel libre, qui peut le rendre complexe à 
* manipuler et qui le réserve donc à des développeurs et des professionnels
* avertis possédant  des  connaissances  informatiques approfondies.  Les
* utilisateurs sont donc invités à charger  et  tester  l'adéquation  du
* logiciel à leurs besoins dans des conditions permettant d'assurer la
* sécurité de leurs systèmes et ou de leurs données et, plus généralement, 
* à l'utiliser et l'exploiter dans les mêmes conditions de sécurité. 
* 
* Le fait que vous puissiez accéder à cet en-tête signifie que vous avez 
* pris connaissance de la licence CeCILL-B, et que vous en avez accepté les
* termes.
*
* @package		4_publish
* @subpackage	core
* @author		Desgranges Mickael
* @license		CeciLL-B
* @link			http://4publish.com
*/
class PDOmysql extends PDO {

	public function connect() { 		
		if ( !$this->connect ) {
			try {	
				$this->link = new \PDO($this->dsn,$this->bdd_login,$this->bdd_pass);					
				$this->link->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				$this->link->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('Fp\Db\PDOStatement', array($this)));			
				$this->link->exec("SET NAMES utf8");
				$this->connect = 1;
			} catch  (\PDOException  $e) {	
				$message = preg_replace("/{$this->bdd_pass}/",'***password***',$e->getMessage());
				$code = $e->getCode();
				$prev = $e->getPrevious();
				throw new Exception($message, $code, $prev);
			}
		}
		return $this->link;
	}
	
	/*
	public function query($sql) {
		$this->connect();		
		$rid = $this->logReq(__METHOD__,$sql);		
		try {
			$r = $this->link->query($sql);
			$this->logReqEnd($rid);
			return $r;
		} catch  (\Exception  $e) {
			Db::debug($e."\r\n ".$sql."\r\n ");			
		}
		$this->logReqEnd($rid);
	}
	public function prepare($sql,$driver_options=array()){
		$this->connect();
		$rid = $this->logReq(__METHOD__,$sql);
		try {
			$r = $this->link->prepare($sql,$driver_options);
			$this->logReqEnd($rid);
			return $r;
		} catch  (\PDOException  $e) {
			Db::debug($e."\r\n ".$sql."\r\n ");
		}
	}
	public function exec($sql){
		$this->connect();
		$rid = $this->logReq(__METHOD__,$sql);
		try { 
			$r = $this->link->exec( $sql );
			$this->logReqEnd($rid);
			return $r;
		} catch  (\PDOException  $e) {
			Db::debug($e."\r\n ".$sql."\r\n ");
		}
	}
	
	public function select_db($db_name){
		$this->connect();
		$sql = "USE $db_name";
		$rid = $this->logReq(__METHOD__,$sql);
		try { 
			$r = $this->link->exec( $sql );
			$this->logReqEnd($rid);
			return true;
		} catch  (\PDOException  $e) {
			Db::debug($e."\r\n ".$sql."\r\n ");
		}
	}
	

	public function quote($string, $paramtype = NULL) {
		$this->connect();
		return $this->link->quote($string);
	}
	
	public function lastInsertId($seqname = NULL) {
	    $this->connect();
	    try {
	        return$this->link->lastInsertId($seqname);
	    } catch  (\PDOException  $e) {
	        Db::debug($e."\r\n ".$sql."\r\n ");
	    }
	}
	*/
}