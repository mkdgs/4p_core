<?php
namespace Fp\Core;
use \Exception;

class Cdn {
    protected $cache_dir;

    public function __construct(\Fp\Core\Init $O) {
        $this->cache_dir = $O->glob('dir_cache').'cdn/';
        $this->O = $O;     
        $this->noCache =  $O->glob('cache');   
       // if( !file_exists($this->cache_dir) ) mkdir($this->cache_dir);
    }

    public function exist($file) {
        if ( !$this->noCache && is_file($this->cache_dir.$file) ) return true;
    }

    public function put($file, $data) {
        if ( file_put_contents($this->cache_dir.$file, $data) ) return true;
    }

    public function read($file) {
        if ( is_file($this->cache_dir.$file) ) {
            $this->readFile($this->cache_dir.$file);
        }
    }

    public function url($file) {
        return $this->O->glob('url').'/cdn/'.$file;
    }

    protected function writefile($file, $data) {
        if ($f = @fopen($file, 'w')) {
            $i = 0;
            while ( !flock($f, LOCK_EX | LOCK_NB) ) {
                $i++;
                if ( $i > 100 ) {
                    throw new Exception(__METHOD__.' file locked, aborting ', 500);
                }
                usleep(100);
            }
            fwrite($f, $data);
            flock($f, LOCK_UN);
            fclose($f);
        }
    }

    protected function readFile($file) {
        if ( is_readable($file) ) {
            if ( !$f = @fopen($file, 'r') ) {
                return false;
            }
            $i = 0;
            while ( !flock($f, LOCK_SH | LOCK_NB) ) {
                $i++;
                if ( $i > 100 ) throw new Exception(__METHOD__.' file locked, aborting ', 500);
                usleep(200);
            }
            try {
                $s = filesize($file);
                $d = '';
                while(!feof($f)) {
                    echo fread($f, $s);
                }
                flock($f, LOCK_UN);
                fclose($f);
                return;                
            } catch ( \Exception $e ) {
                //throw $e;
            }
            if ( is_file($file) ) @unlink($file);
        }
    }
}