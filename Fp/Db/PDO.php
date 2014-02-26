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
class PDO {
	
	protected static $inTransaction = false;
	
	/**
	 * @param unknown_type $dsn
	 * @param unknown_type $bdd_login
	 * @param unknown_type $bdd_pass
	 *
	 */
	final public function __construct($dsn,$bdd_login,$bdd_pass,$type=null) {
		$this->dsn       = $dsn;
		$this->bdd_login = $bdd_login;
		$this->bdd_pass  = $bdd_pass;
		$this->connect   = 0;
		$this->link = '';
		$this->type = $type;
	}
	
	public function connect() {
		if ( !$this->connect ) {
			try {
				$this->link = new \PDO($this->dsn, $this->bdd_login, $this->bdd_pass);
				$this->link->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				$this->link->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('PDOStatement', array($this)));
				$this->link->exec("SET NAMES utf8");
			} catch  (\PDOException  $e) {
				$message = preg_replace("/{$this->bdd_pass}/",'***password***',$e->getMessage());
				$code = $e->getCode();
				$prev = $e->getPrevious();
				throw new Exception($message, $code, $prev);
			}
		}
		$this->connect = 1;
		return $this->link;
	}

	/**
	 * @return Db_Extend_PDOStatement
	 *
	 */
	public function query($sql) {
		$this->connect();
		$rid = $this->logReq(__METHOD__,$sql);
		try {
			$r = $this->link->query($sql);
			$this->logReqEnd($rid);
			return $r;
		} catch  (\Exception  $e) {
			Db::debug($e, $sql);
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
			Db::debug($e, $sql);
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
			Db::debug($e, $sql);
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
			Db::debug($e, $sql);
		}
	}

	/**
	 * @param unknown_type $string
	 * @return string
	 */
	public function quote($string, $paramtype = NULL) {
	    $this->connect();
		return $this->link->quote($string);
	}
	
	final public function inTransaction() {
		return self::$inTransaction;
	}
	
	final public function startTransaction() {
		$this->connect();
		if ( !self::$inTransaction ) {
			if ( $this->link->beginTransaction() ) {
				return self::$inTransaction = microtime();
			}
			throw new Exception(__METHOD__);
		}
	}

	final public function endTransaction($tid) {
		$this->connect();
		if ( self::$inTransaction  == $tid) {
		    self::$inTransaction = false;		    
		    if ( $this->link->inTransaction() ) {
    			return $this->link->commit();
		    }
		}
	}

	final public function rollback() {
		$this->connect();
		if ( $this->link->inTransaction() ) {
			self::$inTransaction = false;
			$this->link->rollBack();
			return true;
		}
	}
	
	final protected function logReq($method,$req) {
		return Db::logReq($method,$req);
	}
	
	final protected function logReqEnd($rid) { 
		Db::logReqEnd($rid);
	}	
}