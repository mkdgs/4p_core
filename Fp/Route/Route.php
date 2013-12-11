<?php
namespace Fp\Route;
use Fp\Core\Filter;
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
class Route {
	private $route		= null;
	public $request_uri= null;
	public $server_uri = null;
	private $command    = array();
	public  $rawCommand =  array();
	public  $rawRoute   =  null;
	public  $var_route  = '_ROUTE';

	public function __construct() {
		$this->route 		= '';
		$this->command 		= array();
		$this->server_uri   = $_SERVER['SCRIPT_NAME'];		
		if ( !array_key_exists($this->var_route, $_GET) ) { // backward
			$_GET[$this->var_route] = $this->request_uri	= $_SERVER['REQUEST_URI'];		
		}
		// un curoeux problème sur certain serveur
		// les caractères "url encodé"  \ sont automatiquement décodé, remplacé et doublé \\
		else $this->request_uri	= str_replace('\\\\', '\\', $_GET[$this->var_route]);
		$this->parseRoute($this->request_uri);	
	}

	/**
	 * @deprecated use parseRoute()
	 */
	public function extractparams($params) {
		return $this->parseRoute($params);
	}
	
	public function parseRoute($url) {
	 	$route = array();
		$uri = explode('?',$url);
		$a_uri = explode('/', $uri[0]);
		$a_srv = explode('/', $this->server_uri);
		$c = count($a_srv);

		for ( $i= 0;$i < $c; $i++) {
			if ( isset($a_uri[$i]) && $a_uri[$i] == $a_srv[$i])	{
				unset($a_uri[$i]);
			}
		}
		$this->rawCommand = $command = array_values($a_uri);
		$this->rawRoute   = implode('/', $this->rawCommand);		
		$i=0;	 	
		
		foreach ( $a_uri as $k => $v) {
			$key = $val = null;
			// c'est une variable
			if ( preg_match('#([^-\.]*)(?:-([^+\.]*)){0,1}#', $v, $m) ) {
				$key = $m[1];				
				$val = ( isset($m[2]) ) ? urldecode($m[2]) : null;
			}						
			if ( $key && !is_null($val) ) {				
				$_GET[$key] = $val;				
				if ( !isset($_POST[$key]) ) $_REQUEST[$key] = $val;
			}
			// c'est un chemin
			else {
				// on nettoie un peu
				$this->command[$i++] = preg_replace('#([^a-z0-9_%,\.\\\].*)#i','', urldecode($v));
				
				//on laisse la forme urlencoded
				if ( !empty($v) ) {
					$_GET[$k] = preg_replace('/\..*/', '', $v);
					$route[$k] = preg_replace('/\..*/', '', $v);
				}
			}
			$this->route = implode('/', $route);
		}
	}

	public function setRoute($route) {
		$this->route = $route;
	}

	public function getRawRoute() {
		return $this->rawRoute;
	}
	
	public function getRoute($maxDepth=null)  {		
		if ( !$maxDepth ) return $this->route;
		else {
			$a = explode('/',$this->route);
			if ( count($a) ) $a = array_slice($a, 0, $maxDepth);
			return trim(implode('/',$a), "\t\n\r\0\x0B. /");
		}
	}

	public function getCommand($index=null) {		
		if ( $index !== null ) {			
			if ( $index < 0 ) {		
				$index = count($this->command)+$index;		
			}
			if ( is_array($this->command) && array_key_exists("$index", $this->command) ) {
				return  $this->command["$index"];
			}
			return null;
		}
		return $this->command;
	}

	public function setCommand($index, $value=null) {
		if ( is_array($this->command) && array_key_exists("$index", $this->command) ) {
			return  $this->command["$index"] = $value;
		}
		return $this->command;
	}
	
	public function removeCommand($index=null) {
		if ( $index !== null ) {				
			if ( $index < 0 ) { 
				$index = count($this->command)-$index;
			}
			if ( is_array($this->command) && array_key_exists("$index", $this->command) ) {
				unset($this->command["$index"]);
				$this->command = array_values($this->command); // rebuild key index
			}
			return null;
		}
		return $this->command;
	}
	 
	public function getRawCommand($index=null) {
		if ( $index !== null ) { 
			if ( is_array($this->rawCommand) && array_key_exists("$index", $this->rawCommand) ) {
				return  $this->rawCommand["$index"];
			}
			return null;
		}
		return $this->rawCommand;
	}

	public function rewriteUrl($url, $keyword=null) {
		$url = trim($url, "\t\n\r\0\x0B. /");
		if ( $keyword ) {
			$keyword = Filter::dirName($keyword);
			$keyword = str_replace('_','-',$keyword);
			$keyword = trim($keyword, '-');
			$keyword = preg_replace("#-{2,}#",'-','+'.$keyword);	
		}
		else $keyword ='';
		// on supprime les params incomplet
		$url = preg_replace('#/(?:[a-z0-9_]*)-\{(?:[a-z0-9_]*)\}#', '', $url);
		$url = preg_replace('#/{[a-z0-9_]*\}#', '', $url);
		$url = preg_replace('#/%7B[a-z0-9_]*\%7D#', '', $url);
		$url = preg_replace('#/$#','',$url).$keyword;
		return $url;
	}
	
	/**
	 * retourne l'url du repertoire
	 * 
	 * il faut utiliser __DIR__ et TOUJOURS forurnir un répertoire
	 * 
	 * @param string path of the module on the server with / or it's a filename
	 * @return string return the webPath of module from the root url
	 */
	public function getWebPath($mod_dir) {
		$a_srv = dirname($_SERVER['SCRIPT_FILENAME']);
		$a_uri = explode('/', $mod_dir);
		$a_srv = explode('/', $a_srv);
		$c = count($a_srv);
		$p = array();
		for ( $i= 0;$i < $c; $i++) {
			if ( isset($a_uri[$i]) && $a_uri[$i] === $a_srv[$i])	{
				unset($a_uri[$i]);
			}
			else $p[] = '..';
		}
		$a_uri = array_merge($p, $a_uri);
		$r = '/'.implode('/',$a_uri);
		return $r;
	}
	
	/**
	 * checks if the request is AJAX
	 */
	public function isAJAX() {		 
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		}
	}
	 
	/**
	 * checks if the server request is POST
	 */
	public function isPost() {
		if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
			return true;
		}
	}
	 
	/**
	 * checks if the server request is GET
	 */
	public function isGET() {
		if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
			return true;
		}
	}
}