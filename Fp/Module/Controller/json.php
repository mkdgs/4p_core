<?php
namespace Fp\Module;
use Fp\Core\Filter;
use Fp\Core\RpcJson;
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
abstract class Controller_json extends Controller {
	// spec:
	// http://groups.google.com/group/json-rpc/web/json-rpc-2-0
	// this implementation only work for by-name paramaters 
    // by-name: params MUST be an Object, with member names that match the Server expected parameter names. The absence of expected names MAY result in an error being generated. The names MUST match exactly, including case, to the method's expected parameters.
	// add of batch support
	
	public $rpc_params;
	public $rpc_id;
	public $rpc_method;
	public $data = array();
	/**
	 * Enter description here ...
	 * @var Core
	 */
	public $O;
	public $M;
	
	final protected function after_construct() {
		$this->O->raw();
		$this->rpc = new RpcJson();
	}

	final public function init() {		
		$this->before_config();
		$this->config();						
		$this->after_config();	
			
		$this->rpc->debug = $this->O->glob('debug');
		if ( $this->rpc->debug ) $this->rpc->debug_html = Filter::int('debug',$_GET);
		if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			$params = $_GET;
		}
		else $params = $_POST;
		
		$this->rpc_data   = Filter::array_($params);
		$this->rpc_method = Filter::id('method', $this->rpc_data, Filter::id($this->var_method, $_REQUEST));
		$this->rpc_id     = Filter::id('id', $this->rpc_data);
		$this->rpc_params = Filter::array_('params',  $this->rpc_data);
		
		$this->batch = array();
		$this->batch_mode = 0;	
		return $this;
	}

	final protected function processing() {		
		if ( !is_array($this->rpc_params) ) $this->rpc_params = array();		
		if ( $this->rpc_id ) { 
			$this->rpc->setId($this->rpc_id);
		}			
		$this->setMethod($this->rpc_method);		
	}

	final public function render() {
		// batching Support
		// batch mode
		if( is_array($this->rpc_data) && !$this->rpc_method && !$this->rpc_id ) {	    		
			foreach ($this->rpc_data as $jrpc ) {		
				$this->rpc_method = Filter::id('method',  $jrpc);
				$this->rpc_id     = Filter::id('id',  $jrpc);
				$this->rpc_params = ( isset($jrpc['params']) ) ?  $jrpc['params'] : array();
				$this->processing();
				$this->batch[] = $this->renderReturn();
			}
			echo '['.implode(',', $this->batch).']';
			$this->batch_mode = 1;
		}
		else {
			try {			
				$this->processing();	
				if ( !$this->methodIsAllowed($this->method) ) {
					throw new Exception('Procedure not Allowed', 400);
				}
				if ( !$this->methodIsCallable($this->method ) ) {	
					throw new Exception('Procedure not found', -32601);
				}
				$this->data = call_user_func_array(array($this, $this->method),array());
				$this->rpc->setData($this->data);
				$this->rpc->respond();
						
			} catch (Exception $e) {
				$this->rpc->setError($e);	
				$this->rpc->respond();		
			}
		}
	}
}