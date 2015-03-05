<?php
namespace Fp\Core;
            
use Fp\Route\Router;

use Fp\Db\Db As Db;
use Fp\Template\Template;
use Fp\Module;
use Fp\ModuleManager;
use \Exception;

require_once __DIR__.'/Debug.php';
require_once __DIR__.'/Filter.php';

/**
 * Copyright Desgranges Mickael
 * mickael@4publish.com
 *
 * Ce logiciel est un programme informatique servant à la création d'application web.
 * Ce logiciel est régi par la licence CeCILL-B soumise au droit français et
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
 * @link		http://4publish.com
 */
abstract class Init {

	public $data = array();
	protected $dir_core  =null;
	protected $dir_route =null;	
	
	protected $dir_class  = array();
	protected $dir_module = array();	

	protected $global = array();
	protected $global_private = array();
	protected $out = '';
	protected $stopProcess = 0;
        protected $nologin = 0;
	protected $version = '7.0';
        
        protected $instance = array();
        
        public function getInstance($classname) {
            if ( !empty($this->instance[$classname])  ) {                
                    return $this->instance[$classname];            
            }
        }

        public function setInstance($classname, $instance) {
            $this->instance[$classname] = $instance;
        }
        
	public function after_init() {}
	public function before_login() {}
	public function before_controller() {}
	public function after_controller() {}
	public function before_error() {}
	
	public function setDirRoute($dir) {
		$this->dir_route = $dir;
	}
	
	public function output_html() {
		$O = $this;
		$O->tpl()->head()->metaLanguage($this->glob('ctype'))
		->noCache($O->glob('cache'))
		->metaCharset($this->glob('charset'))
		->javascriptMaster($this->glob('url').'/config_javascript.php');

		$this->tpl()->setData($this->glob());
		$this->tpl()->doctype('html5');
	}

	public function output_xml() {
		$O = $this;
		$this->tpl()->setData($this->glob());
		$this->tpl()->doctype('xml');
	}

	public function __construct($global,$global_private) {		
		$this->global = $global;
		$this->global_private = $global_private;
		$this->dir_core   = $this->global['dir_lib'];		
		$this->dir_class  = (array) $this->global['dir_class'];
		$this->dir_module = (array) $this->global['dir_module'];
		spl_autoload_register(array($this, '__autoload'), false);		
		\Fp\Core\Debug::set($this);
	}

	public function controller() {
		$O = $this;
		$route = $this->route()->getRoute();	
			
		// dev tools
		if ( $this->glob('debug') > 2 ) {
			$c = $this->route()->getCommand();
			if ( isset($c[0]) ) {
				switch ($c[0]) {
					case 'Generator_Webservice':
						$this->tpl()->setDebug(0);
						\FpModule\Webservice\Module::invoke($O)->html()->render();
						break;

					case 'Generator_Crud':
					case 'Generator_Crud2':
						$this->tpl()->setDebug(0);
						\FpModule\Crud\Module::invoke($O)->html()->render();
						break;
				}
			}
		}

		// si c'est un module
		// le point d'entrée du module doit être présent dans la conf
		if ( !$this->output() ) {		    
		    if ( $module_class = Filter::raw('module', $_GET) ) {		        	
		        $module_entry = $this->glob('module_entry');
		        $module_class = trim(str_replace('.', '\\', $module_class), '/');
		        $mode = Filter::id('mode', $_REQUEST);
		        if ( $module_class ) {
		            if ( class_exists($module_class) ) {    		        
                                $module = new $module_class($O);
                                if ( $module instanceof \Fp\Module\Module ) {
                                    $module->setMode($mode);
                                    if (  $module->getMode() ) {
                                        $controller = $module->getController($module->getMode());
                                        $controller->setRequestParams($_GET);
                                        $controller->init();
                                        $controller->render();
                                    }
                                    else throw new Exception('mode not found', 404);
                                }
                                else throw new Exception('module not found', 404);
                            }
                            else throw new Exception('module not found', 404);
		        }
		        else throw new Exception('module not found', 404);
		    }
		}

		if ( !$this->output() ) {			
			$this->router()->parse($this, '/'.$this->router()->getRawRoute());
		}
		
		// si on a toujours rien pour la sortie on regarde dans data/route/
		if ( !$this->output() ) {
			// c'est un fichier ou un dossier
			$file = $this->dir_route.$route;
			if ( is_dir($file) && is_file($file.'/index.php') ) include_once $file.'/index.php';
			elseif ( is_file( $file.'.php') ) include_once $file.'.php';
		}
		
		if ( !$this->output() ) {
		    // ancienne méthode, déprécié
		    // encore utilisé par le module facebook ( voir redirect_uri )		    
		    $command_0 = $this->route()->getCommand(0);
		    if ( $command_0 == 'mod' ) {
		    
		        $module_entry = $this->glob('module_entry');
		        $module_class = null;
		        $module_class = $this->route()->getCommand(1);
		        $module_class = trim(str_replace('.', '\\', $module_class), '/');
		        if ( !$mode = Filter::id('m', $_REQUEST) ) {
		            $mode = $this->route()->getCommand(2);
		        }
		        if ( $module_class && class_exists($module_class) ) {
		            $module = new $module_class($O);
		            if ( $module instanceof \Fp\Module\Module ) {
		                $module->setMode($mode);
		                if (  $module->getMode() ) {
		                    $controller = $module->getController($module->getMode());
		                    $controller->setRequestParams($_GET);
		                    $controller->init();
		                    $controller->render();
		                }
		                else throw new Exception('mode not found', 404);
		            }
		            else throw new Exception('module not found', 404);
		        }
		    }
		}
		
	}
	
	public function errorPage($code, $e_general) {
		$O = $this;
		switch ( $code ) {
			case '400':
				include_once $this->dir_route.'error/400.php';
				break;
		
			case '401':
				include_once $this->dir_route.'error/401.php';
				break;
		
			case '403':
				include_once $this->dir_route.'error/403.php';
				break;
		
			case '404':
				include_once $this->dir_route.'error/404.php';
				break;
		
			case '500':
                                include_once $this->dir_route.'error/500.php';
				break;
                            
			default:
                            throw new Exception('General Error', 503, $e_general);
                    
		}	
		$this->render();
	}

	public function stopProcess() {
	    $this->stopProcess = 1;
	}
	public function process() {
		if ( !array_key_exists('dir_route', $this->global) ) {
			$this->global['dir_route']  = $this->glob('dir_data').'route/';
		}
		$this->dir_route  = $this->global['dir_route'];
		try {						
			// c'est le fichier de config pour $4p.js
			if ( $this->route()->getCommand(-1) == 'config_javascript.php' ) {				
			    return require_once $this->glob('dir_lib').'init_config_javascript.php';
			}
                        
			if ( !$this->stopProcess ) $this->db();
			if ( !$this->stopProcess ) $this->after_init();
			if ( !$this->stopProcess ) $this->event()->trigger('after_init.core', $this);
                        if ( !$this->nologin ) {
                            if ( !$this->stopProcess ) $this->event()->trigger('before_login.core', $this);
                            if ( !$this->stopProcess ) $this->before_login();
                            if ( !$this->stopProcess ) $this->login();
                            if ( !$this->stopProcess ) $this->event()->trigger('after_login.core', $this);
                        }
			if ( !$this->stopProcess ) $this->event()->trigger('before_controller.core', $this);
			if ( !$this->stopProcess ) $this->before_controller();
			if ( !$this->stopProcess ) $this->controller();
			if ( !$this->stopProcess ) $this->after_controller();
			if ( !$this->stopProcess ) $this->event()->trigger('after_controller.core', $this);
			if ( $this->stopProcess ) return $this->stopProcess = 0;
			
			if ( !$this->output() ) throw new Exception('not found', 404);

			$this->render();

		} catch (\Exception $e_general) {
			//try {
				//$this->before_error();
				//Debug::ExceptionHandler($e_general);
				$this->errorPage($e_general->getCode(), $e_general);
                        /*
			} catch ( \Exception $a) {
				$str = 'Error Handler Failure: <br />'
						.'<pre>'.$a->getMessage()
						."\r\n".$a->getFile().' - '.$a->getLine()
						."\r\n".$a->getTraceAsString()
						.'</pre>';
				$str .= 'Original Failure: <br />'
						.'<pre>'.$e_general->getMessage()
						."\r\n".$e_general->getFile().' - '.$e_general->getLine()
						."\r\n".$e_general->getTraceAsString()
						.'</pre>';
				//$str .= 'backtrace: <br /><pre>'.print_r(debug_backtrace(),true).'</pre>';
				die($str);
			}*/
		}
 

	}


	
	protected $tpl;
	/**
	 * @return Template
	 */
	public function tpl() {
		if ( !isset($this->tpl) ) {
			$this->tpl = new Template($this);
		}
		return $this->tpl;
	}

	
	protected $header;
	/**
	 * @return HttpHeader
	 */
	public function header() {            
		if ( !isset($this->header) ) {
                        require_once __DIR__.'/HttpHeader.php';
			$this->header = new HttpHeader();
		}
		return $this->header;
	}

	protected $db;
	/**
	 * @return Db
	 */
	public function db() {
		if ( !isset($this->db) ) {
                        require_once __DIR__.'/../Db/Db.php';
			# initialisation bdd
			$type = ( array_key_exists('type', $this->global_private['db'][0]) ) ? $this->global_private['db'][0]['type'] : '';
			Db::connect( 	$this->global_private['db'][0]['id'],
			$this->global_private['db'][0]['host'],
			$this->global_private['db'][0]['user'],
			$this->global_private['db'][0]['pass'],
			$this->global_private['db'][0]['base'],
			$type);
			$this->db = Db::get_link($this->global_private['db'][0]['id']);
		}
		return $this->db;
	}

	protected $cache;
	/**
	 * @return Cache
	 */
	public function cache() {
		if ( !isset($this->cache) ) {
                    require_once __DIR__.'/Cache.php';
                    $this->cache = Cache::load($this);
		}
		return $this->cache;
	}

	
	/**
	 * Enter description here ...
	 * @return Router
	 * @deprecated use Core::router() instead
	 */
	public function route() {                
		return $this->router();
	}
	
	protected $router;
	/**
	 * Enter description here ...
	 * @return Router
	 */
	public function router() {                
		if ( !isset($this->router) ) {
                    require_once __DIR__.'/../Route/Route.php';
                    require_once __DIR__.'/../Route/Router.php';
		    $this->router = new Router();
		}
		return $this->router;
	}


	protected    $event;
	/**
	 * @param bool $start
	 * @throws Exception
	 * @return Event
	 */
	public function event() {
		if ( !isset($this->event) ) {
			require_once __DIR__.'/Event.php';
			$this->event = new Event();
		}
		return $this->event;
	}


	/* @var Session */
	protected    $session;
	/**
	 *
	 * @return Session
	 */
	public function session() {
		if ( !isset($this->session) ) {
			require_once __DIR__.'/Session.php';
			$this->session = Session::load($this);
			if( $m = $this->glob('session_mode') ) Session::$mode = $m;
		}
		return $this->session;
	}

        public function hasSession() {
            return is_object($this->session);
        }
        
	/* @var Login */
	protected $auth;
	/**
	 *
	 * @return \Fp\Login\Login
	 */
	public function auth() {
		if ( !isset($this->auth) ) {
                        require_once __DIR__.'/../Login/Login.php';
			$this->auth = new \Fp\Login\Login($this) ;
			$this->auth->sessionCheck();
		}
		return $this->auth;
	}

	/* @var Permission */
	public    $permission;
	/**
	 * @return \Fp\Permission\PermissionUser
	 */
	public function permission() {
		if ( !isset($this->permission) ) {
                   require_once __DIR__.'/../Permission/PermissionUser.php';
		   $this->permission = new \Fp\Permission\PermissionUser($this);
		}
		return $this->permission;
	}

	protected function login() {
		$uid = null;
		$login_pass   = Filter::dbSafe('login_pass',$_POST);
		$login_pseudo = Filter::dbSafe('login_pseudo',$_POST);
		$login_mail   = Filter::dbSafe('login_mail',$_POST);
		$login_uid    = Filter::dbSafe('uid',$_COOKIE);
		$login_cookie_token  = Filter::dbSafe('token',$_COOKIE);
		$wantAcookie  = Filter::dbSafe('wantAcookie',$_REQUEST);
		$next   = Filter::dbSafe('next',$_POST);

		$login_mixed  = filter::dbSafe('login_mixed',$_POST);
		if ( $login_mixed ) {
			$login_pseudo = $login_mixed;
			$login_mail   = $login_mixed;
		}

		$auth = $this->auth();
		if ( !$auth->isAuth() ) {
			$try = 0;
			// on cherche uid
			if ( !$uid && $login_pass && $login_pseudo ) {
				$try = 1;
				$Login = new Login_Pseudo($this);
				$uid = $Login->getUid($login_pseudo);
			}
			if ( !$uid && $login_pass && $login_mail ) {
				$try = 1;
				$Login = new Login_Mail($this);
				$uid = $Login->getUid($login_mail);
			}

			if ( $uid && $login_pass ) {
				$try = 1;
				$login = new Login_Password($this);
				if ( $login->check($uid,$login_pass,1) ) $auth->check($uid);
			}
			elseif (  $login_uid && $login_cookie_token ) {
				$try = 1;
				$Login = new Login_Password($this);
				if ( $Login->checkCookieToken($login_uid,$login_cookie_token) ) {
					$auth->check($login_uid);
				}
			}

			if ( $auth->isAuth() ) {
				if ( $wantAcookie ) {
					$Login = new Login_Password($this);
					$login->sendCookieToken($auth->uid());
				}
				$this->event()->trigger('login_success.core', $auth->uid());
			}
			elseif ( $try ) $this->event()->trigger('login_fail.core');
		}
		else {
			if ( isset($_GET['logout']) || Filter::id('a',$_GET) == 'logout' || Filter::id('action',$_GET) == 'logout'  ) {
				$uid = $auth->uid();
				$auth->logout();
				$this->event()->trigger('logout.core', $uid);
			}
		}
		if ( $auth->isAuth() ) $this->event()->trigger('logged_in.core',$this->auth()->uid());
	}

	/**
	 * Enter description here ...
	 * @param string $name
	 * @param unknown_type $value
	 * @throws Exception
	 * @return multitype:|unknown
	 */
	public function glob($name=null,$value=null) {
		if ( $name === null ) {
			return $this->global;
		}
		if ( func_num_args() == 2 )  return $this->global[$name]=$value;
		elseif ( isset($this->global[$name]) ) return $this->global[$name];
	}

	public function globPrivate($name=null,$value=null) {
		if ( $name !== null ) {
			if ( func_num_args() == 2 )  return $this->global_private[$name]=$value;
			elseif ( isset($this->global_private[$name]) ) return $this->global_private[$name];
			throw new Exception('global private '.$name.' is not set');
		}
		return $this->global;
	}
       
	private static function autoload_psr_search(\Fp\Core\Init $O, $dir, $className,$prefix=null) {
		    $className = ltrim($className, '\\');
		    $fileName  = '';
		    $namespace = '';
		    if ( $lastNsPos = strrpos($className, '\\')) {
		        $namespace = substr($className, 0, $lastNsPos);
		        $className = substr($className, $lastNsPos + 1);
		        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		    }
		    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className). '.php';		    
                    
                    if ( is_file($dir.$fileName) ) { // full qualified name
		        require_once $dir.$fileName;
		        return true;
		    }  
	}

	static $autoload_loop = array();
	public function __autoload($class_name) {
		if ( array_key_exists($class_name, self::$autoload_loop) ) {
			// probleme avec class_exist() si on leve une erreur
			//throw new Exception(__METHOD__." class $class_name not found ", 500);
			return false;
		}
		self::$autoload_loop[$class_name] = true;
		$dir = $class_name;
		
		// dans le Core
		if ( self::autoload_psr_search($this, $this->dir_core, $class_name) ) return;
		
		// dans le projet
		foreach ( $this->dir_class as $dir ) {			
			if ( self::autoload_psr_search($this, $dir, $class_name) ) return;
		}
		
		foreach ( $this->dir_module as $dir ) {
			if ( self::autoload_psr_search($this, $dir, $class_name) ) return;
		}
	}

	public function outputMode() {
		return $this->out;
	}

	public function raw() {
		$this->out = 'raw';
	}

	protected function output() {
		return $this->out;
	}

	public function xml() {
		$this->out = 'xml';
	}

	public function html5() {
		$this->out = 'html5';
		$this->setHtml();
	}

	public function html() {
		$this->out = 'html';
		$this->setHtml();
	}

	private function setHtml() {
		// debug option
		if ( $this->global['debug'] >= 2 ) {
			$session_console = $newsession_console = ( is_object($this->session) ) ? $this->session()->get('console') : 0;
			
			
			$consoleGet = Filter::id('console',$_GET);
			//show block data ?
			if ( $consoleGet == 'off' ) {
				$newsession_console = 0;
			}
			elseif ( $consoleGet == '1' ) {
				$newsession_console = 1;
			}
				
			if ( $session_console != $newsession_console ) {
				if( is_object($this->session) ) $this->session()->set('console', $newsession_console);
				$session_console = $newsession_console;
			}
			
			if ( $session_console ) {
			    $this->tpl()->setDebug($session_console);
			}

			// reset cache
			if ( $consoleGet == 'cache_reset' ) {
			    $dir_cache = $this->glob('dir_cache');
			    \Fp\File\Dir::emptyDir($dir_cache.'cdn/');
			    \Fp\File\Dir::emptyDir($dir_cache.'pool/');
			}
			
			// stop cache
			$cache = $this->glob('cache');
                        if ( !$cache ) { // not override if cache is disabled ( cache == 1 )
                            $new_cache = ( is_object($this->session) ) ? $this->session()->get('console_cache') : $cache;
                            if ( $consoleGet == 'cache_stop' ) {
                                $new_cache = 1;
                            }
                            elseif ( $consoleGet == 'cache_start' ) {
                                $new_cache = 0;
                            }
                            if ( $new_cache != $cache ) {
                                if( is_object($this->session) ) $this->session()->set('console_cache', $new_cache);
                                $cache = $new_cache;
                            }	
                        }
			$this->global['cache'] = $cache;
			
			if ( $consoleGet && $redirect = Filter::custom('redirect',$_GET, function ($var) { return urldecode($var); } ) ) {
			    $this->header()->redirect($redirect);
			}
		}
	}	
	
	public function render() {
		// @todo ne pas envoyer le header si batch ou delayed request
		if ( !headers_sent() ) {
			header("Pragma:");
			header("Expires:");
			//header("Last-Modified: ". gmdate("D, d M Y H:i:s") ." GMT");
			header("Cache-Control: private, max-age=2");
		}

		if ( is_object($this->session) ) $this->session()->set('prev_request_uri', $this->route()->request_uri);
		switch ( $this->out ) {
			case 'xml' :
				if ( !headers_sent() ) {
					header("content-type: text/xml");
				}
				$this->output_xml();
				echo $this->tpl()->renderXml();
				return true;
			case 'html5':
			case 'html':
				if ( !headers_sent() ) {
					header("content-type: text/html");
				}
				$this->output_html();
				echo  $this->tpl()->renderHtml();
				return true;
					
			case 'raw':
				return true;
					
			default:
				throw new Exception(__METHOD__.' mode de sortie non selectioné raw/html', 500);
		}
		exit();
	}
}