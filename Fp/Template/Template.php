<?php
namespace Fp\Template;
use Fp\Log\Logger;
use Fp\Core\Core;
use Fp\Core\Debug;
use Fp\Db\Db;

/**
 * Copyright Desgranges Mickael
 * mickael@4publish.com
 *
 * Ce logiciel est un programme informatique servant à la création d'application web.
 *
 * Ce logiciel est régi par la licence CeCILL-B soumise au droit français et
 * respectant les principes de diffusion des logiciels libres. Vous pouvez
 * utiliser, modifier et/ou redistribuer ce programme sous les conditions
 * de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA
 * sur le site "http://www.cecill.info".
 *
 * En contrepartie de l'accessibilité au code source et des droits de copie,
 * de modification et de redistribution accordés par cette licence, il n'est
 * offert aux utilisateurs qu'une garantie limitée.  Pour les mêmes raisons,
 * seule une responsabilité restreinte pèse sur l'auteur du programme,  le
 * titulaire des droits patrimoniaux et les concédants successifs.
 *
 * A cet égard  l'attention de l'utilisateur est attirée sur les risques
 * associés au chargement,  à l'utilisation,  à la modification et/ou au
 * développement et à la reproduction du logiciel par l'utilisateur étant
 * donné sa spécificité de logiciel libre, qui peut le rendre complexe à
 * manipuler et qui le réserve donc à des développeurs et des professionnels
 * avertis possédant  des  connaissances  informatiques approfondies.  Les
 * utilisateurs sont donc invités à charger  et  tester  l'adéquation  du
 * logiciel à leurs besoins dans des conditions permettant d'assurer la
 * sécurité de leurs systèmes et ou de leurs données et, plus généralement,
 * à l'utiliser et l'exploiter dans les mêmes conditions de sécurité.
 *
 * Le fait que vous puissiez accéder à cet en-tête signifie que vous avez
 * pris connaissance de la licence CeCILL-B, et que vous en avez accepté les
 * termes.
 *
 * @package		4_publish
 * @subpackage	core
 * @author		Desgranges Mickael
 * @license		CeciLL-B
 * @link			http://4publish.com
 */
class Template {	
	protected $class_Data 	= 'TemplateData';
	protected $global_data 	= null;
	protected $doctype 		= 'xhtml_strict';
	protected $block 		= array();
	protected $logBlock 	= array();
	protected $debug 		= null;
	protected $noHeader 	= null;
	protected $tpl_dir 		= '';
	protected $header;
	protected $processing = false;
	
	public $html_body_open  = '<body>';
	public $html_body_close = '</body>';

	public function __construct(Core $O) {
		$this->O = $O;
	}
	
	public function processing() { 
		return $this->processing;
	}
	
	protected function getFile($file) {
		if ( is_file($this->tpl_dir.$file) ) return $this->tpl_dir.$file;
		if ( is_file($file) ) return $file;
	}

	/**
	 * @return TemplateHeader
	 */
	public function head() {
		if ( !isset($this->head) ) $this->head = new TemplateHeader($this->O);
		return $this->head;
	}

	private function noHeader() {
		$this->noHeader = true;
		return $this;
	}

	public function doctype($doctype=null) {
		if ( !$doctype ) return $this->doctype;
		switch ($doctype) {
			
			case 'xml':
				$this->doctype = '<?xml version="1.0" encoding="utf-8"?>';
				return $this;
				
			case 'xhtml_strict':
			/*$this->doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '
				.'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\r\n"
				.'<html xmlns="http://www.w3.org/1999/xhtml">'."\r\n";
				return $this;*/

			case 'html5':
				$this->doctype = '<!DOCTYPE html>'."\r\n";
				$this->doctype .= '<html lang="'.$this->O->glob('lang').'">'."\r\n";
				
				// internet explorer 8 fix html5
				if ( !isset($_SERVER['HTTP_USER_AGENT']) ) $_SERVER['HTTP_USER_AGENT'] = '';		
  				preg_match('#MSIE ([0-9]\.[0-9])#',$_SERVER['HTTP_USER_AGENT'],$reg);
  				if( isset($reg[1])) {
    				if ( floatval($reg[1]) < 9 ) {
    					$str = '<!--[if lte IE 8]>'."\r\n";
						$str .= '<script src="'.$this->O->glob('url_static_core').'/html5/html5.js"></script>'."\r\n";
						$str .= '<![endif]-->'."\r\n";
						$str .= '<style>article, aside, figure, footer, header, hgroup, menu, nav, section { display: block; } </style>'."\r\n";
						$this->head()->rawCode($str);
    				}
				}
				return $this;
		}
		throw new Exception('doctype inconnu '.$doctype,500);
	}

	public function setTplDir($dir) {
		$this->tpl_dir = $dir;
		return $this;
	}

	public function setData($array) {
		if ( !isset($this->global_data) ) $this->global_data = array();
		if ( is_array($array) ) $this->global_data = array_merge($array,$this->global_data);
		return $this;
	}

	public function setDebug($debug=null) {
		$this->debug = $debug;		
		return $this;
	}

	public function __call($name, $arg) {
		return $this->blockInclude($name);
	}
	
	/**
	 * vérifie si un block est assigné
	 * @param unknown $block
	 * @return boolean
	 */
	public function isAssigned($block) {
		if ( array_key_exists($block, $this->block) ) {
			return true;
		}
		return false;
	}
		
	protected function getCache($id) {
	   return $this->O->cache()->get($id);
	}
	
	protected function setCache($id, $ttl, $data) {
	   return $this->O->cache()->add($id, $data, $ttl);
	}
	
	protected function setBlockCache($block) {
	    if ( $this->block[$block]['cache_ttl'] ) {      
	        if ( $cache_result = $this->getCache($this->block[$block]['cache_id']) ) {
	            $this->block[$block]['cache_result'] = $cache_result;
	            return $this;
	        }
	    }
	}
	/**
	 * assign un block avec son template et ses données
	 * @param unknown $block
	 * @param unknown $file
	 * @param unknown $data
	 * @param int $cache_ttl
	 * @param string $cache_id
	 * @return \Fp\Template\Template
	 */
	public function assign($block,$file,$data=array(), $cache_ttl=0, $cache_id=null) {
	    $this->assignFile($block, $file, $cache_ttl, $cache_id);		
		$this->assignData($block, $data, false);	
		return $this;
	}
	
	public function hasCache($block) {
	    if ( $this->isAssigned($block) ) {
	        if ( $this->block[$block]['cache_result'] ) return true;
	    }
	}
	
	public function assignFile($block, $file, $cache_ttl=0, $cache_id=null) {
	    $cache_id = $file.'_'.$cache_id;	    
	    $this->block[$block]= array('name'=> $block,'file' => $file, 'data' => null, 'cache_ttl' => $cache_ttl, 'cache_id' => $cache_id, 'cache_result' => null);
	    $this->setBlockCache($block, $cache_id);
	    return $this;
	}
	
	public function assignData($block, $data, $clear_cache=true) {
	     if ( $this->isAssigned($block) ) {
	         if ( $data instanceof TemplateData) $this->block[$block]['data'] = $data;
	         else $this->block[$block]['data'] = new TemplateData($data);
	         if ( $clear_cache ) $this->block[$block]['cache_result'] = null;
	     }
	     return $this;
	}
	
	/**
	 * retire le block de la liste 
	 * @param unknown $block
	 * @return boolean
	 */
	public function deassign($block) {
		if ( array_key_exists($block,$this->block) ) {
			unset($this->block[$block]);
		}
		return false;
	}
		
	/**
	 * insert le block dans le template
	 * @param unknown $name nom du block (qui à été assigné)
	 * @param string $data 
	 */
	public function insert($name, $data=null) {
		if ( $this->isAssigned($name) ) {
			$this->logBlockCore($name);
			$data = ( $data === null ) ? $this->block[$name]['data'] : $data;			
			if ( !$data instanceof TemplateData ) $data = new TemplateData($data);			
			$this->block[$name]['data'] = $data;

			if ($this->debug) echo '<!-- [BLOCK_START:'.$name.':'.htmlentities(substr($this->block[$name]['file'], 0, 300), null, 'UTF-8').'] -->';
			$this->renderBlock($this->block[$name]);
			if ($this->debug) echo '<!-- [BLOCK_END:'.$name.']-->';
			$this->logBlockEnd($name);
		}
	}
	
	/**
	 * insert le block seulement si il est assigné
	 * @param unknown $block
	 * @param string $data
	 */
	public function insertIf($block, $data=null) {
		if ( $this->isAssigned($block) ) $this->insert($block, $data);
	}	
	
	/**
	 * @deprecated
	 */
	public function blockSet($block, $file, $data=array()) {
		return $this->assign($block, $file, $data);
	}
	
	/**
	 * @deprecated
	 */
	public function blockIncludeIf($name) {
		$this->insertIf($name);
	}		
	
	/**
	 * @deprecated
	 */
	public function blockInclude($name, $data=null) {
		$this->insert($name, $data);
	}
		
	
	public function preProcessingBlockFile() {
		// permet d'éxecuter du code avant le rendu (ex choix de layout)
		$B  = $this;
		$O = $this->O;
		foreach ( $this->block as $k => $block ) {				
			$file = $this->block[$k]['file'];		
			$A = $this->block[$k]['data'];
			$G = $this->tpl_Global;			
			try {	
				if( $this->getFile($file.'_hook.php') ) {					
					include $this->getFile($file.'_hook.php');
				}
				
			} catch (\Exception $e) {					
				$this->logError($e);
				/*
				if ( $this->debug ) {
					echo 'error in:'.$file."\r\n";
					echo $e->getMessage();
				} 
				*/
			}	
		}
	}

	public function parse($data, $file, $blockname=null) {
		if ( is_object($data) AND $data instanceof TemplateData ) $A = $data;
		else $A = new TemplateData($data);
		$G = $this->tpl_Global = new TemplateData(self::getGlobal());
		$O = $this->O;
		$processingState = $this->processing;
		$this->processing = 0;		
		//pre processing file
		try {		
		    if( $this->getFile($file.'_hook.php') ) {					
				include $this->getFile($file.'_hook.php');
			}
		} catch (\Exception $e) {			
			$this->logError($e);
			//	echo 'error in:'.$file;
		}		
		$this->processing=1;
		try {
			// hack pour avoir le block dans la console 	
			$i = 0;
			if( !$blockname ) $blockname = basename($file);
			while ( $this->isAssigned($blockname) ) $blockname = $blockname.' '.$i++;
			$this->assign($blockname, $file, $A);
			
			ob_start();		
			$this->get_include_contents($file,$A);		
			$t = ob_get_contents();
			ob_end_clean();
		} catch (\Exception $e) {
			$this->logError($e);
			//	echo 'error in:'.$file;				
		}		
		$this->processing = $processingState;
		return $t;
	}
	
	public function renderXml($master='HTML') {
		try {
			$this->tpl_Global = new TemplateData(self::getGlobal());
			$this->preProcessingBlockFile();		
			$this->processing=1;
			ob_start();
			$this->logBlockCore('HTML');
			$this->renderBlock($this->block[$master]);
			$body = ob_get_contents();
			$this->logBlockEnd('HTML');
			ob_end_clean();
		} catch (\Exception $e) {
			ob_end_clean();
			throw $e;
		}
		
		if ( $this->debug ) {			
			$txt = '';
			$d = array();
			$d['logSql']     = Db::get_logReq();
			$d['globData']   = $this->global_data;
			$d['block'] = array();
			foreach ( $this->block as $k => $block ) {
				$a = array();
				$v = clone $block['data'];
				$v->name		  = $k;

				if ( isset($this->logBlock[$k]) ) {
					$v->duration    = $this->logBlock[$k]['duration'];
					$v->memory      = $this->logBlock[$k]['memory'];
					$v->memory_peak = $this->logBlock[$k]['memory_peak'];
					$v->tplfile     = htmlentities(substr($block['file'], 0, 300), null, 'UTF-8');
				} else {
					$v->duration    = '';
					$v->memory      = '';
					$v->memory_peak = '';
					$v->tplfile = '';
				}
				$d['block'][] = $v;					
			}

			$d['stats'] = Debug::point();

			$file = dirname(__FILE__).'/tpl_console.php';
			$data  = new TemplateData($d);

			$body = '<debug>'.$this->parse($data,$file).'</debug>';			
		}
		echo self::doctype().$body;
	}
		
	public function renderHtml($master='HTML') {
		try {			
			$this->tpl_Global = new TemplateData(self::getGlobal());
			$this->preProcessingBlockFile();				
			if ( !$this->noHeader ) {
			    echo self::doctype();
			    echo "<head>\n";			   
			    $this->head()->make();
			}
			$this->processing=1;			
			ob_start();
			$this->logBlockCore('HTML');			
			$this->renderBlock($this->block[$master]);
			$body = ob_get_contents();
			$this->logBlockEnd('HTML');
			if ( ob_get_contents() ) ob_end_clean();
		} catch (\Exception $e) {		
			if ( ob_get_contents() ) ob_end_clean();
			throw $e;
		}			
		
		if ( !$this->noHeader ) {
		    $this->head()->make(1);		    
		    echo "</head>\n";	
		}
		
		echo $this->html_body_open;
		if ( $this->debug ) {
			$txt = '';
			$d = array();
			$d['stats'] 	 = Debug::point();			
			$d['logSql']     = Db::get_logReq();
			$d['globData']   = $this->global_data;
			$d['block'] = array();
			foreach ( $this->block as $k => $block ) {
				$a = array();
				$v = ( $block['data'] ) ? clone $block['data'] : new TemplateData();
				$v->name		  = $k;

				if ( isset($this->logBlock[$k]) ) {
					$v->duration    = $this->logBlock[$k]['duration'];
					$v->memory      = $this->logBlock[$k]['memory'];
					$v->memory_peak = $this->logBlock[$k]['memory_peak'];
					$v->tplfile     = htmlentities(substr($block['file'], 0, 300), null, 'UTF-8');
				}
				else {
					$v->duration    = '';
					$v->memory      = '';
					$v->memory_peak = '';
					$v->tplfile = '';
				}
				$d['block'][] = $v;					
			}
			
			$d['log']       = Logger::getLog();			
			$file = dirname(__FILE__).'/tpl_console.php';
			$data  = new TemplateData($d);
			try {
			$txt = $this->parse($data,$file);		
			} catch (\Exception $e ) {				
				var_dump($e);		
				die('----');
			}	
			$body .= $txt;
		}
		echo $body;
		$this->head()->makeJs(1);
		echo $this->html_body_close;		
		if ( !$this->noHeader ) {
			echo "\r\n</html>";
		}
	}

	//TODO refactoring setTplDir
	protected function getGlobal() {
		if ( !isset($this->global_data) || !is_array($this->global_data) ) {
			$this->setData($this->O->glob());
			$this->setTplDir($this->O->glob('dir_tpl'));
		}
		return $this->global_data;
	}
	
	protected function renderBlock($block) {	    
	    if ( $this->hasCache($block['name']) ) {
	        echo $block['cache_result'];
	        return ;
	    }
	    ob_start();
	    $this->get_include_contents($block['file'], $block['data']);
	    $out = ob_get_contents();
	    ob_end_clean(); 
	    echo $out;
	    
	    if ( $block['cache_ttl'] ) {
	        $this->setCache($block['cache_id'], $block['cache_ttl'], $out);
	    }
	}

	protected function get_include_contents($file,TemplateData $A) {	
		if ( is_file($this->tpl_dir.$file) ) $file = $this->tpl_dir.$file;
		// fix empeche d'inclure le fichier index racine
		elseif ( $file == 'index.php')	$file = 'index.php --';
		
		if ( !is_file($file) ) { // on consière que c'est un block de code déjà préparé
			echo $file;	
			return;		
		}
		
		$B  = $this;
		$G  = $this->tpl_Global;
		$O  = $this->O;		
		try {
			include $file;
		} catch (\Exception $e) {						
			$this->logError($e);
		}
	}
	
	protected function logError(\Exception $e) {	
		$log = new Logger();	
		$log->notice('exception: '.$e->getMessage(), array('exception' => $e));
		//print_r($e->getMessage());
	}

	protected function logBlockCore($block) {
		if ( $this->debug ) {
			$this->logBlock[$block] = array('block' => $block,
									  'start'  => microtime(true),
									  'duration'    => '',
									  'memory_start' => memory_get_usage(),
									  'memory'		 => '',
									  'memory_peak'  => '');
		}
	}

	protected function logBlockEnd($block) {
		if ($this->debug && array_key_exists($block, $this->logBlock)) {
			$this->logBlock[$block]['duration'] = number_format(microtime(true)-$this->logBlock[$block]['start'], 5, ',', ' ').' sec';
			$this->logBlock[$block]['memory']   = round((memory_get_usage()-$this->logBlock[$block]['memory_start'])/1024,2).' Ko';
			$this->logBlock[$block]['memory_peak']   = round(memory_get_peak_usage()/1024,2).' Ko';
		}
	}
}