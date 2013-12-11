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
	
	static public function T_dump($var,$inc=0) {
		$T_dump = '';
		if ( is_scalar($var) ) {	
			return htmlentities(substr($var,0,250),ENT_COMPAT,'UTF-8');
		}
		elseif ( !($var instanceof TemplateData)  ) {
			return 'error !?? '.print_r($var,true);
		}
		elseif ( is_scalar($var->v()) ) {	
			return $var->v();
		}		
		elseif ( is_object($var->v()) && !($var->v() instanceof TemplateData) ) { 
			return '(object '.get_class($var->v()).')';
		}		
		$T_dump .= '<ul>';
		foreach ( $var as $k => $v ) {
			$T_dump .= '<li>';	
			$T_dump .= self::name_div($k);
			$T_dump .= self::data_div(self::T_dump($v));
			$T_dump .= '</li>';
		}
		$T_dump .= '</ul>';
		return $T_dump;
	}

	static public function V_dump($var) {
		if ( is_scalar($var)  ) {
			return htmlentities(substr($var,0,300),ENT_COMPAT,'UTF-8')." \r\n";
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