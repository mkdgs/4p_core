<?php
namespace Fp\Core;
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
class Event { 
	/* event exemple */
	
		/*
		class test_event {
			static function fire($ev) {
				$trigger_data = $ev['trigger'];
				$bind_data 	  = $ev['bind'];
				echo 'bind: '.$bind_data[0].' '.$bind_data[1].' for event: '.$ev['event']."<br />\r\n";				
			}
		}		
		$O->event()->bind('test_trigger', array('test_event','fire'), array('general','1'));
		// namespace like jquery 1.3
		$O->event()->bind('test_trigger.myNamespace', array('test_event','fire'), array('myNamespace','2'));
		$O->event()->bind('test_trigger.orAnother', array('test_event','fire'), array('orAnother','3'));
		$O->event()->bind('test_trigger.myNamespace.orAnother', array('test_event','fire'), array('myNamespace.orAnother','4'));
		
		$O->event()->trigger('test_trigger','general');
		echo '--------<br />';
		$O->event()->trigger('test_trigger.myNamespace','myNamespace');
		echo '--------<br />';
		$O->event()->trigger('test_trigger.myNamespace.orAnother','myNamespace.orAnother');
		echo 'unbind --------<br />';
		$O->event()->unbind('test_trigger.myNamespace');
		$O->event()->trigger('test_trigger','general');
		*/
    protected $events = array(); 
    protected $eId    = 1;	
    
    public function __construct() { }
    
    protected function eventNamespace($event) {
		$e = explode('.',$event);
		return array(
			0 => array_shift($e),
			1 => $e
		);
    }
         
    public function &bind($event, $callback,array $params = array()) {
    	$this->eId++; 
    	$e = $this->eventNamespace($event);
    	$event 	   =  $e[0];
    	$namespace =  $e[1];
    	if ( !isset($this->events[$event]) ) $this->events[$event] = array();
    	    	
    	$this->events[$event][$this->eId] = array(	'id'	   => $this->eId,
    												'event'    => $event,
										    		'callback' => $callback,
										    		'params'   => $params,
    												'namespace' => $namespace );
        return $this->eId;
    } 
 
    public function unbind($event, $eId = null) {
    	$e = $this->eventNamespace($event);
	   	$event 	   =  $e[0];
	    $namespace =  $e[1]; 
        if (isset($this->events[$event]) ) { 
        	if ( $eId !== null && isset($this->events[$event][$eId]) ) { 
        		unset($this->events[$event][$eId]);
        	}
        	elseif ( !count($namespace) ) {
        		unset($this->events[$event]);
        	}
        	else { 
        		foreach ( $this->events[$event] as $k => $v) { 
        			$c = array_intersect($namespace,$v['namespace']);
        			if ( count($c) ) unset($this->events[$event][$k]);
        		}        		
        	}
        }
    } 

   public function trigger($event,$args=null) {  
   		$e = $this->eventNamespace($event);
	   	$event 	   =  $e[0];
	    $namespace =  $e[1];   		
        if (isset($this->events[$event])) { 
            foreach($this->events[$event] as $v) {  
            	if ( count($namespace) ) {
            		$c = array_intersect($namespace,$v['namespace']);
        			if ( !count($c) ) continue;
            	} 	
            	$args = func_get_args();
            	array_shift($args);
            	$params = array(
            		'bind'      => $v['params'],
            		'trigger'   => $args,
            		'id'	    => $v['id'],
    				'event'     => $event,
					'namespace' => $namespace); 	        	
                call_user_func_array($v['callback'], array($params)); 
            } 
        } 
    }     
} 