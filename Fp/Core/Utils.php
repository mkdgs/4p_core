<?php
namespace Fp\Core;
use \Exception;

class Utils {
    
    public static function addQueryDelimiter($url) {
        $url = rtrim($url, '?&');
        if ( strpos($url, '?') !== false ) {
            return $url.'&';
        }
        return $url.'?';
    }
    
}