<?php
namespace Fp\Core;

require_once __DIR__.'/../../Lessphp/lessc.inc.php';

class LessCss {
    public $lessphp;
    
    public function __construct() {      
        $this->lessphp = new \lessc();
    } 
    
    public function compile($css_string) {  
        return $this->lessphp->compile($css_string);
    }
}