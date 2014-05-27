<?php 
namespace Fp\Login;
use Fp\Table\Table;
use Fp\Table\Query as Table_Query;
use Fp\Db\Db;
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
class Mail extends \Fp\Module\Model {
	public $table = 'login_mail';
	/**
	 * @var Table_query
	 */
	public $dbLoginMail;
	
	public function __construct(\Fp\Core\Init $O) { 		
		$tableLoginMail 	 = $O->glob('prefix').$this->table;
		$columnLoginMail  = array('uid','mail','tmp_mail','status');
		$this->tableLoginMail  = Table::set(Db::get_link(), $tableLoginMail, $columnLoginMail);
		$this->tableLoginMail->setPrimary('uid');
		$this->tableLoginMail->setUnique(array('mail'));
		$this->tableLoginMail->setSortable(array('uid','mail','tmp_mail','status'));
		$this->tableLoginMail->setSearchable(array (
		  'uid' => 'bigint',
		  'tmp_mail' => 'varchar',
		  'status' => 'int',
		));
		$this->tableLoginMail->setAutoIncrement();		
		$this->dbLoginMail = new Table_query($this->tableLoginMail,'LoginMail');
	}
	
	public function getTable() { return $this->table; }	
	
	public function add($uid,$mail,$status=0) { 
		$data = array('uid'=>$uid,'mail'=>$mail,'status'=>$status);
		$w = array('uid'=>$uid);
		$req = $this->dbLoginMail->duplicate();
		$req->andWhere($w);
		if ( !$req->getColumn() ) {
			return $req->insert($data);
		}
	}
	
	public function delete($uid) {
		$q = $this->dbLoginMail->duplicate();
		$q->andWhere(array('uid'=>$uid));
		return $q->delete();
	}
	
	public function getMail($uid) {
		$q = $this->dbLoginMail->duplicate();
		$q->andWhere(array('uid'=>$uid));
		$q->selectColumn('mail');
		return $q->getColumn();
	}
	
	public function getUid($mail) {
		$q = $this->dbLoginMail->duplicate();
		$q->andWhere(array('mail'=>$mail));
		$q->selectColumn('uid');
		return $q->getColumn();
	}
	
	public function setMail($uid,$mail,$status=0) {	
		$req = $this->dbLoginMail->duplicate();
		$req->andWhere(array('uid'=>$uid));
		$data = array('mail'=>$mail,'status'=>$status);
		if ( $req->getColumn() ) {
			return $req->update($data);
		}
		else {
			$data['uid'] = $uid;
			return $req->insert($data);
		}		
	}

	public function switchMailTemporaire($uid, $status=1) {
		$data = array('mail'=>'tmp_mail', 'tmp_mail'=>'','status'=>$status);
		$q = $this->dbLoginMail->duplicate();
		$q->andWhere(array('uid'=>$uid));
		return $q->update($data);
	}

	public function setMailTemporaire($uid,$mail,$status=0) {			
		$data = array('tmp_mail'=>$mail,'status'=>$status);	
		$q = $this->dbLoginMail->duplicate();
		$q->andWhere(array('uid'=>$uid));
		return $this->dbMail->update($data);		
	}
}