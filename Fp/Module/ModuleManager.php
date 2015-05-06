<?php
namespace Fp\Module;
use Fp\Core;


class ModuleManager {
	
	/**
	 * @param \Fp\Core\Init $O
	 * @param string $module_class
	 * @return \Fp\Module\Module
	 */
	public static function load(\Fp\Core\Init $O, $module_class,  $defaultMode='html', $defaultUrl=null) {		
		if ( $module_class && class_exists($module_class) ) {
			if ( is_subclass_of($module_class, __NAMESPACE__.'\Module') ) {	                            
				return $module = $module_class::getInstance($O, $defaultMode, $defaultUrl);						
			}
		}		
	}

}