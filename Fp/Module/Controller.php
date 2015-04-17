<?php
namespace Fp\Module;
use \Exception;

abstract class Controller { 
	/**
	 * @var \Fp\Core\Core
	 */
	public $O;
	
	/**
	 * @var \Fp\Core\Init
	 */
	public $core;
	
	/**
	 * @var \Fp\Module\Module
	 */
	public $M;
	
	/**
	 * @var \Fp\Module\Module
	 */
	public $module;
	
	/**
	 * @var \Fp\Module\Model
	 */
	public $model;
	public $method =null;
	public $defaultMethod = 'index';
	public $request_mod_id;
	public $request_mod_params = array();
	public $var_method;
	public $data 		  = array();
	protected $forbidMethod = array('__construct','__call','init','setMethod','render','renderReturn',
					'setDefaultMethod','setMethod','getTemplateBlock','setTemplateBlock',
					'setRequestParams','setRequestId','getRequestParams','getRequestId','extendsRequestParams');
	
	abstract protected function after_construct();
	abstract  protected function config();
	abstract  protected function init();
	abstract  public function render();	
	
	private $init;
	// make sure the module is initialized
	final protected function prepare() {
		if ( !isset($this->init) ) {
			$this->init = true;
			$this->init();
		}
	}
	
	final public function __construct(Module $M) {
		$this->module = $this->M = $M;
		$this->core   = $this->O = $M->O;	
		$this->var_method = $this->M->var_method;		
		$this->model = $M->model;
		$this->data = &$M->data;
		$this->data['request_mod_params'] = &$this->request_mod_params;
		$this->hook('after_construct');
	}
	
	final public function setRequestParams($connect_params=array()) {
		$this->request_mod_params = array_merge($this->request_mod_params, $connect_params);
		foreach ( $this->request_mod_params as $k => $v) {
			$params = array('var_method', 'data','method','defaultMethod');
			if ( in_array($k, $params) ) $this->$k = $v;
		}
		return $this;
	}
	
	final public function setRequestId($id=null) {
		if ( $id !== null) $this->request_mod_id = $id;		
		return $this;
	}
	
	final public function getRequestParams() {
		return $this->request_mod_params;
	}
	
	final public function getRequestId() {
		return $this->request_mod_id;
	}
	
	final public function dataMerge(array $data=array()) {
		$this->data = array_merge($this->data, $data);
                return $this;
	}

	final protected function methodIsAllowed($method) {
		if ( !in_array($method, $this->forbidMethod) ) {
			return true;
		}		
	}
		
	final protected function methodIsCallable($method) {		
		if ( $this->methodIsAllowed($method) ) {			
			// module must only call public methods
			return Utils::externalCallTest($this, $method);
		}			
	}
	
	final public function setDefaultMethod($method) { 
		if ( $this->methodIsCallable($method) ) {
			$this->defaultMethod = $method;
		}
		return $this;
	}
	
	final public function setMethod($method) {		
		if ( empty($method) ) { 
			$this->method = $this->defaultMethod;
		}
		elseif ( $this->methodIsCallable($method) ) {			
		 	$this->method = $method;
		}		
		return $this;
	}	
     
	
	final public function hook($method) {
            if ( method_exists($this,$method) ){
		return $this->{$method}();
	    } 		
	}
        
        /*
        * @TODO ancien systeme de hook à supprimer après vérification de tous les modules
        final public function __call($name, $args =array()) {           
		if ( !method_exists($this,$name) ){
			return false;
		} 
		if ( substr($name, 0, 4) == 'tpl_' ) {
			return $this->$name();
		}
		else if ( substr($name, 0, 6) == 'after_' ) {
			return $this->$name();
		}
		else if ( substr($name, 0, 7) == 'before_' ) {
			return $this->$name();
		}		
		throw new Exception('method not allowed'.$name, 500);
	} 

	final public function joinPointAfter($method) {
		return $this->{'after_'.$method}();
	}
	

	final public function joinPointBefore($method) {
		// make sure the module is initialized
		$this->prepare();
		return $this->{'before_'.$method}();
	}
        */
	
	final public function renderReturn() {
		ob_start();
		$this->render();
		$c = ob_get_contents();
		ob_end_clean();
		return $c;
	}
}