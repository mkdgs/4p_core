<?php 
namespace Module\Sample;
use \Exception;


class Module extends \Fp\Module {

	public function config() {
		$this->setAllRenderMode(array('html','json'));
		$this->setUrl($this->mode);
		$r = $this->getWebPath(__DIR__);
		$this->url_static = $this->O->glob('url').$r.'/1_static';
		$this->model = new Model($this->O);
	}

	public function setUrl($mode=null) {
		parent::setUrl($mode);		
	}

	public function html($id=null, $params=array()) {
		return new Controller_html($this, $id, $params);
	}

	public function json($id=null, $params=array()) {
		return new Controller_json($this, $id, $params);
	}
}