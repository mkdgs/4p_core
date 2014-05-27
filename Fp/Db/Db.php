<?php
namespace Fp\Db;

use \Exception;

require_once __DIR__.'/PDO.php';


/**
 * Copyright Desgranges Mickael
 * mickael@4publish.com
 *
 * Ce logiciel est un programme informatique servant à la création d'application web. 
 *
 * Ce logiciel est régi par la licence CeCILL-B soumise au droit français e
 * respectant les principes de diffusion des logiciels libres. Vous pouvez
 * utiliser, modifier et/ou redistribuer ce programme sous les conditions
 * de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA 
 * sur le site "http://www.cecill.info".
 *
 * En contrepartie de l'accessibilité au code source et des droits de copie,
 * de modification et de redistribution accordés par cette licence, il n'est
 * offert aux utilisateurs qu'une garantie limitée.  Pour les mêmes raisons,
 * seule une responsabilité restreinte pèse sur l'auteur du programme,  
 * titulaire des droits patrimoniaux et les concédants successifs.
 *
 * A cet égard  l'attention de l'utilisateur est attirée sur les risques
 * associés au chargement,  à l'utilisation,  à la modification et/ou au
 * développement et à la reproduction du logiciel par l'utilisateur étant 
 * donné sa spécificité de logiciel libre, qui peut le rendre complexe à 
 * manipuler et qui le réserve donc à des développeurs et des professionnels
 * avertis possédant  des  connaissances  informatiques approfondies.  Les
 * utilisateurs sont donc invités à charger  et  tester  l'adéquation  d
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
class Db {
	protected static $DB = array();
	public static $link_id = null;
	protected static $last_link_id = null;
	protected static $DEBUG = 1;
	protected static $LogReq = array();

	protected static $inTransaction = false;

	protected function __construct() { die('Don\'t tell '.__CLASS__.' in '.__FILE__ ); }

	// connection a une base de donnée 
	// et stockage dans la listes des connections disponible
	/**
	* @param unknown_type $id
	* @param unknown_type $bdd_host
	* @param unknown_type $bdd_login
	* @param unknown_type $bdd_pass
	* @param unknown_type $bdd_base
	* @return string
	*/
	public static function connect($link_id='default',$bdd_host=false, $bdd_login=false, $bdd_pass=false, $bdd_base=false, $type=null) {
		if ( empty(self::$DB) ) 					self::$DB = array();
		if ( empty($link_id) ) 						die('db_connect: id absent 1');
		if ( array_key_exists($link_id,self::$DB) ) die('db_connect: duplicate id ');
		if ( !$type ) $type = 'mysql';
		$dsn = $type.':dbname='. $bdd_base .';host='. $bdd_host;
                
                if ( $type == 'mysql' ) {
                    require_once __DIR__.'/PDOmysql.php';
                    require_once __DIR__.'/PDOpgsql.php';
                }
		$Db_Extend_PDO = '\Fp\Db\PDO'.$type;
		self::$DB[$link_id] = array('link' => new $Db_Extend_PDO($dsn,$bdd_login,$bdd_pass, $type),
									'type' => $type,
							   		'base' => $bdd_base,
									'id'   => $link_id);	
		self::$link_id = $link_id;
		return self::get_link($link_id);
	}

	/**
	 * retourne la connection
	 * @param string $link_id
	 * @return Db
	 */
	public static function get_link($link_id=null) {
		if ( $link_id ) self::select_Link($link_id);
		if ( array_key_exists(self::$link_id, self::$DB) ) return self::$DB[self::$link_id]['link'];
		throw new Exception('link not found');
	}

	public static  function select_Link($link_id) {
		if ( array_key_exists($link_id,self::$DB) ) self::$link_id = $link_id;
		else throw new Exception('id not found');
	}

	public static function isConnected($link_id=null) {
		if ( self::get_link() ) return true;
	}

	// selection d'une base de données pour la connection
	public static function select_db($db_name,$db_con_id=null) {
		if ( !$db_con_id ) 							  $db_con_id = self::$link_id;
		if ( array_key_exists($db_con_id,self::$DB) ) return self::get_link()->select_db($db_name);
		throw new Exception(' Db '.$db_con_id.' not found' ,500);
	}

	// fermeture connection
	public static function close($all=null) {
		if ( $all ) foreach ( self::$DB as $k => $v ) self::$DB[$k] = null;
		elseif ( array_key_exists(self::$link_id,self::$DB) ) self::$DB[self::$link_id] = null;
	}

	/**
	 * query sur la connection courante
	 * @param string $query
	 * @return PDOStatement
	 */
	public static function query($query) {
		try {
			$result =  self::get_link()->query($query);
			if ( is_object($result) ) return $result;
		} catch  (\PDOException  $e) { self::debug($e); }
	}

	// execute une requéte sans traitement en retour
	public static function exec($query) {
		try {
			return self::get_link()->exec($query);
		} catch  (\PDOException  $e) { self::debug($e);	}
	}

	/**
	 * @param unknown_type $query
	 * @return Db_Extend_PDOStatement
	 */
	public static function prepare($query) {
		try {
			$result = self::get_link()->prepare($query);
			if ( is_object($result) ) return  $result;
		} catch  (\PDOException  $e) { self::debug($e); }
	}

	// @todo global transaction over all db
	public static function startTransaction() {
		if ( !self::$inTransaction ) { 
			$tid =  self::get_link()->startTransaction();
			self::$inTransaction = $tid;
			return $tid;
		}
	}

	public static function endTransaction($tid) {
		if ( $tid == self::$inTransaction ) {
		    if ( self::get_link()->inTransaction() ) {		    
		        return  self::get_link()->endTransaction($tid);
		    }
		}
	}

	public static function rollback() {
		try {
		    if ( self::get_link()->inTransaction() ) {
		       return  self::get_link()->rollBack();
		    }			
		} catch (\Exception $e ) { }
	}

	public static function commit() {
		return  self::get_link()->commit();
	}

	public static function foreignKeyCheks($bool) {
		$bool = ( $bool ) ? '1' : '0';
		if ( $bool ) return  self::get_link()->exec("SET FOREIGN_KEY_CHECKS=$bool;");
	}

	// voodoo
	public static function set_debug($set=null) {
		self::$DEBUG = $set;
	}

	public static function get_debug($set=null) {
		return self::$DEBUG;
	}

	public static function logReq($method,$req) {
		if ( self::$DEBUG ) {
			$rid = count(self::$LogReq);
			self::$LogReq[$rid] =  array( 'method' => $method,
									  'sql'    => substr($req,0,5500),
									  'start'  => microtime(true),
									  'duration'    => '',
									  'memory_start' => memory_get_usage(),
									  'memory' => '',
									  'memory_peak' => '');
			return $rid;
		}
	}

	public static function logReqEnd($rid) {
		if ( self::$DEBUG && array_key_exists($rid, self::$LogReq) ) {
			self::$LogReq[$rid]['memory_peak'] = round(memory_get_peak_usage()/1024,2).' Ko';
			self::$LogReq[$rid]['duration']    = number_format(microtime(true)-self::$LogReq[$rid]['start'], 5, ',', ' ').' sec';
			self::$LogReq[$rid]['memory']      = round((memory_get_usage()-self::$LogReq[$rid]['memory_start'])/1024,2).' Ko';
		}
	}

	public static function &get_logReq() {
		return self::$LogReq;
	}

	public static function debug($e) {	    
		if ( self::$DEBUG ) {
		     if ( $e instanceof \Exception ) throw $e; 
		     else throw new \Exception($e);
		    
		}
	}

	/**
	 * @param unknown_type $string
	 * @return string
	 */
	public static function quote($string) {
		return self::get_link()->quote($string);
	}

}