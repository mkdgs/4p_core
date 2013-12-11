<?php
namespace Fp\Module;
use Fp\Core\Filter;

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
abstract class Controller_html extends Controller {
	/**
	 * template block name
	 * @var unknown_type
	 */
	protected $T_block	  = '';
	
	final protected function after_construct() {
		$this->O->html();
		if ( !$this->T_block ) $this->setTemplateBlock($this->O->glob('block_central_1'));
	}
	
	final public function setTemplateBlock($name) {
		$this->T_block = $name;
		return $this;
	}
	
	final public function getTemplateBlock() {
		return $this->T_block ;
	}
	
	
	final public function init() {		
		$this->before_config();		
		$this->config();
		$this->tpl_config();
		$this->after_config();		
		if ( !$this->method ) {
			// on récupère la methode pour le module
			$method = Filter::id($this->var_method, $_GET);
			$this->setMethod($method);
		}		
		return $this;
	}
	
	abstract  public function index();
	
	final public function render() {		
		if ( empty($this->method) ) $this->method = $this->defaultMethod;
		
		if ( !$this->methodIsAllowed($this->method) ) {			
			throw new \Exception('Not Allowed', 400);
		}
		if ( $this->methodIsCallable($this->method ) ) {
			$this->before_render();			
			$this->{$this->method}();	
			$this->after_render();
			return true;
		}		
		else {			
			throw new \Exception('Not Found', 404);
		}
	}		
}