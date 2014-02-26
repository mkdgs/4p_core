<?php
namespace Fp\Core;
use Fp\Db\Db;
use \Exception;
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
class Debug {
	private static $TABLE = 'debug';
	private static $DEBUG = null;
	private static $start_time = null;
	private static $last_time = null;
	private static $id = null;
	private static $workAround = null;
	private static $switch = null;
	/**
	  * @var Core
	  */
	private static $O;

	public static $noSql = null;


	private static function switch_on() {
		if ( !self::$switch ) {
			self::$switch = 1;
			//set_exception_handler(array('Debug','ExceptionHandler'));
			//set_error_handler(array('Debug','ErrorHandler'));
		}
	}
	public static function switch_off() {
		if ( self::$switch ) {
			self::$switch = 0;
			//restore_exception_handler();
			//restore_error_handler();
		}
	}

	public static function set(Core $O) {
		
		self::$O = $O;
		self::$TABLE = $O->glob('prefix').'debug';
		self::$DEBUG = $O->glob('debug');
		self::$start_time = microtime(true);
		self::switch_on();
	}

	public static function point() {
		$time = microtime(true);
		$r = array('last_time'=>0,'total_time'=>0,'memory'=>0,'memory_peak'=>0);
		if ( self::$last_time ) {
			$r['last_time'] =  number_format($time-self::$last_time, 5, ',', ' '). ' sec';
		}
		self::$last_time = $time;
		$r['total_time'] = number_format($time-self::$start_time, 5, ',', ' '). ' sec';
		$r['memory'] = round(memory_get_usage()/1024,2).' Ko';
		$r['memory_peak'] = round(memory_get_peak_usage(true)/1024,2).' Ko';
		return $r;
	}

	public static function get_id() {
		if ( self::$id  == null ) {
			self::$id = time().rand(100,999);
		}
		return self::$id;
	}

	public static function get_uid() {
		$uid = '0';
		try {			
			$uid = self::$O->auth()->uid();
		} catch (\Exception $e) {

		}
		return $uid;
	}

	public static function make_data($msg, $backtrace, $errno, $errfile, $errline, $code) {		
		
		try {
			if ( !$backtrace ) $backtrace = print_r(debug_backtrace(),true);
			
			$d = array();
			$d[':uid']			= self::get_uid();
			$d[':pid']          = self::sanit(self::get_id());
			$d[':errno']        = self::sanit($errno);
			$d[':msg']          = self::sanit($msg);
			$d[':backtrace']    = self::sanit($backtrace);
			$d[':file']         = self::sanit($errfile);
			$d[':line']         = self::sanit($errline);
			$d[':referer']      = ( isset($_SERVER['HTTP_REFERER'])     ) ? self::sanit($_SERVER['HTTP_REFERER'])   : "''";
			$d[':user_agent']   = ( isset($_SERVER['HTTP_USER_AGENT'])  ) ? self::sanit($_SERVER['HTTP_USER_AGENT']): "''";
			$d[':ip']     	    = ( isset($_SERVER['REMOTE_ADDR']) ) ? self::sanit($_SERVER['REMOTE_ADDR']) : "''";
			$d[':request_uri']  = ( isset($_SERVER['REQUEST_URI']) ) ? self::sanit($_SERVER['REQUEST_URI']) : "''";
			$d[':query_string'] = ( isset($_SERVER['QUERY_STRING'])) ? self::sanit($_SERVER['QUERY_STRING']): "''";

			if ( self::$DEBUG >= 3 ) {					
				// on cache les erreurs http banale
				if ( !ctype_digit($code) || ( $code <= 300 && $code >= 499 ) ) {					
					echo $code.': '.$d[':msg']."<br>\r\n <b>".$d[':file'].' </b>  line '.$d[':line']." <br>\r\n";	
					echo "<pre>". print_r($d[':backtrace'] ,true)."</pre> <br>\r\n";
				}		
		    }	
			if ( !self::$noSql ) {				
				if ( Db::isConnected() ) { 					
					$req = 'INSERT INTO '.self::$TABLE." 	
						(uid,pid,errno,msg, 
						backtrace,file, 
						line, date, 	
						referer, user_agent, 	
						ip,request_uri,	query_string)
					VALUES(:uid,:pid,:errno,:msg,:backtrace,:file,:line,NOW(),:referer,:user_agent,:ip,:request_uri,:query_string)";
					$req = Db::prepare($req)->execute($d);
				}
			}
		} catch (\Exception $e ) {
			throw new Exception('Debug Error :'.$e->getMessage(), 666);
		}
	}

	public static function sanit($data) {
		return stripslashes(substr($data,0,1000));
	}
	
	public static function msg($msg, $backtrace=false, $errno=false, $errfile=false, $errline=false, $code=0) {				
		$d = self::make_data($msg,$backtrace, $errno, $errfile, $errline, $code);
	}

	public static function setWorkAround($msg,$callback,$params=array()) {
		if ( self::$workAround == null ) self::$workAround = array(array($msg,$callback,$params));
		else self::$workAround[] = array($msg,$callback,$params);
	}
	
	public static function getWorkAround($errstr) {
		if ( is_array(self::$workAround) ) {
			foreach ( self::$workAround as $v ) {
				if ( preg_match('/'.$v[0].'/i',$errstr) ) {
					call_user_func_array($v[1],$v[2]);
					self::msg('Debug::getWorkAround',print_r($v,true));
				}
			}
		}
	}

	static public function report($e) {
		if ( self::$DEBUG ) {
			if ( is_object($e) ) self::ExceptionHandler($e);
			elseif ( is_array($e)  ) {
				self::switch_off();
				self::msg('report',print_r($e,true));
				self::switch_on();				
			}
			else {
				self::switch_off();
				self::msg($e);				
				self::switch_on();
			}
		}
	}

	static function ErrorHandler($errno=null, $errstr=null, $errfile=null, $errline=null) {
			if ( self::$DEBUG ) {
				self::switch_off();				
				$backtrace = print_r(debug_backtrace(),true);
				if ( $errno == E_USER_ERROR || $errno == E_ERROR || $errno == E_CORE_ERROR ) {
					echo "<br/><b>FATAL</b> [$errno] $errstr<br>\n";
					echo "Fatal error in line " . $errline . " of file ". $errfile;
					echo "Aborting...<br>\n";
					self::msg($errstr, $backtrace, $errno, $errfile, $errline);
					exit(1);
				}
				self::msg($errstr,$backtrace, $errno, $errfile, $errline);
				self::switch_on();
			}
	}
	static public function ExceptionHandler($e) {
		if ( self::$DEBUG ) {				
				self::switch_off();
				$errstr    = $e->getMessage();
				$backtrace = $e->getTraceAsString();
				$errno     = 'Exeption';
				$errfile   = $e->getFile();
				$errline   = $e->getLine();
				$code 	   = $e->getCode();
				self::getWorkAround($errstr);
				self::msg($errstr, $backtrace, $errno, $errfile, $errline, $code);
				self::switch_on();
		}	
	}
}