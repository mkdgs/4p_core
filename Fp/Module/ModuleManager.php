<?php
namespace Fp\Module;
use Fp\Core\Core;


class ModuleManager {
	
	/**
	 * @param Core $O
	 * @param string $module_class
	 * @return \Fp\Module\Module
	 */
	public static function load(Core $O, $module_class,  $defaultMode='html', $defaultUrl=null) {
		
		if ( $module_class && class_exists($module_class) ) {
			if ( is_subclass_of($module_class, __NAMESPACE__.'\Module') ) {
				
				return $module = new $module_class($O, $defaultMode, $defaultUrl);
				/*
				if ( !$mode ) {
					$module->autoMode();
				}
				else {
					$module->setMode($mode);
				}
				if (  $module->getMode() ) {
					var_dump($params);
					$controller = $module->getController($module->getMode());	
					$controller->setRequestId($mod_id)
							   ->setRequestParams($mod_params)
							   ->setMethod($method);
					return $controller;
			
					$controller->config();
					
								->init()
								->render();
					return true;
		
					
				}	*/			
			}
		}		
	}

}