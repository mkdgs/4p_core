<?php 
use \Fp\Core\Core as Core;
use \Fp\Template\TemplateData;
use \Fp\Template\Template as T;
use \Fp\Template\TemplateDataMethod;
/**
 * eclipse auto-completion
 * @property mixed $url_static
 */
die('eclipse auto-completion');
class A_T_Data_Method extends TemplateData {
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public function __call() {}
	
	/**
	 * Enter description here ...
	 * @var A_T_Data_Method
	 */
	public function __get() {}
	
	/**
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function getIf($args=null) {}
	public static function issetIf( $args=null) {}	
	public static function valueIf() {}
	public static function echoIf() {}
	public static function get( $name, $default=null) {}
	public static function getEcho( $name, $default=null) {}
	public static function toArray() {}
	public static function toJson() {}
	public static function e() {}
	public static function es() {}
	public static function count() {}
	public static function name() {}
	public static function data() {}	
	public static function value() {}
	public static function v() {}
	public static function replace() {}
	public static function filter($list) {}
	public static function filterNot($list) {}
	
	/**
	 * @deprecated
	 */
	public static function json_encode() {}
	
	/**
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function url(Core $O, $keyword=null) {}
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function urlRelativ(Core $O, $url) {}
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function r($t) {}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function int($t) {}
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function groupBy( $group_key, $group_function = null) {}	 
	public static function iteratePosition($t) {}	
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function iterate( $nb=null, $offset=null, $rewind=null) {}
	
	
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function jsString() {}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function unescape() {}
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function escape() {}
	

	/**
	 * protège une chaine destiné a être affichée dans une attribut html (ex:title)
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function escapeAttribute() {}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function textile() {}	
	
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function htmlToText() {}	
	
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function extrait($lenght=120) {}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $args
	 * @return A_T_Data_Method
	 */
	public static function is() {}
	
}

class G_completion extends A_T_Data_Method { 	
	public $url; 	
	public function url() {}
	
	public $url_static; 	
	public function url_static() {}	
	
	public $url_static_core; 	
	public function url_static() {}	
	
	public $dir;
	public function dir() {}   
		
	public $dir_data;
	public function dir_data() {} 
	
	public $dir_module;
	public function dir_module() {}
	
	public $dir_tpl;
	public function dir_tpl() {}  
	
	public $dir_lib; 
	public function dir_lib() {} 
	
	public $dir_cache;
	public function dir_cache() {}
	
	public $dir_media;
	public function dir_media() {}
	
	public $dir_module;
	public function dir_module() {}
}

/**
 * @global Core $O
 */
$O = new Core();

/**
 * Enter description here ...
 * @global A_T_Data_Method $A
 */
$A = new A_T_Data_Method('', '');

/**
 * Enter description here ...
 * @global G_completion $G
 */
$G = new G_completion();

/**
 * Enter description here ...
 * @global T $B
 */
$B = new T();
