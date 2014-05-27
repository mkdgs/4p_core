<?php
namespace Fp\Login;
use Fp\Table\Table;
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
class Pseudo extends \Fp\Module\Model {
	private $table   = 'login_pseudo';
	
	public function __construct(\Fp\Core\Init $O) {  
		$this->table      = $O->glob('prefix').$this->table;
		return $this;
	}
	
	public function getTable() { return $this->table; }	
	
	public function add($uid,$pseudo) { 		
		$req = "INSERT INTO $this->table VALUES (:uid,:pseudo) ";
		$req = Db::prepare($req);
		
		$val = array(':uid'=>$uid,':pseudo'=>$pseudo);
		$req->execute($val);		
		return $req->rowCount();
	}
	
	public function get($uid) {
		$req = "SELECT pseudo FROM $this->table WHERE uid=:uid limit 1";		
		$req = Db::prepare($req);
		$req->execute(array(':uid'=>$uid));
		if (  $mfa = $req->fetchAssoc() ) {
			return $mfa['pseudo'];
		}
		
	}
	
	/**
	 * @param unknown_type $pseudo
	 * @return unknown_type
	 */
	public function getUid($pseudo) {
		$req = "SELECT uid FROM $this->table WHERE pseudo=:pseudo limit 1";		
		$req = Db::prepare($req);
		$req->execute(array(':pseudo'=>$pseudo));
		if (  $mfa = $req->fetchAssoc() ) {
			return $mfa['uid'];
		}
	}

	public function setPseudo($uid,$pseudo) {
		$req = Db::prepare("UPDATE $this->table SET pseudo=:pseudo WHERE uid=:uid ");
		if ( $req ) {
			$req->execute(array(':pseudo'=>$pseudo,':uid'=>$uid));
			return $req->rowCount();
		}
		throw new Exception(__CLASS__.'::'.__METHOD__.'()');
	}
	
	public function delete($uid) {
		$req = Db::prepare("DELETE FROM $this->table WHERE uid=:uid ");
		if ( $req ) {
			$req->execute(array(':uid'=>$uid));
			return $req->rowCount();
		}
		throw new Exception(__CLASS__.'::'.__METHOD__.'()');
	}	
}