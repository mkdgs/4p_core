<?php 
namespace Module\Sample;
use \Exception;


class Module extends \Fp\Module {

	public function config() {
		$r = $this->getWebPath(__DIR__);
		$this->url_static = $this->O->glob('url').$r.'/1_static';
		$this->model = new Model($this->O);

		$this->data['url_my_method'] = $this->M->addQueryDelimiter($this->data['url_html']).$this->var_method.'=myMethod';
	}

	/* Set Controller, otherwise it's automatically loaded if exist */
	public function html($id=null, $params=array()) {
		return new Controller_html($this, $id, $params);
	}

	public function json($id=null, $params=array()) {
		return new Controller_json($this, $id, $params);
	}
}