<?php
namespace Fp\Login;
use Fp\Table\Table;
use Fp\Table\Query;
use Fp\Db\Db;
use Fp\Core\Cookie;

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
class Password {
	private $token   	  = null;
	private $salt_token   = 'un_petit_grain_de_sel';
	public $table 		  = 'login_password';	
	public $crypt_function = 'md5';
	
	/**
	 * Enter description here ...
	 * @var Table_query
	 */
	public $dbLoginPassword = null;
	
	public function __construct(\Fp\Core\Core $O) { 
		if ( $O->glob('crypt_function') ) { 
			$this->crypt_function = $O->glob('crypt_function');
		} 
		/*
@TODO delete after Table test validation 
		$tableLoginPassword 	 = $this->table;
		$columnLoginPassword  = array('uid','password','password_tmp');
		$this->tableLoginPassword  = Table::set(Db::get_link(), $tableLoginPassword, $columnLoginPassword);
		$this->tableLoginPassword->setPrimary('uid');
		$this->tableLoginPassword->setUnique(array());
		$this->tableLoginPassword->setSortable(array('uid','password','password_tmp'));
		$this->tableLoginPassword->setSearchable(array (
		  'uid' => 'bigint',
		  'password' => 'varchar',
		  'password_tmp' => 'varchar',
		));
		$this->tableLoginPassword->setAutoIncrement();	
		*/
		$dbLink = Db::get_link();
		$this->table  = $O->glob('prefix').$this->table;
		$shemaPassword  = array('table' => $this->table,
				'column'=> array(
						'uid'                    => array('type'=>'bigint','primary'=>1,'sortable'=>1,'searchable'=>1),
						'password'               => array('type'=>'varchar','sortable'=>1,'searchable'=>1),
						'password_tmp'           => array('type'=>'varchar','sortable'=>1,'searchable'=>1),
				));
		$this->tablePassword = Table::setTable($dbLink, $shemaPassword);
		
		
		$this->dbLoginPassword = new Query($this->tablePassword,'LoginPassword');
	}
	
	public function crypt_function($data) { 
		$crypt = $this->crypt_function;
		return $crypt($data);
	}
	
	public function getTable() { return $this->table; }	
	
	public  function check($uid,$password,$md5) {
		if ( $md5 ) $md5pass=$this->crypt_function($password);		
		else $md5pass=$password;
		$q = $this->dbLoginPassword->duplicate();
		$q->andWhere(array('uid'=>$uid,'password'=>$md5pass));
		$q->selectColumn('uid,password');
		return $q->getAssoc();
	}
	
	public function checkToken($uid,$token=null) {
		$gentok = $this->getToken($uid);
		if ( $gentok == $token ) return $uid;
	}
	public function sendCookieToken($uid) {
		$token = $this->genCookieToken($uid); 
		Cookie::set('uid',$uid);
		Cookie::set('token', $token);
	}
	public function checkCookieToken($uid,$cookieToken=null) {		
		$gentok = $this->genCookieToken($uid);
		if ( $gentok == $cookieToken ) return $uid;
		Cookie::delete('token');
	}
	
	public function genCookieToken($uid) { 
		$usr = $_SERVER['HTTP_USER_AGENT'];
		$gentok = $this->getToken($uid);
		return $this->crypt_function($usr.$this->salt_token.$gentok);
	}
	
	public function getToken($uid) {
		$q = $this->dbLoginPassword->duplicate();
		$q->andWhere(array('uid'=>$uid));
		$q->selectColumn('uid,password');
		if ( $mfa = $q->getAssoc() ) {
			return $this->genToken($mfa['uid'],$mfa['password']);
		}
	}
	
	public function getTmpToken($uid) { 
		$q = $this->dbLoginPassword->duplicate();
		$q->andWhere(array('uid'=>$uid));
		$q->selectColumn('uid,password_tmp');
		if ( $mfa = $q->getAssoc() ) {
			return $this->genToken($mfa['uid'],$mfa['password_tmp']);
		}
	}
	
	public function checkTmpToken($uid,$token=null) {
		$gentok = $this->getTmpToken($uid);
		if ( $gentok == $token ) return $uid;
	}
	
	private function genToken($uid,$pass) {
		// fix: si le mot de passe temporaire est vide le token deviens prédictible
		// alors si le pass est vide le token est aléatoire
		if ( !$pass ) $pass = $this->crypt_function(time().rand(0, 999).rand(0, 999).rand(0, 999));
		return $this->crypt_function($pass.$this->salt_token.$uid);
	}
	
	public function add($uid,$pass,$noCrypt = null) {
		if( !$noCrypt ) {  
			$pass = $this->crypt_function($pass);
		}		
		$data = array('uid'=>$uid,'password'=>$pass);
		return $this->dbLoginPassword->insert($data);
	}
	
	public function setPassword($uid,$password) {	
		$npass = $this->crypt_function($password);
		$req =  $this->dbLoginPassword->duplicate();
		$req->andWhere(array('uid' => $uid));
		$data = array('password' => $npass);
		if ( $req->getColumn() ) {
			return $req->update($data);
		}
		$data['uid'] = $uid;
		return $req->insert($data);
	}
	
	public function switchPasswordTemporaire($uid) {
		$set = array('password'=> 'password_tmp', 'password_tmp'=>'');
		$q = $this->dbLoginPassword->duplicate();
		$q->andWhere(array('uid'=>$uid));
		return $q->update( $set, 'password');
	}

	public function setPasswordTemporaire($uid,$password_tmp) {	
		$npass = $this->crypt_function($password_tmp);
		$req = $this->dbLoginPassword->duplicate();
		$req->andWhere(array('uid' => $uid));
		$data = array('password_tmp' => $npass);
		if ( $req->getColumn() ) {
			return $req->update($data);
		}
		$data['uid'] = $uid;
		return $req->insert($data);	 
	}

	public function checkPasswordTemporaire($uid,$password_tmp) {
		$q = $this->dbLoginPassword->duplicate();
		$q->andWhere(array('uid'=>$uid,'password_tmp'=>$password_tmp));
		$q->selectColumn('uid,password');
		if ( $password_tmp != '' ) {
			return $q->getAssoc();
		}		
	}
}