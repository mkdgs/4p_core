<?php 
namespace Module\Sample;
use Fp\Core\Filter;
use Fp\Core\Date;
use Fp\Core\Invalidate;
use Fp\Core\GeoLatLng;
use Fp\Template\TemplateData;
use Fp\Core\Redirect;
use \Exception;

class Controller_json extends \Fp\Module\Controller_json {
	public function config() { 
		$this->model = new Model($this->O);
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
	
	public function myMethod() {
			return array(); 
	}
}