<?php
namespace Fp\Core;
use \Exception;
/**
* Copyright Desgranges Mickael 
* mickael@4publish.com
* 
* Ce logiciel est un programme informatique servant à la création d'application web. 
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
class Session {
	private $S;
	private static $i;
	private static $prefix = null;
	public static $mode = 'sql';
	
	/**
	 * @var sqlSessionStore
	 */
	public $sessionStore;
	public $session_name = 'PHPSESSID';
	public $session_id   = null;
	
	private function __construct(Core $O) {		
			
		if ( self::$mode == 'sql' ) {	
			try {				
				$this->sessionStore = new sqlSessionStore($O);			
			} catch (\Exception $e) {
				echo $e->getMessage();			
			}
		}
		self::$prefix = ( $p = $O->glob('prefix') ) ? $p : 'session_';
		
		
		$currentCookieParams = session_get_cookie_params();		
		$rootDomain = $O->glob('domain');		
		session_set_cookie_params(
				$currentCookieParams["lifetime"],
				$currentCookieParams["path"],
				$rootDomain,
				$currentCookieParams["secure"],
				$currentCookieParams["httponly"]
		);
				
		//allow session id for post
		//jsonRpc + plupload flash upload not work with cookie
		if ( !isset($_COOKIE[$this->session_name]) && isset($_POST[$this->session_name]) ) {					
			session_id($_POST[$this->session_name]);
		}
		
		$this->start();
		
		if ( !isset($_SESSION[self::$prefix]) ) {
			      $_SESSION[self::$prefix] = array();
		}
	}

	public function start() {
		$r = session_start();		
		return $r;
	}
	
	public function name($name=null) {
		return session_name($name);
	} 
	public function id($id=null) {
		return session_id($id);
	} 
	
	static public function load(Core $O) {
		if ( !isset(self::$i) ) {			
			$c = __CLASS__;
			self::$i = new $c($O);			
		}
		return self::$i;
	}
	
	public static function getInstance()  {
		if ( self::$i !== null ) {
			return self::$i;
		}
	}

	public function clear() {
		$_SESSION[self::$prefix] = array();
	}
	
	public static function close() {		
		session_write_close();
	}
	
	public function setInArray($arrayName,$key,$value) {
		if ( !isset($this->S[$arrayName]) ) { 
			$_SESSION[self::$prefix][$arrayName] = array();
		}
		if ( is_array($this->S[$arrayName]) ) { 
			$_SESSION[self::$prefix][$arrayName][$key] = $value;
		} 
		else throw new Exception(__METHOD__);
	}
	
	
	public function remove($name) {
		$this->__unset($name);
	}
	
	public function __unset($name) {
		unset($_SESSION[self::$prefix][$name]);
	}
	
	public function set($name,$value) { 		
		$this->__set($name,$value);		
	}
	
	public function get($name) {		
		return $this->__get($name);
	}
	
	public function __set($name,$value) {		
		$_SESSION[self::$prefix][$name] = $value;		
	}
	
	public function __isset($name) {
		if ( array_key_exists($name, $_SESSION[self::$prefix]) ) return true;	
	}
	
	public function __get($name) {		
		if ( isset($_SESSION[self::$prefix][$name]) ) {				
			return $_SESSION[self::$prefix][$name];			
		}	
	}	
	
	public function destroy() {	
		return session_destroy();	
	}
	
}

/*
CREATE  TABLE IF NOT EXISTS `app`.`sessions` (
  `session_id` VARCHAR(255) NOT NULL ,
  `data` TEXT NULL DEFAULT NULL ,
  `lastaccess` INT(10) UNSIGNED NULL DEFAULT '0' ,
  PRIMARY KEY (`session_id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
 */
/*
 * @todo rename for postgres 
 * rewrite write with delete/insert
 */
class sqlSessionStore {
	/**
	 * Enter description here ...
	 * @var Db
	 */
	public $db;
	public $table = 'sessions';
	
    public function __construct(Core $O) {  
    	$this->table = $O->glob('prefix').$this->table;   	
    	/*
        session_set_save_handler(
            array($this, "open"),
            array($this, "close"),
            array($this, "read"),
            array($this, "write"),
            array($this, "destroy"),
            array($this, "gc")
        );*/
    }

    public function open($savePath, $sessName) {    	
    	$this->db = Db::get_link();
        return true;
    }

    public function close() {
    	$this->gc(get_cfg_var("session.gc_maxlifetime"));
        //return $this->db->close();
    }

    public function read($id) {
        //fetch the session record
         $res = $this->db->prepare("SELECT data FROM $this->table WHERE id = :session_id"); 
         $res->execute( array(':session_id'=>$id));

        if ( $row = $res->fetchAssoc() ) return $row['data'];
        //MUST send an empty string if no session data
        return "";
    }

    public function write($id,$data){    	    
       $res = $this->db->prepare("DELETE FROM $this->table WHERE id = :session_id");
       $res->execute( array(':session_id'=>$id) ); 
       $time = time();
       $res = $this->db->prepare("INSERT INTO $this->table 
       			(id,lastaccess,data)
                VALUES(:session_id, 
                 	   :time,
                 	   :data)");
       $res->execute( array(':session_id'=>$id , ':time'=>$time, ':data'=>$data ) );
       if( $res->rowCount() ) return true;
    }

    public function destroy($id) {
       //remove session record from the database and return result
       $res = $this->db->prepare("DELETE FROM $this->table WHERE id = :session_id");
       $res->execute( array(':session_id'=>$id ,':data'=>$data ) );       
       if( $res->rowCount() ) return true;
    }

    public function gc($maxLifeTime){
       //garbage collection
       $timeout = time() - $maxLifeTime;
       $res = $this->db->query("DELETE FROM $this->table WHERE lastaccess < '".$timeout."'");
       if( $res->rowCount() ) return true;
    }
}