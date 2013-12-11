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
class HttpHeader { 
	
	
	public function redirect($url) { 
		if ( headers_sent() ) {
			echo "<html><body><script type=\"text/javascript\">\ntop.location.href = '$url';\n</script></html></body>";
			exit();
		}
		else {
			    $this->http_301($url);
				die($url);
		}
	}
	/**
	 * header page not found
	 */
	public function http_404() { 
		header("HTTP/1.0 404 Not Found");
	}	
	public function http_400() { 
		header("HTTP/1.0 400 Bad Request");
	}	
	public function http_401() { 
		header("HTTP/1.1 401 Unauthorized");
	}
	public function http_403() { 
		header("HTTP/1.1 403 Forbidden");
	}	
	
	public function http_500() { 
		header("HTTP/1.1 500 Internal Server Error");
	}
	
	public function http_501() { 
		header("HTTP/1.1 501 Not Implemented");
	}
	
	/**
	 *  redirect See other
	  * @param string $url
	 */
	public function http_303($url) { 
		// 303 See Other
		header("Location: ".$url,TRUE,303);			
	}
	
	/**
	 * redirect to $url
	 * @param string $url
	 */
	public function http_location($url) {
		header("Location: ".$url);
	}
	
	/**
	 * redirect 301 Moved Permanently
	 * @param unknown_type $url
	 */
	public function http_301($url) { 
		header("Location: ".$url,TRUE,301);
	}

   /**
    * redirect 307 Temporary Redirect
    * @param unknown_type $url
    */
   public function http_307($url) { 
		header("Location: ".$url,TRUE,307);
   }

   public function typeJs() { 
   		Header("content-type: application/x-javascript"); 
   }

}