<?php
namespace Fp\Permission;
use Fp\Core;
use Fp\Core\Filter;
use Fp\Db\Db;
use Fp\Table\Table;
use Fp\Table\Query as Table_query;
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
class Permission extends \Fp\Module\Model {	
	protected $table_Group        	= 'permissions_group';	
	/**
	 * @var Table_query
	 */
	protected $dbGroup;
	protected $table_Zone        	= 'permissions_zone';
	
	/**
	 * @var Table_query
	 */
	protected $dbZone;	
	protected $table_GroupUser 	= 'permissions_group_user';	
	/**
	 * @var Table_query
	 */
	protected $dbGroupUser;	
	protected $table_GroupZone 	= 'permissions_group_zone';
	/**
	 * @var Table_query
	 */
	protected $dbGroupZone;	
	    
    protected $core_group 	= array (0, 1);
    protected $core_zone 	= array (0, 1);	
    protected $prefix       = '';
    
	protected static $level = array('administration'=>1, 'gestion'=>20, 'edition'=>30, 'publication'=>40, 'consultation'=>50, 'ban'=>999);
	protected static $cache = array();
	
	protected function isInCache($key) {
		if ( array_key_exists("$key", self::$cache) ) return true;
	}	
	
	protected function getCache($key) {
		if ( array_key_exists("$key", self::$cache) ) return self::$cache["$key"];
	} 
	
	protected function setCache($key, $value) {
		return self::$cache["$key"] = $value;
	}
	
	protected function clearCache() {
		self::$cache = array();
	}
	
	public function __construct(\Fp\Core\Init $O) {
		$this->O = $O; 
		$this->prefix = $O->glob('prefix');
		
		$dbLink = Db::get_link();
		/*
		 * Groupe
		 */
		$shema  = array('table' => $this->prefix.$this->table_Group,
				'options' => array('auto_increment' => 1),
				'column'=> array(
						'gid'                    => array('type'=>'bigint','primary'=>1,'sortable'=>1,'searchable'=>1),
						'name'                   => array('type'=>'varchar','unique'=>1,'sortable'=>1,'searchable'=>1),
						'timestamp'              => array('type'=>'timestamp'), 
				));
		$this->tableGroup = Table::setTable($dbLink, $shema);
		$this->dbGroup = new Table_query($this->tableGroup,'g');
		
		/*
		 * Zone
		 */
		$shemaZone  = array('table' => 'permissions_zone',
							'options' => array('auto_increment' => 1),
							'column'=> array(
									'zid'                    => array('type'=>'bigint','primary'=>1,'sortable'=>1,'searchable'=>1),
									'name'                   => array('type'=>'varchar','unique'=>1,'sortable'=>1,'searchable'=>1),
									'timestamp'              => array('type'=>'timestamp','sortable'=>1,'searchable'=>1),
						));
		$this->tableZone = Table::setTable($dbLink, $shemaZone);
		
		$this->dbZone = new Table_query($this->tableZone,'z');			
		
		/*
		 * GroupUser
		 */
		$shemaGroupUser  = array('table' => 'permissions_group_user',
				'column'=> array(
						'gid'                    => array('type'=>'bigint','primary'=>1,'sortable'=>1,'searchable'=>1),
						'uid'                    => array('type'=>'bigint','sortable'=>1,'searchable'=>1),
						'permission'             => array('type'=>'int'),
						'timestamp'              => array('type'=>'timestamp','sortable'=>1,'searchable'=>1),
				));
		$this->tableGroupUser = Table::setTable($dbLink, $shemaGroupUser);
		$this->dbGroupUser = new Table_query($this->tableGroupUser,'gu');
		$this->dbGroupUser->innerJoin($this->tableGroup, 'g', 'gu.gid=g.gid ' );
		
		/*
		 * Group Zone
		 */						
		$shemaGroupZone  = array('table' => 'permissions_group_zone',
				'column'=> array(
						'gid'                    => array('type'=>'bigint','primary'=>1,'sortable'=>1,'searchable'=>1),
						'zid'                    => array('type'=>'bigint','sortable'=>1,'searchable'=>1),
						'permission'             => array('type'=>'int','sortable'=>1,'searchable'=>1),
						'timestamp'              => array('type'=>'timestamp','sortable'=>1,'searchable'=>1),
				));
		$this->tableGroupZone = Table::setTable($dbLink, $shemaGroupZone);
		$this->dbGroupZone = new Table_query($this->tableGroupZone,'gz');		
		$this->dbGroupZone->innerJoin($this->tableGroup, 'g', 'gz.gid=g.gid ' );
		$this->dbGroupZone->innerJoin($this->tableZone, 'z', 'gz.zid=z.zid ' );		
	}	
	
	/**
	 * controle l'accès correspondant
	 * 
	 * return true si l'acces est valide
	 * l'utilisateur peut avoir acces si
	 * -> uid est égal a c_uid
	 * -> fait partie d'un groupe égal a c_gid et possede le level requis
	 * -> fait partie d'un groupe qui est membre d'une zone égal à c_zid et possede le level requis
	 * 
	 * @param unknown $c_gids
	 * @param unknown $c_zids
	 * @param unknown $required_level
	 * @param string $uid
	 * @return boolean
	 */
	public function hasAccessLevel($c_gids, $c_zids, $required_level, $uid=null) {
		if ( !$uid ) $uid = $this->O->auth()->uid();
		if ( !$uid ) $uid = 0;	
		$required_level = (int) $required_level;
		
		$access_level = $this->getUserAccessOnGroups($uid, $c_gids);
	    if ( (int) $access_level && $required_level >= (int) $access_level ) {
			return true;
		}
		
		$access_level = $this->getUserAccessOnZones($uid, $c_zids);
		if ( (int) $access_level && $required_level >= (int) $access_level ) {
			return true;
		}
		return false;	
	}

	public function hasAccessAdministration($c_gids=array(), $c_zids=array(), $uid=null) {		
		return  $this->hasAccessLevel($c_gids, $c_zids, $this->levelAdministration() ,$uid);
	}
	public function hasAccessGestion($c_gids=array(), $c_zids=array(), $uid=null) {
		return  $this->hasAccessLevel($c_gids, $c_zids, $this->levelGestion() ,$uid);
	}
	public function hasAccessEdition($c_gids=array(), $c_zids=array(), $uid=null) {
		return  $this->hasAccessLevel($c_gids, $c_zids, $this->levelEdition() ,$uid);
	}
	public function hasAccessPublication($c_gids=array(), $c_zids=array(), $uid=null) {
		return  $this->hasAccessLevel($c_gids, $c_zids, $this->levelPublication() ,$uid);
	}
	public function hasAccessConsultation($c_gids=array(), $c_zids=array(), $uid=null) {
		return  $this->hasAccessLevel($c_gids, $c_zids, $this->levelConsultation() ,$uid);
	}
	public function hasAccessBan($c_gids=array(), $c_zids=array(), $uid=null) {
		return  $this->hasAccessLevel($c_gids, $c_zids, $this->levelBan(),$uid);
	}
	
	
	public function getUserAccessOnGroups($uid, $gids) {		
		if ( !is_array($gids) ) {
			$c_gids = explode(',',$gids);
		}
		else {
			$c_gids = $gids;
		}
		$higher_access = 0;
		
		foreach ( $c_gids as $c_gid ) {
			if( !$c_gid = $this->sanitizeIdGroup($c_gid) ) continue;
			
			$access_level = $this->getUserLevelOnGroup($uid, $c_gid);
			if ( (int) $access_level && $higher_access < (int) $access_level ) {
				$higher_access = $access_level;
			}
		}	
		return $higher_access;
	}
	
	public function getUserAccessOnZones($uid, $zids) {
		if( !is_array($zids) ) {
			$c_zids = explode(',', $zids);
		}	
		else {
			$c_zids = $zids;
		}
		$higher_access = 0;
		foreach ( $c_zids as $c_zid ) {
			if( !$c_zid = $this->sanitizeIdZone($c_zid) ) continue;
			
			$access_level = $this->getUserLevelOnZone($uid, $c_zid);
			if ( (int) $access_level && $higher_access < (int) $access_level ) {
				$higher_access = $access_level;
			}
		}
		return $higher_access;
	}
	
	/**
	 * @param int $uid
	 * @param id $zone
	 */
	public function getUserLevelOnZone($uid, $zone) {
		$key = 'getUserLevelOnZone_'.$uid.'_'.$zone;		
		if ( !$this->isInCache($key)  ) {		
			$zid = $this->sanitizeIdZone($zone);
			$uid = Db::quote($uid);
			$zid = Db::quote($zid);
			$req = "SELECT gz.permission, gz.zid FROM
				$this->table_GroupUser as gu
				INNER JOIN $this->table_GroupZone as gz ON gu.gid=gz.gid
				WHERE gu.uid=$uid AND gz.zid=$zid ORDER BY gz.permission DESC LIMIT 1";
			$r = Db::query($req)->fetchColumn();
			$this->setCache($key, $r);
		}
		return $this->getCache($key);

	}
	
	/**
	 * @param int $uid
	 * @param id $group
	 */
	public function getUserLevelOnGroup($uid, $gid) {
		$key = 'getUserLevelOnGroup_'.$uid.'_'.$gid;
		if ( !$this->isInCache($key)) {
			$req = "SELECT gu.permission FROM
			$this->table_GroupUser as gu
			WHERE gu.uid='$uid' AND gu.gid='$gid' ";
			$r = Db::query($req)->fetchColumn();
			$this->setCache($key, $r);
		}
		else $r = $this->getCache($key);
		return $r;
	}
	
	
	
	/**
	 * @param id $name
	 * @return unknown
	 */
	protected function sanitizeName($name) { 
		$name = Filter::fileName($name);
		if ( $name && !ctype_digit("$name") ) { 
			return $name;
		}
		throw new Exception(' invalide name', 500);
	}
	
	/**
	 * @param int $level
	 */
	protected function sanitizeLevel($level) { 
		if ( ctype_digit("$level") && in_array(intval($level),self::$level) ) { 
			return intval($level);
		}	
		else if ( array_key_exists("$level" , self::$level) ) { 
			return self::$level ["$level"];
		}
		throw new Exception(' invalide level', 500);
	}
	
	
	public static function levelAdministration() { 
		return self::$level['administration'];
	}
	
	public static function levelGestion() { 
		return self::$level['gestion'];
	}
	
	public static function levelPublication() { 
		return self::$level['publication'];
	}
	
	public static function levelEdition() { 
		return self::$level['edition'];
	}
	
	public static function levelConsultation() { 
		return self::$level['consultation'];
	}
	
	public static function levelBan() { 
		return self::$level['ban'];
	}
	
	/*
	 * 		ZONE ACCESS
	 */
	
	
	/**
	 * @param id $group_id
	 * @param id $zone
	 * @deprecated use setGroupInZone
	 */
	public function grantViewInZone($group_id,$zone) {
		 return $this->setGroupInZone($group_id,$zone, Permission::levelConsultation());		
	}
	
	/**
	 * @param id $group_id
	 * @param id $zone
	 * @deprecated use setGroupInZone
	 */
	public function grantEditInZone($group_id,$zone) { 
		return $this->setGroupInZone($group_id,$zone, Permission::levelEdition());
	}
	
	/**
	 * @param id $group_id
	 * @param id $zone
	 * @deprecated use setGroupInZone
	 */
	public function grantPublishInZone($group_id,$zone) { 
		return $this->setGroupInZone($group_id,$zone, Permission::levelPublication());
	}
	
	/**
	 * @param id $group_id
	 * @param id $zone
	 * @deprecated use setGroupInZone
	 */
	public function grantAdminInZone($group_id,$zone) { 
		return $this->setGroupInZone($group_id,$zone, Permission::levelAdministration());
	}
	
	/**
	 * @param id $group_id
	 * @param id $zone
	 * @deprecated use setGroupInZone
	 */
	public function banInZone($group_id,$zone) {
		return $this->setGroupInZone($group_id,$zone, Permission::levelBan());
	}
	

	
	/**
	 * @param id $zone_name
	 * @param id $zid
	 * @return number
	 */	
	public function addZone($zone_name, $zid=null) { 
		$zone_name = $this->sanitizeName($zone_name);
		if ( $zid !== null ) $zid = $this->sanitizeIdZone($zid);
		$data = array('zid' => $zid, 'name' => $zone_name);
		$w = array('zid' => $zid);
		$req = $this->dbZone->duplicate();
		$req->andWhere($w);
		if ( !$req->getColumn() ) {
			return $req->insert($data);
		}
	}
	
	/**
	 * @param id $zone
	 * @throws Exception
	 * @return number
	 */
	public function deleteZone($zone) {
		$zid = $this->sanitizeIdZone($zone);
		if ( isset($this->core_zone[$zid]) ) throw new Exception(__METHOD__.' core Zone');
		$q = $this->dbZone->duplicate();
		$q->andWhere($zid);		
		return $q->delete();
	}
	
	/**
	 * @param id $zone
	 * @param id $zone_name
	 */
	public function renameZone($zone, $zone_name) { 
		$zid = $this->sanitizeIdZone($zone);
		$zone_name = $this->sanitizeName($zone_name);
		if ( isset($this->core_zone[$zid]) ) { 	throw new Exception(__METHOD__.' forbiden ', 403);	}		
		$q = $this->dbZone->duplicate();
		$q->andWhere($zid);	
		return $this->dbZone->update(array('name'=>$zone_name));		
	}
	
	/**
	 * @param id $zid
	 * @return string
	 */
	public function nameZone($zid) { 
		$zid = $this->sanitizeIdZone($zid);
		$q = $this->dbZone->duplicate()
				  ->selectColumn('name');
		$q->andWhere($zid);
		return $q->getColumn();
	}
	
	/**
	 * @param id $zone
	 * @return int || null
	 */
	public function idZone($zone) { 			
		if ( !ctype_digit("$zone") ) {	
			$q = $this->dbZone->duplicate()
				  	  ->selectColumn('zid', false);
			$q->andWhere(array('name'=>"$zone"));	
			return $q->getColumn();
		}
		if ( $this->existZone($zone) ) return $zone;
	}
	
	/**
	 * @param id $zone
	 * @return int
	 */
	public function sanitizeIdZone($zone) { 
		$zone = Filter::id($zone);
		if ( !ctype_digit("$zone") ) {				
			$zone = $this->idZone($zone);
		}
		if ( ctype_digit("$zone") ) {		
			return $zone;
		}		
	}
	
	/**
	 * @param id $zid
	 * @return bool
	 */
	public function existZone($zid) { 			
		if ( !ctype_digit("$zid") ) {			
			 $zid = $this->idZone($zid);
		}
		if ( !ctype_digit("$zid") ) {		
			return null;
		}			
		$q = $this->dbZone->duplicate()
				  	 	  ->selectColumn('zid');
		$q->andWhere($zid);
		if ( $q->getAssoc() ) return true;
	}
	

	
	/**
	 * @param int $uid
	 * @param id $group
	 * @param int $level
	 */
	public function setUserInGroup($uid,$group, $level) { 
		$gid = $this->sanitizeIdGroup($group);		
		$uid = Filter::int($uid);
		if ( $level == 0 ) { 
			return $this->removeUserFromGroup($uid, $gid);
		}
		$w = array(
			'gid'=>$gid,
			'uid'=>$uid			
		);
		$data = array('permission' => $level);
		$req = $this->dbGroupUser->duplicate();
		$req->andWhere($w);
		if ( $req->getColumn() ) {
			return $req->update($data);
		}		
		$data = array_merge($w, $data);
		return $req->insert($data);		
	}
	
	/**
	 * @param int $uid
	 * @param filename $group
	 * @return multitype:
	 */
	public function isUserInGroup($uid, $group) { 	
		$key = 'isUserInGroup_'.$uid.'_'.$group;
		
		if ( !$this->isInCache($key) ) {
			$gid = $this->idGroup($group);
			$uid = Filter::int($uid);
			$q = $this->dbGroupUser->duplicate();
			$q->andWhere(array('uid'=>$uid, 'gid'=>$gid));
			$value = $q->getAll();	
			$this->setCache($key, $value);
		}
		return $this->getCache($key);		
	}
	
	/**
	 * @param uid $uid
	 * @param id $group
	 */
	public function removeUserFromGroup($uid,$group) { 
		if ( $uid == 1 && $group == 1 ) throw new Exception('super admin can\'t be removed from group admin');
		$gid = $this->idGroup($group);
		$uid = Filter::int($uid);
		$q = $this->dbGroupUser->duplicate();
		$q->andWhere(array('uid'=>$uid, 'gid'=>$gid));
		return $q->delete();	
	}
		
	/*
	 *   ______
	 *   GROUPE
	 * 
	 */	

	/**
	 * @param id $group
	 * @param id $zone
	 * @param id $level
	 */
	public function setGroupInZone($group,$zone,$level) { 
		$zid = $this->sanitizeIdZone($zone);
		$gid = $this->sanitizeIdGroup($group);
		$level = $this->sanitizeLevel($level);		
		if ( $level == 0 ) 		return $this->removeGroupFromZone($gid,$zone);
		$w = array(
			'gid'=>$gid,
			'zid'=>$zid			
		);
		$data = array('permission' => $level);
		$req = $this->dbGroupZone->duplicate();
		$req->andWhere($w);
		if ( $req->getColumn() ) {
			return $req->update($data);
		}		
		$data = array_merge($w, $data);
		return $req->insert($data);	
	}
	
	/**
	 * @param id $gid
	 * @param id $zone
	 */
	public function removeGroupFromZone($gid,$zone) { 
		$zid = $this->sanitizeIdZone($zone);
		$gid = $this->sanitizeIdGroup($gid);
		$q = $this->dbGroupZone->duplicate();
		$q->andWhere(array('gid'=> $gid,'zid'=>$zid));
		return $q->delete();
	}
	
			
	/**
	 * @param string $group_name
	 * @param gid $gid
	 * @return gid or null
	 */
	public function addGroup($group_name,$gid=null) { 
		$group_name = $this->sanitizeName($group_name);
		if ( $gid !== null ) $gid = $this->sanitizeIdGroup($gid);
		$data = array('gid' => $gid, 'name' => $group_name);
		$w = array('gid' => $gid);
		$req = $this->dbGroup->duplicate();
		$req->andWhere($w);
		if ( !$req->getColumn() ) {
			if ( $r = $req->insert($data) ) {
				if ( $gid ) return $gid;
				return $r;
			}			
		}
	}
	
	/**
	 * @param id $group
	 * @param id $group_name
	 */
	public function renameGroup($group,$group_name) { 
		$group_name = $this->sanitizeName($group_name);
		$gid = $this->sanitizeIdGroup($group);
		if ( isset($this->core_group[$gid]) ) throw new Exception(__METHOD__.' forbiden ', 403);
		$q = $this->dbGroup->duplicate();
		$q->andWhere($gid);
		return $q->update(array('name'=>$group_name)); 
	}
	
	/**
	 * @param id $group
	 */
	public function deleteGroup($group) {
		$gid = $this->sanitizeIdGroup($group);
		if ( isset($this->core_group[$gid]) ) { throw new Exception(__METHOD__.' forbiden ', 403); }	
		$q = $this->dbGroup->duplicate();
		$q->andWhere($gid);	
		return $q->delete();
	}
	
	/**
	 * @param id $group
	 */
	public function nameGroup($group) { 
		$gid = $this->sanitizeIdGroup($group);
		if ( $gid ) {
			$q = $this->dbGroup->duplicate()
						   ->selectColumn('name');
			$q->andWhere($gid);	
			return $q->getColumn();
		}		
	}
	
	/**
	 * @param id $group
	 */
	public function idGroup($group) { 
		if( !strlen("$group") ) return false;
		if ( !ctype_digit("$group" ) ) {		
			$q = $this->dbGroup->duplicate()
						   	   ->selectColumn('gid');
			$q->andWhere(array('name'=>$group));		
			return $q->getColumn();
		}		
		if ( $this->existGroup($group) ) {			
			return $group;
		}
	}
	
	/**
	 * @param id $group
	 */
	public function existGroup($group) { 	
			if( !strlen("$group") ) return false;
			if ( !ctype_digit("$group") ) {
				$gid = $this->idGroup($group);				
			}
			else {
				$gid = $group;			
			}
			if( !strlen("$gid") ) return false;
			
			$q = $this->dbGroup->duplicate()
						   	   ->selectColumn('gid');
			$q->andWhere($gid);
			if ( $q->getAssoc() ) return true;
	}
	
	/**
	 * @param id $group
	 */
	public function sanitizeIdGroup($group) { 
		$group = Filter::id($group);
		$group = $this->idGroup($group);
		return $group;
	}
}