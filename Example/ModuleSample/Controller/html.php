<?php
namespace Module\Sample;
use Fp\Core\Filter;
use Fp\Core\Date;
use Fp\Core\Invalidate;
use Fp\Core\GeoLatLng;
use Fp\Template\TemplateData;
use Fp\Core\Redirect;
use \Exception;

class Controller_html extends \Fp\Module\Controller_html {

	public function config() {
		$this->T_block 		= $this->O->glob('block_central_1');
		$this->tpl_dir 		= dirname(__FILE__).'/../template/';
		$params				= $this->getRequestParams();
		$this->data['var']            = Filter::Int($this->M->var_id_node , $this->getRequestId());


	}	
	
	private function checkPublishPermission() {
		if ( 0 ) {
			throw new Exception('Unauthorized', 401);
		}
	}
	
	private function checkEditPermission($id_node) {	
		if ( 0 ) {		
			throw new Exception('Unauthorized', 401);
		}
	}
	
	public function index() {	    
	    $this->data['test'] = 'index';	    
	    $this->O->tpl()->assign($this->T_block, $this->tpl_dir.'my_template.php', $this->data);
	}
	
	public function myMethod() {	    
	    $this->data['test'] = 'myMethod';
	    $this->O->tpl()->assign($this->T_block, $this->tpl_dir.'my_template.php', $this->data);
	}
		
}
