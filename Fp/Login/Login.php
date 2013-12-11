<?php
namespace Fp\Login; 
use Fp\Core\Cookie;
use Fp\Db\Db;
use Fp\Table\Table;
use Fp\Table\Query;
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
class Login {
	public $table = 'login';

	/**
	 * @var Table_query
	 */
	public $dbLogin;
	/**
	 * Enter description here ...
	 * @var Session
	 */
	private $session = null;
	private $uid     = null;
	private $salt_session_key = 'un autre petit grain de sel__v6.1';
	public  $O;

	protected $data;

	/**
	 * @param string $prefix
	 * @return Auth
	 */
	public function __construct(\Fp\Core\Core $O) {
		$this->O 	= $O;
		$this->data 	= array();
		$this->table    = $O->glob('prefix').$this->table;
		
		$this->session_key = md5($this->salt_session_key);
		$this->session  = $O->session();	
		$dbLink = Db::get_link();
		$tableLogin   = $O->glob('prefix').$this->table;
		$shemaLogin  = array('table' => $tableLogin,
				'options' => array('auto_increment' => 1),
				'column'  => array(
						'uid'                    => array('type'=>'bigint','primary'=>1,'sortable'=>1,'searchable'=>1),
						'time'                   => array('type'=>'datetime','sortable'=>1,'searchable'=>1),
						'status'                 => array('type'=>'int','sortable'=>1,'searchable'=>1),
		));
		$this->tableLogin = Table::setTable($dbLink, $shemaLogin);
		$this->dbLogin = new Query($this->tableLogin,'Login');
	}

	public function getTable() { return $this->table; }

	public function sessionCheck() {
		if ( $r = $this->session->get('loginData') ) {			
			if ( isset($r['uid']) && isset($r['session_key'])  ) {				
				if ( $this->session_key == $r['session_key'] ) {					
					return $this->auth_proceed($r['uid'],$this->session->loginData);
				}				
			}			
			$this->logout();
		}
	}

	public function isAuth() {
		return $this->uid;
	}

	public function uid() {
		return $this->uid;
	}

	public function logout() {
		$this->data 	 = array();
		$this->uid       = null;
		$this->session->remove('loginData');
		Cookie::delete('token');
		Cookie::delete('uid');
	}

	public function check($uid,$data=null, $force=null) {
		$q = $this->dbLogin->duplicate();
		if ( $force ) $q->andWhere(array('uid'=>$uid));
		else $q->andWhere(array('uid'=>$uid, 'status' => 1));
		$q->selectColumn('uid,status,time');
		if (  $mfa = $q->getAssoc() ) {
			if ( is_array($data) ) $data = array_merge($data,$mfa);
			return $this->auth_proceed($mfa['uid'],$mfa);
		}
		// pas de check on délog
		$data['session_key'] 	= null;
		$this->session->remove('loginData');
		$this->uid               = null;
	}

	private function auth_proceed($uid,$data) {
		$data['session_key'] 	= $this->session_key;
		$this->session->set('loginData',$data);
		$this->uid               = $uid;
		return $uid;
	}

	public function getList($start,$end) {
		$q = $this->dbLogin->duplicate();
		$q->andWhere(true);
		$q->limitSelect($start,$end);
		return $q->getAll();
	}

	public function add($status=0,$uid=null) {
		$data = array('uid' => $uid, 'time'=> 'NOW()', 'status' => $status);
		if ( $uid = $this->dbLogin->insert($data, 'time') ) {
			if ( $uid ) return $uid;
			return $uid;
		}
	}

	private function getUid($uid) {
		if ( !$uid ) $uid =  $this->uid();
		return $uid;
	}
	public function get($uid) {	
		$q = $this->dbLogin->duplicate();
		$q->andWhere(array('uid'=>$uid));
		return $q->getAssoc();	
	}

	/**
	 * Enter description here ...
	 * @param uid $uid if null session uid is set
	 * @return mixed
	 */
	public function isActive($uid=null) {
		$uid = $this->getUid($uid);
		if ( $uid = $this->get($uid) ) {
			return $uid['status'];
		}
	}

	public function active($uid) {		
		$q = $this->dbLogin->duplicate();
		$q->andWhere(array('uid'=>$uid));
		$r = $q->update(array('status'=>'1'));
		if ( $uid == $this->uid() ) {
			$this->session->setInArray('loginData','status',$r);
		}
		return $r;	
	}

	public function desactive($uid) {
		if ( $uid == 1 ) throw new Exception('Super admin can\'t be desactived');
		$q = $this->dbLogin->duplicate();
		$q->andWhere(array('uid'=>$uid));
		$r = $q->update(array('status'=>'0'));
		if ( $uid == $this->uid() ) {
			$this->session->setInArray('loginData','status',!$r);
		}
		return $r;			
	}

	public function delete($uid) {
		if ( $uid == 1 ) throw new Exception('Super admin can\'t be deleted');		
		$q = $this->dbLogin->duplicate();
		$q->andWhere(array('uid'=>$uid));
		return $q->delete();
	}
}