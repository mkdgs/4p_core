<?php 
namespace Fp\Template;
use \Fp\Template\TemplateData;

class TemplateConsole {

	static public function data_div($str) {
		return '<pre class="console-data">'.$str.'<br style="clear:both;"/></pre>';
	}
	
	static public function name_div($str) {
		$t = '<span class="console-label">'.$str.'</span>';
		return $t;
	}
	
	static public function T_dump($var, $inc=0) {	
		if ( is_scalar($var) || $var === null ) {	
                    return htmlentities(substr($var,0,250),ENT_COMPAT,'UTF-8');
		}
		else if ( $var instanceof TemplateData ) {	                    
                    return  self::name_div($var->key).self::data_div(self::T_dump($var->vars));                                              
		}
                else if ( is_array($var) ) {		
                    $T_dump = '<ul>';
                    foreach ( $var as $k => $v ) {
                            $T_dump .= '<li>';	
                            $T_dump .= self::T_dump($v);
                            $T_dump .= '</li>';
                    }
                    $T_dump .= '</ul>';
                    return $T_dump;
                }
                return print_r($var, true);            
	}

	static public function V_dump($var) {
		if ( is_scalar($var) || $var === null ) {
			return htmlentities(substr($var,0,300), ENT_COMPAT, 'UTF-8')." \r\n";
		}
		$T_dump = '';
		foreach ($var as $k => $v ) {
			$T_dump .= '<div><span class="console-label">'.$k.'</span> <pre class="console-data">'.print_r($v, true)."</pre> <br style=\"clear:both;\" /></div>\r\n";
		}
		return $T_dump;
	}	
	
	static public function T_dumpSql($var) {
		$T_dump = '<div>';
		foreach ( $var as $k => $v ) {
			$T_dump .= '<div style="float:left;" ><b> '.$k.'</b> -> ';
			$T_dump .= ' <b>'.$v['duration'].' / '.$v['memory'].' / '.$v['memory_peak'].' </b> : </div>';
			$T_dump .= '<pre style="float:left;" >'.$v['sql'].'</pre><br style="clear:both;" />';
		}
		$T_dump .= '</div>';
		return $T_dump;
	}
	
	static public function T_dumpLog($var) {
		$T_dump = '<div>';
		foreach ( $var as $k => $v ) {
			$T_dump .= $v;		
		}
		$T_dump .= '</div>';
		return $T_dump;
	}
}