<?php
namespace Fp\Core;
use \Exception;
use \Memcache;
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
class Cache {
	private static $instance  = null;
	private static $cacheType = null;
	private static $cacheDir  = null;
	private static $nocache;

	private function __construct() {}
	
	public static function getInstance() { 
		return self::$instance;
	}
	
	/**
	 * @return Cache 
	 */
	public static function load(\Fp\Core\Init $O,$cacheType=null) {
		if (self::$instance === null ) {
			$c = __CLASS__;
			self::$instance = new $c;
			self::$cacheType = $cacheType;
			if ( self::$cacheType == 'memcache' ) {
				self::$memcache = new Memcache;
				if ( !self::$memcache->connect('127.0.0.1', 11211) ) {
					throw new Exception(__METHOD__.' memcache fails' , 500);
				}
				self::$memcache->setCompressThreshold(20000, 0.2);
			}
			else {
				self::$cacheDir = $O->glob('dir_cache').'pool/';				
			}
			
		}
		self::$nocache = $O->glob('cache');
		return self::$instance;
	}

	private static function keyName($key) {
		return Filter::dirName($key);
	}

	public function add($key,$data,$ttl=6000,$flags=false) {
		if ( self::$cacheType == 'memcache' ) {
			@self::$mcache->add($key, $data , $flags, $ttl );
		}
		else {
			$expire = time()+$ttl;
			$d = array(
				'expire' => $expire,
				'data'   => $data
			);
			$data = $this->serialize($d);
			$file = self::$cacheDir.self::keyName($key).".cache.v3";
			if ($f = @fopen($file, 'w')) {
				$i = 0;
				while ( !flock($f, LOCK_EX | LOCK_NB) ) {
					$i++;
					if ( $i > 100 ) {
						throw new Exception(__METHOD__.' file locked, aborting ', 500);
					}
					usleep(100);
				}
				fwrite($f, $data);
				flock($f, LOCK_UN);
				fclose($f);				
			}
		}
	}


	private function serialize($data) {	  
	    return json_encode($data);
	   //return base64_encode(serialize($data));
	}
	
	private function unserialize($data) {
	    return json_decode($data, true);
	   //return unserialize(base64_decode($d));
	}
	
	
	public function delete($key) {
		if ( self::$cacheType == 'memcache' ) {
			@self::$mcache->add($key, $data , $flags, $ttl );
		}
		else {
			$file = self::$cacheDir.self::keyName($key).".cache.v3";
			if ( is_file($file) ) { 				
				@unlink($file);
			}
		}
	}

	public function get($key,$flags=false) {
	    
	    // if caching is disabled
	    if ( self::$nocache ) return;
	    
 		if ( self::$cacheType == 'memcache' ) {
			if ( $var = self::$mcache->get($key, $flags) ) {
				return $var;
			}
		}
		else {		
		    //clearstatcache();
			$file = self::$cacheDir.self::keyName($key).".cache.v3";
			if ( is_readable($file) ) {
				if ( !$f = @fopen($file, 'r') ) { 
					return false;
				}
				$i = 0;
				while ( !flock($f, LOCK_SH | LOCK_NB) ) {
					$i++;
					if ( $i > 100 ) throw new Exception(__METHOD__.' file locked, aborting ', 500);				
					usleep(500);
				}				
				try {
					$s = filesize($file);
					$d = '';
					while(!feof($f)) {
						$d .= fread($f, $s);
					}
					flock($f, LOCK_UN);
					fclose($f);		
					$d = $this->unserialize($d);
					if ( is_array($d) ) {
						if ( array_key_exists('expire', $d) && array_key_exists('data', $d) )  {
							if ( $d['expire'] >= time() ) {
								return $d['data'];
							}
						}
					}
				} catch ( \Exception $e ) {
					//throw $e;
				}
				if ( is_file($file) ) @unlink($file);
			}
		}
	}
}