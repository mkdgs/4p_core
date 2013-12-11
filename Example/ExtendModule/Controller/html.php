<?php
namespace Exemple\ExtendedModule;
use My\Module as Base;
use Fp\Core\Invalidate;
use Fp\Template\TemplateData;
use Fp\Core\Filter;
use Fp\Core\Redirect;


class Controller_html extends Base\Controller_html {	
	
	
	public function index() {			
		
		
		$this->O->tpl()->assign($this->O->glob('block_central_1'), __DIR__.'/../template/nosOffres.php', $this->data);
	}


}
