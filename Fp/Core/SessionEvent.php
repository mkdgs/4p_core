<?php
namespace Fp\Core;
class SessionEvent {
	private static $i;
	public $bindEv;
	/**
	 * Enter description here ...
	 * @var core
	 */
	public $O;
	
	
	private function __construct(Core $O) {
		$this->O = $O;
		// liste d'action repoussées
		$this->bindEv = (array) $this->O->session()->get('sessionEvent');
	}
		
	static public function load(Core $O) {
		if ( !isset(self::$i) ) {			
			$c = __CLASS__;
			self::$i = new $c($O);			
		}
		return self::$i;
	}
	
	public static function getInstance(Core $O)  {
		return self::load($O);
		
	}
	
	public function listen() { 		
		foreach ( (array) $this->bindEv as $on => $class ) {	

				if ( !count($this->bindEv[$on]) ) {
					unset($this->bindEv[$on]);	
					$this->change();				
					continue;
				}
				
				$this->O->event()->bind($on, function($ev) {
					$e = $ev['bind'][0];
					$O = clone $e->O;				
				
					foreach ( (array) $e->bindEv[$ev['bind'][1]] as $k => $action ) {
							try { 					
								if ( !class_exists($action['class']) ) return;							
								$class = new $action['class']($O);
								$class = $class->json();										
								$class->rpc_data   = Filter::array_($action);
								$class->rpc_method = Filter::id('method', $class->rpc_data);
								$class->rpc_id     = Filter::id('id', $class->rpc_data);
								$class->rpc_params = Filter::array_('params',  $class->rpc_data);									
								$r = json_decode($class->renderReturn(), 1);
				
							} catch (\Exception $e) {
								Debug::report($e);
							}
							unset($e->bindEv[$ev['bind'][1]][$k]);
							$e->change();
					}
					
				}, array($this, $on));				
		}
	}
	
	public function bind($event, $class, $method, $params) {
		if ( !array_key_exists($event, $this->bindEv) ) $this->bindEv[$event] = array();
		$this->bindEv[$event][] = array('class' => $class, 'method'=> $method, 'params' => $params);		
		$this->change();
	}
	
	
	public function unbind($event) { 
		unset($this->bindEv[$event]);
		$this->O->event()->unbind($event);	
		$this->change();
	}
	
	public function change() {
		$this->O->session()->set('sessionEvent', $this->bindEv);
	}	
}