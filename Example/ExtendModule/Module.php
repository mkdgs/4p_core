<?php
namespace Exemple\ExtendedModule;

use My\Module as Base;
use Fp\Core\Filter;

class Module extends Base\Module implements PlugInterface {
    
    public function config() {
        
        // ovverride static dir 
        $r = $this->getWebPath(__DIR__);
        $this->url_static = $this->O->glob('url').$r.'/1_static';
        
        // override Model 
        // Module try to load Model automatically if he's not defined
        // $this->model = new Model($this->O);
    
        
        
        // $this->data is copied in all controller of this module
        // you can (re)define default value for your module here
        
        // define all url here is a good thing
        // set the correct url ( with ? or & )
        // $url = $this->addQueryDelimiter($url_r).$this->var_method;
        // $this->data['url_is_great'] = $url.'youpi=yeah'
        
        
    }
    
    // override and add method here ...
    
    
    // Plug support
	public function plug($mod_id=null, $params=null) {
		return 0;
	}
	
	public function unplug($mod_id=null, $params=null) {
		return 0;
	}
	public function plugChange($mod_id=null, $params=null) {
		return 0;
	}
	public function afterPlugChange($node) {
		return 0;
	}
	public function afterUnplug($node) {
		return 0;
	}
	public function afterPlug($node) {
		return 0;
	}
	
	public function searchPlug($search) {
		return array( 'list'=> array( array('label'=>'exemple', 'id'=>0)));
	}
}
