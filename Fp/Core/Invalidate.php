<?php
namespace Fp\Core;
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
class Invalidate {
	private function __construct() {
		die('static class');
	}
	
	/**
	 * return true si la chaine est vide 
	 * @param unknown_type $string
	 * @return boolean|number
	 */
	public static function emptySentence($string) { 
		if ( !preg_match('/[a-z0-9-_\.]/i', (string) $string) ) { 
			return true;
		} 		
	} 
	
	/**
	 * return non null si la valeur n'est pas entre $min et $max
	 * @return 1 trop petit, 2 trop grand 
	 */
	public static function notBetween($val ,$min=null, $max=null) { 
		if ( (int) $val === null ) return 3;
		if ( (int) $val < $min ) return 1;
		if ( (int) $val > $max ) return 2;
	}
	

	/**
	 * @param string $email adresse mail a tester
	 * @param int $max longueur maximal de l'adresse (defaut mysql vachar 255)
	 * @return int 0 mail valide, 1 trop long, 2 caractère invalide, 3 pas de mx 
	 */
	public static function Email($email='',$max=255) {	
		if ( strlen( (string) $email) > $max ) {
			return 1;
		}
		elseif ( !preg_match('/^([a-z])([\w\d.+-]{2,})'.'@'.'([a-z])([\w\d.-]{2,})'.'\.([a-z]{2,5}$)/i',$email) ) {
			return 2;
		}
		else {
			list($name, $domain) = explode('@',$email);
			if ( !checkdnsrr($domain,'MX') ) {
				// No MX record found
				return 3;
			}
		}
		return 0;
	}
	
	/**
	 * vérifie si la chaine remplis les conditions
	 * @param string $string chaine a tester
	 * @param int $min longueur minimum de la chaine  
	 * @param int $max longueur maximum de la chaine  
	 * @param string $Allow rang de caractères autorisés (regex, defaut a-z0-9_-)
	 * @return int 0 pseudo valide, 1 trop court, 2 trop long  
	 */
	public static function Id($string, $min=3, $max=12, $Allow='a-z0-9_-') {
		if ( strlen($string) < $min ) {
			return 1;
		}
		if ( strlen($string) > $max ) {
			return 2;
		}
		if ( preg_match("/([^$Allow])/i",$string) ) {
			return 3;
		}
		return 0;
	}
	
	/**
	 * @param string $pass mot de passe a tester 
	 * @param int $min longueur minimum du mot de passe 
	 * @param int $max longueur maximun du mot de passe  
	 * @return int 0 pass valide, 1 password trop court, 2 password trop long
	 */
	public static function Pass($pass,$min=4,$max=64) {
		if ( !trim($pass) ) { 
			return 1;
		}
		if ( strlen($pass) > $max ) {
			return 1;
		}
		if ( strlen($pass) < $min ) {
			return 2;
		}
		return 0;
	}
}