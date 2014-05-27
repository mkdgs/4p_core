<?php
namespace Fp\Template;
use \Fp\Core;
use Fp\Core\Filter;
use Fp\Core\LessCss;
use Fp\Core\Cdn;
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
class TemplateHeader {
    private $script   = Array();
    private $scriptMaster   = Array();
    private $scriptInline = Array();
    private $css 	 = Array();
    private $link     = Array();
    private $meta     = Array();
    private static $cache = null;
    private $instance = null;
    private $base_url = null;
    private static $assets_include = array();

    public function __construct(\Fp\Core\Init $O) {
        $this->O = $O;
    }

    public function base_url($url,$target=null) {
        $target = ( $target ) ? 'target="'.$target.'"' :'';
        $this->base_url = '<base href="'.$url.'" '.$target.' />';
        return $this;
    }

    /**
     * Enter description here ...
     * @param unknown_type $url
     * @return unknown
     * @deprecated
     */
    public function makeUrlRelative($url) {
        return $url;
        //return str_replace($this->O->glob('url'), $this->O->glob('url_relative'), $url);
    }

    function noCache($val) {
        if ( $val && !self::$cache ) self::$cache = time();
        else self::$cache = '';
        return $this;
    }

    function rw_cache($url) {
        $url = $this->makeUrlRelative($url);
        if ( self::$cache  ) {
            return preg_match('/\?/',$url) ? $url.'&_='.self::$cache : $url.'?_='.self::$cache;
        }
        else if ( $v = $this->O->glob('version') ) {
            return preg_match('/\?/',$url) ? $url.'&v='.$v : $url.'?v='.$v;
        }
        return $url;
    }

    protected $cachedJsMaster = array();
    function javascriptMaster($s) {        
        if ( $this->isAssetsInclude($s) ) return $this;
        self::$assets_include[$s] =true;
        
        $key = 'jsMaster';
        if ( !array_key_exists($key, $this->cachedJsMaster) ) $this->cachedJsMaster[$key] = array();
        $this->cachedJsMaster[$key][] = $s;        
        return $this;
    }

    function js($s,array $attr=null) {
        return self::javascript($s, $attr);
    }

    protected $js = array();
    protected $cachedJs = array();
    function javascript($s, $no_cache=0) {
        if ( $this->isAssetsInclude($s) ) return $this;
        self::$assets_include[$s] =true;
        
        $key = 'js';
        if ( $no_cache ) {
            if ( !array_key_exists($key, $this->js) ) $this->js[$key] = array();
            $this->js[$key][] = $s;
        }
        else {
            if ( !array_key_exists($key, $this->cachedJs) ) $this->cachedJs[$key] = array();
            $this->cachedJs[$key][] = $s;
        }
        
        return $this;
    }

    function rawCode($str) {
        $this->script[md5($str)] =  ''.$str.'';
    }

    function javascriptCode($code, $addTag=null) {
        if ( $addTag ) $code = '<script type="text/javascript">'.$code.'</script>';
        $this->scriptInline[md5($code)] =  $code;
        return $this;
    }
    function jsTemplate($id,$file) {
        $this->link['jstemplate'] = '<link id="x-js-template-'.$id.'" rel="x-js-template" type="text/x-js-template" href="'.$file.'" />';
        return $this;
    }

    protected $cachedCss = array();
    function lessCss($s,$media='all') {
        if ( $this->isAssetsInclude($s) ) return $this;
        self::$assets_include[$s] =true;
        
        $key = 'cssless';
        if ( !array_key_exists($media, $this->cachedCss) ) $this->cachedCss[$media] = array($key => array());
        if ( !array_key_exists($key, $this->cachedCss[$media]) ) $this->cachedCss[$media][$key] = array();
        $this->cachedCss[$media][$key][] = $s;
        return $this;
    }

    function css($s, $media='all') {
        if ( $this->isAssetsInclude($s) ) return $this;
        self::$assets_include[$s] =true;
        
        $this->css[md5($s)] = '<link rel="stylesheet" href="'.Filter::htmlAttr($this->rw_cache($s)).'" type="text/css" media="'.$media.'" />';
        return $this;
    }

    function linkRel($ref, $type="canonical") {
        $this->link[md5($ref)] = '<link rel="'.Filter::htmlAttr($type).'" href="'.Filter::htmlAttr($ref).'" />';
        return $this;
    }

    function touchIcon($s) {
        $this->link['touch_icon'] = '<link rel="apple-touch-icon-precomposed" href="'.Filter::htmlAttr($this->rw_cache($s)).'" />';
        return $this;
    }
    function shortcutIcon($s) {
        $this->link['shortcut_icon'] = '<link rel="shortcut icon" href="'.Filter::htmlAttr($this->rw_cache($s)).'" type="image/x-icon" />';
        return $this;
    }
    function icon($s) {
        $this->link['icon'] = '<link rel="icon" href="'.Filter::htmlAttr($this->rw_cache($s)).'" />';
        return $this;
    }
    function rss($titre,$url) {
        $this->link[md5($url)] =  '<link rel="alternate" type="application/rss+xml" title="'.Filter::htmlAttr($titre).'" href="'.Filter::htmlAttr($this->rw_cache($url)).'" />';
        return $this;
    }
    function atom($titre,$url) {
        $this->link[md5($url)] = '<link href="'.Filter::htmlAttr($this->rw_cache($url)).'" type="application/atom+xml" rel="alternate" title="'.Filter::htmlAttr($titre).'" />';
        return $this;
    }

    function base($s) {
        $this->meta['base'] = '<base href="'.Filter::htmlAttr($s).'"/>';
        return $this;
    }
    function title($s) {
        $this->meta['title'] =  '<title>'.Filter::htmlspecialchars($s).'</title>';
        return $this;
    }

    // for open graph http://ogp.me/
    function metaProperty($name,  $content) {
        $this->meta['property '.$name] = 	'<meta property="'.Filter::htmlAttr($name).'" content="'.Filter::htmlAttr($content).'" />';
        return $this;
    }

    function metaName($name,  $content) {
        $this->meta['name '.$name] = 	'<meta name="'.Filter::htmlAttr($name).'" content="'.Filter::htmlAttr($content).'" />';
        return $this;
    }

    function metaDescription($s)  {
        $this->meta['description'] = '<meta name="description" content="'.Filter::htmlAttr($s).'" />';
        return $this;
    }
    function metaAuthor($s) {
        $this->meta['author'] = '<meta name="author" content="'.Filter::htmlAttr($s).'" />';
        return $this;
    }
    function metaCharset($s='utf-8') {
        $this->meta['charset'] =  '<meta http-equiv="Content-Type" content="text/html; charset='.$s.'" />';
        return $this;
    }
    function metaLanguage($s='fr_FR') {
        $this->meta['language'] =  '<meta name="language" content="'.$s.'" />';
        return $this;
    }
    function metaCopyright($s) {
        $this->meta['copyright'] = 	'<meta name="copyright" content="'.Filter::htmlAttr($s).'" />';
        return $this;
    }
    function metaRobots($s='NOODP,index,follow') {
        $this->meta['robots'] = 	'<meta name="robots" content="'.Filter::htmlAttr($s).'" />';
        return $this;
    }
    function metaRevisit($s='3 days') {
        $this->meta['revisit'] = 	'<meta name="revisit-after" content="'.Filter::htmlAttr($s).'" />';
        return $this;
    }
    function metaDistribution($s='Global') {
        $this->meta['distribution'] = 	'<meta name="distribution" content="'.Filter::htmlAttr($s).'" />';
        return $this;
    }

    function metaHttpEquiv($name,$content) {
        $this->meta['http_equiv'.$name.$content] =  '<meta  http-equiv="'.Filter::htmlAttr($name).'" content="'.Filter::htmlAttr($content).'" />';
        return $this;
    }
    
    protected function isAssetsInclude($url) {
        if ( array_key_exists($url, self::$assets_include) ) return true;
    }

    protected $headerStarted = null;
    
    function make($last = null) {
        require_once __DIR__.'/../../Lib/JSMin.php';
        require_once __DIR__.'/../../Lib/CSSmin.php';
        $Cdn  = new Cdn($this->O);
        $less = new LessCss();
        $debug_level = $this->O->glob('debug');

        if ( $debug_level > 2 ) $line_cr = "\r\n";
        else $line_cr = null;

        if ( !$this->headerStarted ) {
            $this->headerStarted = 1;
            echo $this->base_url.$line_cr;
        }

        foreach ( $this->cachedCss as $media => $csskey )  {
            foreach ( $csskey as $k => $files ) {
                $sorted_files_ref = $files;
                sort($sorted_files_ref);
                $key = 'lesscss_'.md5(implode('',$sorted_files_ref)).'_'.count($files).'_'.$this->O->glob('version').self::$cache.'.css';

                if ( !$cache = $Cdn->exist($key) ) {
                    foreach ( $files as $file ) {
                        try {
                            // rewrite relative uri to absolute url
                            $cb_replace = function($matches) use ($file) {
                                $path = implode('/',array_slice(explode('/', $file), 0,-1));
                                return  'url('.$matches[1].$path.'/'.$matches[2].$matches[3].')';
                            };                            
                            $css = @file_get_contents($file);
                            $css = preg_replace_callback("#url\((['\"]?)([^'\":)]+)(['\"]?)\)#i", $cb_replace, $css);
                            $css = $less->compile($css);
                            if  ( $debug_level < 3 ) {
                                $cssmin = new \CSSmin();
                                $css = $cssmin->run($css, 3000);
                            }
                            $cache .= $css.$line_cr;
                        } catch (\Exception $e) {
                             if  ( $debug_level > 2 ) {
                                 throw $e;
                             }                            
                        }
                    }
                    $Cdn->put($key, $cache);
                }
                echo '<link rel="stylesheet" href="'.$Cdn->url($key).'" type="text/css" media="'.$media.'" />'.$line_cr;
                break;
            }
        }
        $this->cachedCss = array();

        foreach ( $this->css  as $v	) echo $v.$line_cr;
        $this->css = array();
        
        if  ( $debug_level >= 2 && $this->O->glob('cache') ) {
            foreach ( $this->cachedJsMaster as $k => $files ) {
                foreach ( $files as $file ) {
                    echo '<script src="'.$this->rw_cache($file).'" type="text/javascript" ></script>'.$line_cr;
                }
            }
            $this->cachedJsMaster = array();
        }
        else {
            foreach ( $this->cachedJsMaster as $k => $files ) {
                $sorted_files_ref = $files;
                sort($sorted_files_ref);
        
                $key = 'jsMaster_'.md5(implode('',$sorted_files_ref)).'_'.count($files).'_'.$this->O->glob('version').self::$cache.'.js';
                if ( !$cache = $Cdn->exist($key) ) {
                    foreach ( $files as $file ) {
                        // lazy loading fix
                            $srcfile = json_encode($file);
                            $cache .= "document.currentScript = document.createElement('script');";
                            $cache .= "document.currentScript.src = $srcfile;";
                            $js = @file_get_contents($file);
                        if  ( $this->O->glob('debug') < 3 ) {
                            $jsmin = new \JSMin($js);
                            $js = $jsmin->min();
                        }
                        $cache .= $js.$line_cr;
                    }
                    $Cdn->put($key, $cache);
                }
                echo '<script src="'.$Cdn->url($key).'" type="text/javascript" ></script>'.$line_cr;
                break;
            }
            $this->cachedJsMaster = array();
        }

        if ( $last ) {
            foreach ( $this->link as $v ) echo $v.$line_cr;
            foreach ( $this->meta as $v ) echo $v.$line_cr;
        }
        
        if ( ob_get_level() ) ob_flush();
        flush();
    }

    public function makeJs() {
        require_once __DIR__.'/../../Lib/JSMin.php';
        require_once __DIR__.'/../../Lib/CSSmin.php';
        $Cdn  = new Cdn($this->O);
        $less = new LessCss();
        $debug_level = $this->O->glob('debug');
        
        if ( $debug_level > 2 ) $line_cr = "\r\n";
        else $line_cr = null;
        
        foreach ( $this->js as $key => $files ) {
            foreach ( $files as $file ) {
                echo '<script src="'.$this->rw_cache($file).'" type="text/javascript" ></script>'.$line_cr;
            }
        }
               
        if  ( $debug_level >= 2 && $this->O->glob('cache') ) {        
            foreach ( $this->cachedJs as $key => $files ) {
                foreach ( $files as $file ) {
                    echo '<script src="'.$this->rw_cache($file).'" type="text/javascript" ></script>'.$line_cr;
                }
            }
            $this->cachedJs = array();
        }
        else {
            foreach ( $this->cachedJs as $key => $files ) {
                $sorted_files_ref = $files;
                sort($sorted_files_ref);
        
                $key = 'js_'.md5(implode('', $sorted_files_ref)).'_'.count($files).'_'.$this->O->glob('version').self::$cache.'.js';
                if ( !$cache = $Cdn->exist($key) ) {
                    foreach ( $files as $file ) {
                        try {
                            // lazy loading fix
                            $srcfile = json_encode($file);
                            $cache .= "document.currentScript = document.createElement('script');";
                            $cache .= "document.currentScript.src = $srcfile;";
                            $js = @file_get_contents($file);
                            if  ( $this->O->glob('debug') < 3 ) {
                                $jsmin = new \JSMin($js);
                                $js = $jsmin->min();
                            }
                            $cache .= $js.$line_cr;
                        } catch (\Exception $e) {
                        }
                    }
                    $Cdn->put($key, $cache);
                }
                echo '<script src="'.$Cdn->url($key).'" type="text/javascript" ></script>'.$line_cr;
                break;
            }
            $this->cachedJs = array();
        }
        
        foreach ( $this->scriptInline as $v ) echo $v.$line_cr;
        $this->scriptInline = array();
    }
}