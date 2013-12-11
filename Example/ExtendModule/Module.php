<?php
namespace Exemple\ExtendedModule;

use My\Module as Base;
use Fp\Core\Filter;

class Module extends Base\Module implements PlugInterface {
	
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
