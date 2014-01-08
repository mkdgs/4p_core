<?php
namespace Fp\Module;
use Fp\Core\Core;
use Fp\Core\Filter;
use \Exception;
/**
 * Copyright Desgranges Mickael
 * mickael@4publish.com
 *
 * Ce logiciel est un programme informatique servant à la création d'application web. 
 *
 * Ce logiciel est régi par la licence CeCILL-B soumise au droit français e
 * respectant les principes de diffusion des logiciels libres. Vous pouvez
 * utiliser, modifier et/ou redistribuer ce programme sous les conditions
 * de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA 
 * sur le site "http://www.cecill.info".
 *
 * En contrepartie de l'accessibilité au code source et des droits de copie,
 * de modification et de redistribution accordés par cette licence, il n'est
 * offert aux utilisateurs qu'une garantie limitée.  Pour les mêmes raisons,
 * seule une responsabilité restreinte pèse sur l'auteur du programme,  
 * titulaire des droits patrimoniaux et les concédants successifs.
 *
 * A cet égard  l'attention de l'utilisateur est attirée sur les risques
 * associés au chargement,  à l'utilisation,  à la modification et/ou au
 * développement et à la reproduction du logiciel par l'utilisateur étant 
 * donné sa spécificité de logiciel libre, qui peut le rendre complexe à 
 * manipuler et qui le réserve donc à des développeurs et des professionnels
 * avertis possédant  des  connaissances  informatiques approfondies.  Les
 * utilisateurs sont donc invités à charger  et  tester  l'adéquation  d
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
abstract class Module {
	/**
	 * @var Core
	 */
	public $O;
	/**
	 * @var Model
	 */
	public $model;
	public $var_mode        = 'm';	
	public $var_method 		= 'a';
	public $url;

	public $url_static = '';
	public $url_html;	
	public $url_json;
	public $url_xml;
	public $url_raw;
	public $url_media;	
	
	public $data = array();	

	protected $mode;
	protected $render_mode = array('html','json','raw','media','widget','xml');

	
	public function __construct(Core $O, $defaultMode='html', $defaultUrl=null) {
		$this->O = $O;		
		if ( !$defaultUrl ) {
			$this->url = $this->data['url'] = trim($this->O->route()->getRoute(),'/');			
		}
		else {
			$this->url = $this->data['url'] = $defaultUrl;
		}		
		if( !$mode = $this->detectMode() ) {
			$mode = $defaultMode;
		}
		$this->setMode($mode);			
		$this->setUrl($this->mode);
		$this->config();		
		$this->data['url_static'] = $this->url_static;
		// on appel after_config une seul fois  
		// à la fin de la construction de l'objet 
	 	$this->after_config();		 	
	}
	
	public function detectMode() {
		$mode = Filter::id($this->var_mode, $_GET);
		if ( !$mode ) {
			$mode = $this->O->route()->getCommand(-1);
		}
		if ( in_array($mode, $this->render_mode) ) {
			return $mode;
		}
	}
		
	final protected function dataMerge(array $data=array()) {
		$this->data = array_merge($this->data, $data);
	}	
	
	/**
	 * @param string path of the module on the server
	 * @return string return the webPath of module from the root url
	 */
	protected function getWebPath($mod_dir) {
		return $this->O->route()->getWebPath($mod_dir);
	}
		
	public function setUrl($mode=null) {
		$m=0;
		$mode = ( $mode ) ? $mode : $this->mode;
		$sp = ( preg_match("#\?#", $this->url) ) ? '&' : '?';		
		if ( $mode ) {
			// si le mode est présent dans l'url on le remplace
			// @TODO l'utilisation des variables-value est déprécié
			if ( preg_match("#/(?:{$this->var_mode}-)?$mode#", $this->url) ) $m=1;				
			if ( preg_match("#/(?:{$this->var_mode}=)?$mode#", $this->url, $match) ) $m=1;
		}
		foreach ( $this->render_mode as $v ) {			
			if ( $m ) {
				$this->data["url_".$v] = $this->{"url_".$v} = str_replace($mode, $v, $this->url);
			}
			else if ( $v != $mode ) { 
				$this->data["url_".$v] = $this->{"url_".$v} = $this->url.$sp.$this->var_mode.'='.$v;
			}
			else { 
				$this->data["url_".$v] = $this->{"url_".$v} = $this->url;
			}			
		}
		$this->data['url_static'] = $this->url_static;
		return $this;
	}

	/**
	 * @TODO breaking change replace config by on_config
	 * we want sure to return $this on config() call
		protected function config() {
			$this->on_config();
			return $this;
		}
	*/
	abstract protected function config();
	protected function after_config() {}
	
	/**
	 * @param $mod type of controller
	 * @return \Fp\Module\Controller
	 */
	public function getController($mode=null) {	
		if ( $mode ) $this->setMode($mode);	
		if ( !$this->modeExist($this->mode) ) throw new Exception('method not allowed', 400);
	 	return $this->{$this->mode}();
	}	
	
	public function modeExist($mode) {
		if ( in_array($mode, $this->render_mode) ) {
			if ( method_exists($this, $mode)) {
				return true;
			}
		}
	}

	
	public function setAllRenderMode(array $mode=array()) {
		$this->render_mode = $mode;
		return $this;
	}	
	public function getAllRenderMode() {
		return $this->render_mode;
	}
	
	
	public function getRenderMode() {
		return $this->mode;
	}
	public function setRenderMode($mode) {
		$this->setMode($mode);
	}
	public function setMode($mode) {
		if ( $mode ) $this->mode = $mode;
		return $this;
	}
	
	public function getMode() {
		return $this->mode;
	}
	
	private function getControllerClass($c, $controller) {		
		$c = explode('\\', $c);
		array_pop($c);
		$c = implode('\\', $c);
		
		$t = $c.'\\'.$controller;
		if( class_exists($t) ) {
			$class= $t;	
			return $class = new $class($this);
		}
	}
		
	/**
	 * @return \Fp\Module\Controller
	 */
	protected function loadController($controller) {		
		$c = get_class($this);
		
		
		if ( $classController = $this->getControllerClass($c, $controller) ) {		  
			return $classController;
		}
		
		
		// check parent class
		$c1 = get_parent_class($this);
		if ( $classController = $this->getControllerClass($c1, $controller) ) {
			return $classController;
		}
		
		
		throw new Exception(addslashes($c).' and '.addslashes($c1).' has no '.$controller);
	}
	
	/**
	 * @return \Fp\Module\Controller
	 */
	public function autoMode() {
		$m = Filter::id('m', $_GET);		
		if ( $this->modeExist($m) ) {
			$this->setMode($m);
			$m = $this->mode;			
			return $this->$m();
		}
		else return $this->html();
	}		

	public function widget() {
		$t = 'Controller_widget';
		return $this->loadController($t);	
	}
	
	/**
	 * @return \Fp\Module\Controller_html
	 */
	public function html() {
		$t = 'Controller_html';
		return $this->loadController($t);	
	}

	/**
	 * @return \Fp\Module\Controller_xml
	 */
	public function xml() {
		$t = 'Controller_xml';
		return $this->loadController($t);	
	}
	
	/**
	 * @return \Fp\Module\Controller_json
	 */
	public function json() {
		$t = 'Controller_json';
		return $this->loadController($t);	
	}
	
	/**
	 * @param Core $O
	 * @return self
	 */
	final public static function invoke(Core $O) {
		$c = get_called_class();
		return new $c($O);
	}
}