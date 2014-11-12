<?php
use \Fp\Log\Logger;
if ( version_compare(PHP_VERSION, '5.3.0', '<') ) {
	die('bad php version' . PHP_VERSION);
}
require_once 'Fp/Core/Init.php';
require_once 'Fp/Core/Core.php';

# paramètre php
#ini_set('arg_separator.output', '&amp;');
// contre le vol de session
ini_set("session.use_trans_sid", 0);

ini_set('default_charset',	$C_glob['charset']);
ini_set('arg_separator.output','&'); 
ini_set('arg_separator.input',';&'); 

// souvent bloqué sur le serveur -> php.ini
ini_set("memory_limit",			$C_glob['memory_limit']);
ini_set('post_max_size', 		$C_glob['max_filesize']);
ini_set('upload_max_filesize', 	$C_glob['max_filesize']);

//set_magic_quotes_runtime(false);
ini_set('magic_quotes_gpc',     'Off');
ini_set('magic_quotes_runtime', 'Off');
ini_set('magic_quotes_sybase', 	'Off');  

// misc 
ini_set('default_charset',		$C_glob['charset']);
ini_set('zend.script_encoding', 	$C_glob['charset']);

// obsolete depuis php 5.6
@ini_set('mbstring.internal_encoding', 	$C_glob['charset']);
@ini_set('mbstring.http_output',	$C_glob['charset']);


ini_set('mbstring.detect_order',        'auto');

date_default_timezone_set($C_glob['lc.timezone']);
setLocale(LC_TIME,        $C_glob['lc.message']);
setlocale(LC_NUMERIC,	  'C');
setlocale(LC_MONETARY,	  $C_glob['lc.monetary']);
setlocale(LC_CTYPE, 	  $C_glob['lc.ctype']);
setlocale(LC_COLLATE, 	  $C_glob['lc.collate']);

//setlocale(LC_ALL, 'fr_FR', 'french');//iso8859-1 ?

// garbage collector
ini_set('session.gc_probability', 0);

// php msg error & warning
ini_set('display_errors', 0);
ini_set('html_errors',0);
if ( !$C_glob['debug'] ) {
	ini_set('error_reporting', 0);
}
elseif ( $C_glob['debug'] == 1 ) {
	ini_set('error_reporting', E_ALL|E_STRICT);
}
else {
        
        ini_set('display_startup_errors', 1);
	ini_set('error_reporting', -1);
	ini_set('display_errors', 1);
	ini_set('html_errors',1);	
}

if ( !function_exists('exception_error_handler') ) {
	function exception_error_handler($errno, $errstr, $errfile, $errline ) {
		try {
			if( class_exists('Logger') ) {
				$log = new \Fp\Log\Logger();
				$context = array(
						'file'=> $errfile,
						'line'=> $errline				
				);
				$log->log($errno, $errstr, $context);
			}
			else {				
				throw new \Exception($errstr, $errno);
			}
		} catch(\Exception $e) {
			throw $e;
		}
	}
}
set_error_handler("exception_error_handler");

// Pour le mode CLI et les var affecté par le client
if ( !isset($_SERVER['HTTPS'])) $_SERVER['HTTPS'] = '';
if ( !isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = '';
if ( !isset($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT'] = '';
if ( !isset($_SERVER['SERVER_ADDR'])) $_SERVER['SERVER_ADDR'] = '';
if ( !isset($_SERVER['REQUEST_METHOD'])) $_SERVER['REQUEST_METHOD'] = '';
if ( !isset($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = '';
if ( !isset($_SERVER['SERVER_NAME'])) $_SERVER['SERVER_NAME'] = '';
if ( !isset($_SERVER['REMOTE_PORT'])) $_SERVER['REMOTE_PORT'] = '';
if ( !isset($_SERVER['REMOTE_ADDR'])) $_SERVER['REMOTE_ADDR'] = '';
