<?php

namespace Fp\Template;

use Fp\Log\Logger;
use Fp\Core;
use Fp\Core\Debug;
use Fp\Db\Db;

require_once __DIR__ . '/TemplateData.php';
require_once __DIR__ . '/TemplateDataMethod.php';
require_once __DIR__ . '/TemplateHeader.php';

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

    protected $class_Data = 'TemplateData';
    protected $global_data = array();
    protected $doctype = '';
    protected $block = array();
    protected $data = array();
    protected $parsed = array();
    protected $logBlock = array();
    protected $debug = null;
    protected $noHeader = null;
    protected $tpl_dir = '';
    protected $header;
    protected $processing = false;
    public $html_body_open = '<body>';
    public $html_body_close = '</body>';

    public function __construct(\Fp\Core\Init $O) {
        $this->O = $O;
    }

    public function processing() {
        return $this->processing;
    }

    protected function getFile($file) {
        if ( is_file($file))
            return $file;
        
        if ( is_file($this->tpl_dir . $file))
            return $this->tpl_dir . $file;

        return false;
    }

    protected function isFile($file) {
        if (defined('PHP_MAXPATHLEN') && strlen($file) > PHP_MAXPATHLEN)
            return false;
        if (is_file($this->tpl_dir . $file))
            return $this->tpl_dir . $file;
        if (is_file($file))
            return $file;
    }

    protected function getHook($file) {
        try {            
            if ( $hook_file = $this->isFile($file . '_hook.php')) {                
                return $hook_file;
            }
        } catch (\Exception $e) {
            $this->logError($e);
        }
    }

    /**
     * @return TemplateHeader
     */
    public function head() {
        if (!isset($this->head))
            $this->head = new TemplateHeader($this->O);
        return $this->head;
    }

    private function noHeader() {
        $this->noHeader = true;
        return $this;
    }

    public function doctype($doctype = null) {
        if (!$doctype) {
            if (!$this->doctype) {
                $this->doctype('html5');
            }
            return $this->doctype;
        }
        switch ($doctype) {

            case 'xml':
                $this->doctype = '<?xml version="1.0" encoding="utf-8"?>';
                return $this;

            case 'xhtml_strict':
                $this->doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '
                        . '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\r\n"
                        . '<html xmlns="http://www.w3.org/1999/xhtml">' . "\r\n";
                return $this;

            case 'html5':
                $lang = ( $this->O->glob('lang') ) ? 'lang="' . $this->O->glob('lang') . '"' : '';
                $this->doctype = '<!DOCTYPE html>' . "\r\n";
                $this->doctype .= '<html>' . "\r\n";
                return $this;
        }
        throw new Exception('doctype inconnu ' . $doctype, 500);
    }

    public function setTplDir($dir) {
        $this->tpl_dir = $dir;
        return $this;
    }

    // TODO refactoring setTplDir
    protected function getGlobal() {
        if (empty($this->global_data)) {
            $this->setData($this->O->glob());
            $this->setTplDir($this->O->glob('dir_tpl'));
        }
        return $this->global_data;
    }

    public function setData(array $array) {
        if (empty($this->global_data)) {
            $this->global_data = (array) $this->O->glob();
            $this->setTplDir($this->O->glob('dir_tpl'));
        }

        if (is_array($array))
            $this->global_data = array_merge($array, $this->global_data);

        $this->tpl_Global = new TemplateData($this->global_data);
        return $this;
    }

    public function getTplGlobal() {
        $this->getGlobal();
        return $this->tpl_Global;
    }

    public function setDebug($debug = null) {
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
        if (array_key_exists($block, $this->block)) {
            return true;
        }
        return false;
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
    public function assign($block, $file, $data = array(), $cache_ttl = 0, $cache_id = null) {
        $this->assignFile($block, $file, $cache_ttl, $cache_id);
        $this->assignData($block, $data, false);
        return $this;
    }

    public function hasCache($block) {
        if ($this->isAssigned($block)) {
            if ($this->block[$block]['cache_result'])
                return true;
        }
    }

    public function assignFile($block, $file, $cache_ttl = 0, $cache_id = null) {
        $cache_id = $file . '_' . $cache_id;
        $this->block[$block] = array('name' => $block, 'file' => $file, 'data' => null, 'cache_ttl' => $cache_ttl, 'cache_id' => $cache_id, 'cache_result' => null);
        $this->setBlockCache($block, $cache_id);
        return $this;
    }

    public function assignData($block, $data, $clear_cache = true) {
        if ($this->isAssigned($block)) {
            if ($data instanceof TemplateData)
                $this->block[$block]['data'] = $data;
            else
                $this->block[$block]['data'] = new TemplateData($data);
            if ($clear_cache)
                $this->block[$block]['cache_result'] = null;
        }
        return $this;
    }

    /**
     * assigne un block de données (ex: function helper)
     * permet de voir le block de données dans la console
     * 
     * @param type $block
     * @param type $data
     * 
     * @return TemplateData
     */
    public function assignDataSet($block, $data) {
        $d = array(
            'name' => $block,
            'current_file' => self::$current_file
        );
        if ($data instanceof TemplateData) {
            $d['data'] = $data;
        } else
            $d['data'] = new TemplateData($data);

        $this->data[] = $d;
        return $d['data'];
    }

    /**
     * retire le block de la liste 
     * @param unknown $block
     * @return boolean
     */
    public function deassign($block) {
        if (array_key_exists($block, $this->block)) {
            unset($this->block[$block]);
        }
        return false;
    }

    /**
     * insert le block dans le template
     * @param unknown $name nom du block (qui à été assigné)
     * @param string $data 
     */
    public function insert($name, $data = null) {
        if ($this->isAssigned($name)) {
            $this->logBlockCore($name);
            $data = ( $data === null ) ? $this->block[$name]['data'] : $data;
            if (!$data instanceof TemplateData)
                $data = new TemplateData($data);

            $this->block[$name]['data'] = $data;

            if ($this->debug)
                echo '<!-- [BLOCK_START:' . $name . ':' . htmlentities(substr($this->block[$name]['file'], 0, 300), null, 'UTF-8') . '] -->';

            $this->renderBlock($this->block[$name]);

            if ($this->debug)
                echo '<!-- [BLOCK_END:' . $name . ']-->';
            $this->logBlockEnd($name);
        }
    }

    /**
     * insert le block seulement si il est assigné
     * @param unknown $block
     * @param string $data
     */
    public function insertIf($block, $data = null) {
        if ($this->isAssigned($block))
            $this->insert($block, $data);
    }

    protected function getCache($id) {
        return $this->O->cache()->get($id);
    }

    protected function setCache($id, $ttl, $data) {
        return $this->O->cache()->add($id, $data, $ttl);
    }

    protected function setBlockCache($block) {
        if ($this->block[$block]['cache_ttl']) {
            if ($cache_result = $this->getCache($this->block[$block]['cache_id'])) {
                $this->block[$block]['cache_result'] = $cache_result;
                return $this;
            }
        }
    }

    public function preProcessingBlockFile() {
        // permet d'éxecuter du code avant le rendu (ex choix de layout) chargment css, js...
        $B = $this;
        $O = $this->O;
        foreach ($this->block as $k => $block) {
            if (!array_key_exists('file', $this->block[$k]))
                continue; // ( si c'est un block de données ex: AsssignDataSet -> Helper
            $file = $this->block[$k]['file'];
            $A = $this->block[$k]['data'];
            $G = $this->getTplGlobal();
            $hookfile = $this->getHook($file);
            if ( $hookfile ) include $hookfile;
        }
    }

    public function parse($data, $file, $blockname = null) {

        if (is_object($data) AND $data instanceof TemplateData)
            $A = $data;
        else
            $A = new TemplateData($data);
        $G = $this->getTplGlobal();
        $O = $this->O;
        $processingState = $this->processing;
        $this->processing = 0;
        //pre processing file
        $hookfile = $this->getHook($file);
        if ( $hookfile ) include $hookfile;

        $this->processing = 1;
        try {
            if ($this->debug) {
                $name = ( $blockname ) ? $blockname : $file;
                $ct = 0;
                $name_k = 'parsed::' . $name . '-' . $ct;
                while (array_key_exists($name_k, $this->parsed) && $ct < 10) { // limite le nombre de block dans les log
                    $name_k = 'parsed::' . $name . '-' . $ct++;
                }
                $this->logBlockCore($name_k);

                $this->parsed[$name_k] = array('name' => $name_k,
                    'file' => $file,
                    'current_file' => self::$current_file,
                    'data' => $A);

                $this->logBlockEnd($name_k);
            }
            ob_start();
            $this->get_include_contents($file, $A);
            $t = ob_get_contents();
            ob_end_clean();
        } catch (\Exception $e) {
            $this->logError($e);
            echo 'error in:' . $file;
        }
        $this->processing = $processingState;
        return $t;
    }

    public function renderXml($master = 'HTML') {
        try {
            $this->preProcessingBlockFile();
            $this->processing = 1;
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

        if ($this->debug) {
            $body = '<debug>' . $this->parse_console() . '</debug>';
        }
        echo self::doctype() . $body;
    }

    public function renderHtml($master = 'HTML') {
        try {
            $this->preProcessingBlockFile();
            if (!$this->noHeader) {
                echo self::doctype();
                echo "<head>\n";
                $this->head()->make();
            }
            $this->processing = 1;
            ob_start();
            $this->logBlockCore('HTML');
            $this->renderBlock($this->block[$master]);
            $body = ob_get_contents();
            $this->logBlockEnd('HTML');
            if (ob_get_contents())
                ob_end_clean();
        } catch (\Exception $e) {
            if (ob_get_contents())
                ob_end_clean();
            throw $e;
        }

        if (!$this->noHeader) {
            $this->head()->make(1);
            echo "</head>\n";
        }

        echo $this->html_body_open;
        echo $body;
        if ($this->debug) {
            echo $this->parse_console();
        }

        $this->head()->makeJs(1);
        echo $this->html_body_close;
        if (!$this->noHeader) {
            echo "\r\n</html>";
        }
    }

    public function parse_console() {
        $txt = '';
        $d = array();
        $d['stats'] = Debug::point();
        $d['logSql'] = Db::get_logReq();
        $d['globData'] = $this->global_data;
        $d['data'] = $this->data;

        $d['block'] = array();
        $d['parsed'] = array();

        foreach ($this->block as $k => $block) {
            $v = (array) $block;
            $v['file'] = (!empty($block['file']) ) ? $block['file'] : null;
            $v['file'] = htmlentities(substr($block['file'], 0, 300), null, 'UTF-8');
            if (isset($this->logBlock[$k])) {
                $v['duration'] = $this->logBlock[$k]['duration'];
                $v['memory'] = $this->logBlock[$k]['memory'];
                $v['memory_peak'] = $this->logBlock[$k]['memory_peak'];
            }
            $d['block'][] = $v;
        }

        foreach ($this->parsed as $block) {
            $v = (array) $block;
            $v['file'] = htmlentities(substr($block['file'], 0, 300), null, 'UTF-8');
            $v['current_file'] = htmlentities(substr($block['current_file'], 0, 300), null, 'UTF-8');
            if (isset($this->logBlock[$block['name']])) {
                $v['duration'] = $this->logBlock[$block['name']]['duration'];
                $v['memory'] = $this->logBlock[$block['name']]['memory'];
                $v['memory_peak'] = $this->logBlock[$block['name']]['memory_peak'];
            }
            $d['parsed'][] = $v;
        }

        $d['log'] = Logger::getLog();
        $file = dirname(__FILE__) . '/tpl_console.php';
        $data = new TemplateData($d);
        try {
            $txt = $this->parse($data, $file);
            $txt = json_encode($txt);
            $txt = "<!-- start Console -->                
                    <iframe style='width:100%;' id='4p_console_content'  ></iframe>
                    <script>// <!--
                    var $4pConsole = document.getElementById('4p_console_content').contentWindow.document;
                    $4pConsole.open();
                    $4pConsole.write($txt);
                    $4pConsole.close(); 
                    function $4presizeIframe(obj) {                        
                        obj.style.height = (obj.contentWindow.document.body.scrollHeight+500) + 'px';
                    }
                    $4presizeIframe(document.getElementById('4p_console_content'));
                    // -->
                    </script>
                    <!-- end Console -->\r\n";
        } catch (\Exception $e) {
            var_dump($e);
            die('parse_console error');
        }
        return $txt;
    }

    protected function renderBlock($block) {
        if ($this->hasCache($block['name'])) {
            echo $block['cache_result'];
            return;
        }

        ob_start();
        $this->get_include_contents($block['file'], $block['data']);
        $out = ob_get_contents();
        ob_end_clean();
        echo $out;

        if ($block['cache_ttl']) {
            $this->setCache($block['cache_id'], $block['cache_ttl'], $out);
        }
    }

    protected static $current_file = null;

    protected function get_include_contents($file, TemplateData $A) {


        $is_file = $this->isFile($file);
        if ( !$is_file) { // on consière que c'est un block de code déjà préparé
            echo $file;
            return;
        }
        
        $file = $is_file;

        $previous = self::$current_file;
        self::$current_file = $file;
        $B = $this;
        $G = $this->getTplGlobal();
        $O = $this->O;
        try {
            include $file;
            self::$current_file = $previous;
        } catch (\Exception $e) {
            $this->logError($e);
        }
    }

    protected function logError(\Exception $e) {
        print_r($e->getMessage());
        $log = new Logger();
        $log->notice('exception: ' . $e->getMessage(), array('exception' => $e));
    }

    protected function logBlockCore($block) {
        if ($this->debug) {
            $this->logBlock[$block] = array('block' => $block,
                'start' => microtime(true),
                'duration' => '',
                'memory_start' => memory_get_usage(),
                'memory' => '',
                'memory_peak' => ''
            );
        }
    }

    protected function logBlockEnd($block) {
        if ($this->debug && array_key_exists($block, $this->logBlock)) {
            $this->logBlock[$block]['duration'] = number_format(microtime(true) - $this->logBlock[$block]['start'], 5, ',', ' ') . ' sec';
            $this->logBlock[$block]['memory'] = round((memory_get_usage() - $this->logBlock[$block]['memory_start']) / 1024, 2) . ' Ko';
            $this->logBlock[$block]['memory_peak'] = round(memory_get_peak_usage() / 1024, 2) . ' Ko';
        }
    }

    /**
     * @deprecated
     */
    public function blockSet($block, $file, $data = array()) {
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
    public function blockInclude($name, $data = null) {
        $this->insert($name, $data);
    }

}
