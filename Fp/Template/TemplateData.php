<?php
namespace Fp\Template;
use Fp\Log\Logger;
use \Exception;

/**
 * Copyright Desgranges Mickael
 * mickael@4publish.com
 *
 * Ce logiciel est un programme informatique servant à la création d'application web.
 *
 * Ce logiciel est régi par la licence CeCILL-B soumise au droit français e
 * respectant les principes de diffusion des logiciels libres. Vous pouvez
 * utiliser, modifier et/ou redistribuer ce programme sous les conditions
 * de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA
 * sur le site "http://www.cecill.info".
 *
 * En contrepartie de l'accessibilité au code source et des droits de copie,
 * de modification et de redistribution accordés par cette licence, il n'est
 * offert aux utilisateurs qu'une garantie limitée.  Pour les mêmes raisons,
 * seule une responsabilité restreinte pèse sur l'auteur du programme,
 * titulaire des droits patrimoniaux et les concédants successifs.
 *
 * A cet égard  l'attention de l'utilisateur est attirée sur les risques
 * associés au chargement,  à l'utilisation,  à la modification et/ou au
 * développement et à la reproduction du logiciel par l'utilisateur étant
 * donné sa spécificité de logiciel libre, qui peut le rendre complexe à
 * manipuler et qui le réserve donc à des développeurs et des professionnels
 * avertis possédant  des  connaissances  informatiques approfondies.  Les
 * utilisateurs sont donc invités à charger  et  tester  l'adéquation  d
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
 * @property TemplateData
 */

class TemplateData implements \ArrayAccess, \Iterator, \Countable {
	public $vars = array();
	public $key  = null;
	public $i_iterate   = null;
	public $i_total     = null;
	public $i_position  = null;

	/**
	 * Enter description here ...
	 * @param array|TemplateData $vars
	 */
	public function __construct($vars = array(), $key=null) {		
		$this->key = $key;
		if ( !($vars instanceof TemplateData) ) { 
                    if( is_array($vars) ) {
			foreach ( $vars as $k => $v ) {
				$this->vars[$k] = new TemplateData($v, $k);
			}
                    }
                    else $this->vars = $vars;
		}
		else {
                    $this->vars = $vars->vars;
                    $this->i_iterate   = $vars->i_iterate;
                    $this->i_total     = $vars->i_total;
                    $this->i_position  = $vars->i_position;
                }
	}

	public function __toString() {
		return ( is_scalar($this->vars) || $this->vars === null ) ? (string) $this->vars : print_r($this->vars,true);
	}

	/**
	 * Enter description here ...
	 * @param unknown_type $name
	 * @return TemplateData
	 */
	public function __get($name) {
		if ( !$this instanceof TemplateData ) return $this->name;
		if ( is_array($this->vars) && array_key_exists($name, $this->vars) ) return $this->vars[$name];		
		
		$log = new Logger();
		$log->notice('variable non définie:'. $name);			
	}

	public function __call($name, $args) {		
		try {
			array_unshift($args, $this);
			return call_user_func_array("\Fp\Template\TemplateDataMethod::$name", $args );
		}
		catch (\Exception $e) {
			$log = new Logger();			
			$log->notice($e->getMessage(), array($e));	
		}
	}

	public function __set($name, $val) {
		$this->vars[$name] = new TemplateData($val, $name);
	}

	public function __unset($name) {
		unset($this->vars[$name]);
	}
	
	public function __isset($name) {
		return ( array_key_exists($name, $this->vars) ) ? true : false;
	}

	// array access
	public function offsetSet($offset, $value) {
		$this->__set($offset, $value);
		//$this->vars[$offset] = $value;
	}
	public function offsetExists($var) {
		return isset($this->vars[$var]);
	}
	public function offsetUnset($var) {
		unset($this->vars[$var]);
	}
	public function offsetGet($var) {
		if ( isset($this->vars[$var]) ) return $this->vars[$var];
	}

	// countable 
	public function count() {
     	return count($this->vars);
    }
    
	// array iterator
	public function rewind() {
		if ( is_array($this->vars) ) reset($this->vars);
	}

	public function current() {
		return ( is_array($this->vars) ) ? current($this->vars) : false;
	}

	public function key() {
		if ( is_array($this->vars) ) return key($this->vars);
	}

	public function next() {
		if ( is_array($this->vars) ) return next($this->vars);
	}
	
	public function prev() {
	    if ( is_array($this->vars) ) return prev($this->vars);
	}

	public function valid() {
		return $this->current() !== false;
	}
	
	public function end() {
	    return ( is_array($this->vars) ) ? end($this->vars) : false;
	}
	
	public function reset() {
	    return ( is_array($this->vars) ) ? reset($this->vars) : false;
	}
}