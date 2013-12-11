<?php
namespace Fp\Route;
use Fp\Core\Core;

class Router extends Route {

	protected $directory = array();
	
	protected function makeOptionalNamedCapture($name, $regex) {
		return "/?(?:/?(?P<$name>$regex))?";
	}
	protected function makeNamedCapture($name, $regex) {
		return "(?P<$name>$regex)";
	}
	
	/**
	 * Connect un chemin (url) à une action $callback via la méthode parse
	 * 
	 * $route est l'expression régulière qui déclenchera l'éxécution de $callback
	 * $route peut aussi contenir des paramètres de capture nommé de la façon suivante
	 * :param et :param_opt? (optionnel)
	 * ils seront transmis en temps que second arguments à function $callback sous la forme d'un tableau associatif
	 * 
	 * $namedCapture contient les masques (regex) des paramètres nommés ex:
	 * array(
	 * 		'param'    => '[a-z]+',
	 * 		'param_opt => '[0-9]+'
	 * )
	 * 
	 * $callback est une function à éxécuté si $route correspond à l'url analysée
	 * le premier argument est une instance de Core, le second la liste des correspondants au résultat de $namedCapture:
	 * function(Core $O, $params) { code ... }
	 * 
	 * ex:
	 * ->connect('myurl', '^/foo:id:slug?$',
	 *           array(
	 *	             'id' 	=> '[0-9]+',	
	 *	             'slug' => '[^/]*'),
	 *		 	 function(Core $O, $params) {					
	 *				$module   = ModuleManager::load($O, '\My\Module');
	 *				$controller = $module->autoMode();
	 *				$controller->setRequestParams($params)->config();
	 *				$controller->init()->render();
	 *			 }
	 *	)		
	 * 
	 * @param unknown $route_id identifiant unique
	 * @param unknown $route est l'expression régulière qui déclenchera l'éxécution de $callback
	 * @param array $namedCapture contient les masques (regex) des paramètres nommés
	 * @param unknown $callback est une function à éxécuté si $route correspond à l'url analysée
	 * @throws Exception
	 * @return \Fp\Route\Router
	 */
	public function connect($route_id, $route, array $namedCapture, $callback) {	
		if ( !is_callable($callback) ) throw new Exception('callback is not callable');				
		$compiledRoute = $route;		
		$namedCapture = (array) $namedCapture;
		foreach ( $namedCapture as $name => $regex ) {
			$named = $this->makeOptionalNamedCapture($name, $regex);
			$compiledRoute = str_replace(":$name?", $named, $compiledRoute);
			$named = $this->makeNamedCapture($name, $regex);
			$compiledRoute = str_replace(":$name", $named, $compiledRoute);
		}		
		$this->directory[$route_id] = array(
			'id' 			=> $route_id,
			'compiledRoute' => $compiledRoute,
			'route' 		=> $route,
			'callback'  	=> $callback,
			'namedCapture'	=> $namedCapture		
		);		
		return $this;	
	}
	
	/**
	 * analyse $url et lance le callback associé via la méthode connnect
	 * 
	 * @param Core $O
	 * @param string $url url à analyser /$rawRoute par défaut
	 * @return boolean retourne null en cas d'échec et true en cas de succès
	 */
	public function parse(Core $O, $url=null) {
		if ( !$url ) $url = '/'.$O->router()->getRawRoute();
		foreach ( $this->directory as $r ) {
			$r['compiledRoute'] = rtrim($r['compiledRoute'], '/');
			if ( preg_match('#'.$r['compiledRoute'].'#u', $url, $match) ) {				
				if( is_callable($r['callback']) ) {
					$r['callback']($O, $match, $r, $url);
					return true;
				}
			}
		}
	}

	public function url(Core $O, $url, $params) {		
		foreach ( $params as $k => $v) {
			$url = str_replace(":$k?", $v, $url);
			// ajouter condition pour ne pas écraser var qui ont le même préfix
			$url = str_replace(":$k", $v, $url);
		}	
		// append root 	
		$url = preg_replace('#^\^#', $O->glob('url'), $url);
		// remove optional parameters
		// @todo not remove url get params (?!\?[^=&?]+)
		$url = preg_replace('#/?:[a-z_0-9]+\?#', "", $url);
		return $url;
	}
	
}