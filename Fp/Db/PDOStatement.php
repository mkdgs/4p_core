<?php
namespace Fp\Db;
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
class PDOStatement extends \PDOStatement {
	public $DBC;

	protected function __construct(PDO $DBC) {
		$this->DBC = $DBC;		
	}
	
	public function fetchAssoc() {
		return $this->fetch(\PDO::FETCH_ASSOC);

	}
	public function fetchObject($class_name = "stdClass" , $ctor_args = array() ) {
		return parent::fetchObject($class_name , $ctor_args);
	}
	
	public function fetchAll($fetch_style = \PDO::FETCH_ASSOC, $col = 0,$ctor_args = array() ) {	
		if ( $fetch_style == \PDO::FETCH_ASSOC ) { 
			// bug 	PDO::FETCH_ASSOC n'accepte pas $col = 0,$ctor_args = array()
			// SQLSTATE[HY000]: General error: Extraneous additional parameters
			return parent::fetchAll($fetch_style);
		}
		else return parent::fetchAll($fetch_style,$col,$ctor_args);
	}
	
	public function fetchAllColumn($column_number = 0) {
		return parent::fetchAll(\PDO::FETCH_COLUMN, $column_number);
	}
	public function fetchNum() {
		return $this->fetch(\PDO::FETCH_NUM);
	}
	/**
	 * Enter description here ...
	 * @return string
	 * @deprecated
	 */
	public function lastInsertId() {
		return $this->DBC->link->lastInsertId();
	}
	public function rowCount() {
		return parent::rowCount();
	}
}