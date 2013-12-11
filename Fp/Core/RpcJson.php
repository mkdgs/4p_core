<?php
namespace Fp\Core;
use \stdClass;
use \Exception;
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
class RpcJson {
	private $result  = null;
	private $error = null;
	private $id    = null;
	private $callback = null;
	private $errorCallback = null;
	
	public $debug = null;
	public $debug_html = null;	
	
	public function __construct() { 
		//Header("content-type: application/x-javascript"); 
	}
	
	public function setData($data) { 
		$this->result = $data;	
	}
	
	public function setCallback($data) { 
		$this->callback = $data;	
	} 
	
	public function setError($data) { 
		$e = new stdClass();		
		if ( $data instanceof Exception ) { 
			$e->code = $data->getCode();
			$e->message = $data->getMessage();
			if ( $this->debug ) {  
				$e->data = $data->getTraceAsString();
			}			
		}
		else { 
			$e->code = 500;
			$e->message = $data;
			if ( $this->debug ) {  
				$e->data = debug_backtrace();
			}			
		}		
		$this->error = $e;			
	}
	
	public function setErrorCallback($data) { 
		$this->errorCallback =  $data;
	}
	 
	public function setId($id) { 
		$this->id = $id; 
	}
	
	public function respond() {
		$data = array();
		$data['jsonrpc'] = "2.0";
		
		if ( $this->id ) { 
			$data['id'] = $this->id;
		}
		if ( $this->error ) { 			
			$data['error'] = $this->error;			
 		}
 		else { 
			$data['result'] = $this->result;
 		}
 		if ( $this->debug_html ) {  
 			$data['debug_sql'] = Db::get_logReq();
 			echo $data = '<pre>'.print_r($data,true).'</pre>';
 		}
 		else { 			
 			echo json_encode($data); 
 		}
	}
	
	private function getObjectPublic($d) { 
		if ( is_array($d) ) { 
			foreach ( $d as $k => $v ) { 
				$d[$k] = $this->getObjectPublic($v);
			}			
		}
		if ( is_object($d) ) {			
			return $this->get_object_public_vars($d);
		}
		return $d;
	}
	
	function get_object_public_vars($o) {
		$getFields = create_function('$o', 'return get_object_vars($o);');
		return $getFields($o); // Returns only 'name' and 'publicFlag'
	}	
}