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
class Redirect {
	
	/**
	 * Enter description here ...
	 * @param unknown_type $to_b64
	 * @param unknown_type $from
	 * @param unknown_type $code
	 * @deprecated
	 */
	public function base64To($to_b64,$from=null,$code=303) { 
		$to = base64_decode($to_b64);
		self::to($to,$from,$code);
	}
	/**
	 * Enter description here ...
	 * @deprecated
	 */
	public static function to($O, $to,$from=null,$code=303) {		
		if ( !$O instanceof Core ) { //backward < 5.0
			$to = $O;
			$from= $to;
			$code = $from;
		}
		$O->header()->redirect($to);
	}
	/**
	 * Enter description here ...
	 * @deprecated
	 */
	public static function reload($removeParams=null) {
		$url = 'http://'.$_SERVER["HTTP_HOST"].''.$_SERVER["REQUEST_URI"];
		if ( $removeParams )  { 
			$url=preg_replace("#$removeParams#",'',$url);
		}		
		self::to($url);
	}
}