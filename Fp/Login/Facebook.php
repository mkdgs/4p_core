<?php
namespace Fp\Login;
use Fp\Core\Core;
use Fp\Core\Date;
use Fp\Core\Filter;
use Fp\Db\Db;
use Fp\Db\Utils as Db_Utils;
use Fp\Table\Table;
use Fp\Table\Query as Table_Query;
use Fp\Module\Utils as Module_Utils;
use Fp\Template\TemplateData;
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
class Facebook {
	private $table   = 'login_facebook_2';
	
	/**
	 * Enter description here ...
	 * @var Table_query
	 */
	public $dbLoginFacebook;
	
	public function __construct(\Fp\Core\Core $O) {
		$tableLoginFacebook  =  $O->glob('prefix').$this->table;	
		$columnLoginFacebook = array('uid','fb_uid','app_id');
		$this->tableLoginFacebook  = Table::set(Db::get_link(), $tableLoginFacebook, $columnLoginFacebook);
		$this->tableLoginFacebook->setPrimary('uid');
		$this->tableLoginFacebook->setUnique(array('fb_uid','uid'));
		$this->tableLoginFacebook->setSortable(array('uid','fb_uid'));
		$this->tableLoginFacebook->setSearchable(array (
		  'uid' => 'bigint',
		));
		$this->tableLoginFacebook->setAutoIncrement();		
		$this->dbLoginFacebook = new Table_query($this->tableLoginFacebook,'LoginFacebook');	
	}
	
	public function getTable() { return $this->table; }		
	
	public function add($uid,$fb_uid,$app_id) { 
		$data = array('uid' 	=> $uid,
					  'fb_uid'	=> $fb_uid,
					  'app_id'	=> $app_id);
		$w = array('fb_uid'	=> $fb_uid,
				   'app_id'	=> $app_id); 
		
		$req = $this->dbLoginFacebook->duplicate();
		$req->andWhere($w);
		if ( !$req->getColumn() ) {
			return $req->insert($data);
		}		
	}
	
	public function delete($uid,$app_id) {
		$q = $this->dbLoginFacebook->duplicate();
		$q->andWhere(array('uid'=>$uid, 'app_id'	=> $app_id));
		return $q->delete();
	}
	
	public function getUid($fb_uid) {
		$q = $this->dbLoginFacebook->duplicate();
		$q->andWhere(array('fb_uid'=>$fb_uid));
		$q->selectColumn('uid');
		return $q->getColumn();
	}
	
	public function getFbUid($uid, $app_id) {
		$q = $this->dbLoginFacebook->duplicate();
		$q->andWhere(array('uid'=>$uid, 'app_id'=> $app_id));
		$q->selectColumn('fb_uid');
		return $q->getColumn();
	}
	
	public function getApp($fb_uid, $app_id) {
		$q = $this->dbLoginFacebook->duplicate();
		$q->andWhere(array('fb_uid'=>$fb_uid,'app_id'=> $app_id));
		$q->selectColumn('app_id');
		return $q->getColumn();
	}
}