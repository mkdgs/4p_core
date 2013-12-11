<?php
namespace Fp\Core;
class nullObject {	
	public function __construct() { 
		return null;
	}
	
	public function __call($name=null, $data=null) { 
		return null;
	}
	
	public static function __callstatic($name=null, $data=null) { 
		return null;
	}
	
	public function __get($name=null) { 
		return null;
	}
	
	public function __set($name=null, $data=null) { 
		return null;
	}
	
	public function __invoke($name=null, $data=null) { 
		return null;
	}
	
	public function __toString() { 
		return null;
	}
	
	public function __wakeup() { 
		return null;
	}
	
	public function __isset($name=null) { 
		return null;
	}
}