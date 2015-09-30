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
class Cookie {
	private static $domain  = null;

	public static function set($name,$value, $expire=null,$path='/',$domain=null,$secure=false,$httponly=true) {
		$expire   = ($expire)   ? $expire   : time() + ( 24*3600 *30);
		$domain   = ($domain)   ? $domain   : self::domain();
		$secure   = ($secure)   ? $secure   : false;
		$httponly = ($httponly) ? $httponly : true;
		$path  	  = ($path)     ? $path     : '/';		
		if ( !headers_sent() ) {
			setcookie($name,$value, $expire,$path,$domain,$secure,$httponly);
		}
		else { 
			$e = __METHOD__.' header already sent, cookie "'.$name.'" can\'t be sent:';
			Debug::report($e);
		}
	}
	
	public static function domain($dom=null) { 		
		if ( $dom ) {
			self::$domain = $dom;
		}
		elseif ( !empty(self::$dom) ) { 
			return self::$domain;
		} 
		return $_SERVER["SERVER_NAME"];
	}

	public static function delete($name=null) {	
		
		$expire = time() - (36000);
		if ( $name ) {
			self::set($name,'',$expire);
		}
		else {
			foreach ( $_COOKIE as $key => $val ) {
				self::set($key,'',$expire);
			}
		}	
	}
}