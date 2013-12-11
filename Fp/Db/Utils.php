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
* @deprecated
*/
class Utils { 
	/**
	 * @param unknown_type $start
	 * @param unknown_type $end
	 * @return string|string|string
	 */
	public static function limit($start=0,$end=null) {
		if ( !is_numeric($start) && !$end ) return '';
		if ( !intval($start) && intval($end) ) return "LIMIT $end";		
		if ( $start && intval($end) ) 	   return "LIMIT $start, $end";	
		if ( $start && !intval($end) ) 	   return "LIMIT $start, 999999";			
	}
	
	/**
	 * si $order est un array la fonction vérifie la présence des clefs dans la liste des colonnes
	 * @param array $order column[ASC | DESC | RAND] tableau dont les clefs sont colonnes associées aux sens de trie
	 * @return string retourne une clause ORDER sql
	 */
	public static function orderBy($order) {
		$o = array();
		if (is_array($order))  {
			foreach ( $order as $k => $v  ) {		
					$k = str_replace('.', '`.`', $k);	
					if 	   ( strtoupper($v) == 'DESC' ) $o[] = " `$k` $v";
					elseif ( strtoupper($v) == 'RAND' )	$o[] = " RAND()";
					elseif ( strtoupper($v) == 'ASC'  ) $o[] = " `$k` $v";				
			}
		}
		if ( count($o) ) return $o = ' ORDER by'.implode(',', $o);
	}
	
	/**
	 * @param unknown_type $string
	 * @return string
	 */
	public static function escape($string) {
		return addslashes($string);
	}
	
	public static function column_name($column) { 
		$c = explode('.',$column);
		$r = array();
		foreach ( $c as $v ) { 
			$r[] = '`'.$v.'`';
		}
		return implode('.',$r);
	}
	/**
	 * 
	 * @param array $order column[ASC | DESC | RAND] tableau dont les clefs sont colonnes associées aux sens de trie 
	 * @return string retourne une clause ORDER sql
	 */
	public static function build_orderBy($order) {		
		$o = array();
		if (is_array($order))  {
			foreach ( $order as $k => $v  ) {				
					if ( strtoupper($v) == 'DESC' ) {
						$o[] = self::column_name($k)." $v";
					}
					elseif ( strtoupper($v) == 'RAND' ) {
						$o[] = " RAND() ";
					}
					elseif ( strtoupper($v) == 'ASC' ) {
						$o[] = self::column_name($k)." $v";
					}			
			}
		}
		if ( count($o) ) { 
			return $o = ' ORDER by'.implode(',',$o);
		}		
	}
	
	public static function build_search_string($column, $value ) { 		
		$value = Filter::DbSafe($value);
		return "$column COLLATE utf8_general_ci LIKE '%$value%' ";	
	}	
}