<?php
ini_set('error_reporting', -1);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

if ( !isset($C_glob) ) {
     die('no configuration found ( C_glob is not set )');
}

try {
    require_once 'init_set.php';
    require_once $C_glob['dir_data'] . 'class/class_init.php';
    $O = new init($C_glob, $C_glob_Private);   
    $O->process();
} catch (Exception $e_init) {
    
    if ( !headers_sent() ) {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 3600');
    }
    
    try { // tentative de log
        $e = $e_init;
        do {
            \Fp\Core\Debug::ExceptionHandler($e); 
        } while ($e = $e->getPrevious());
    }    
    catch (Exception $e) { /* echec silencieux */ }
    
    if ( isset($C_glob) && array_key_exists('debug', $C_glob) && $C_glob['debug'] > 2 ) {
        echo "<pre>\r\n";
        do {
            printf("[%s] %s \n %s:%d  (%d) \n %s\r\n ================== \r\n", get_class($e_init), $e_init->getMessage(), $e_init->getFile(), $e_init->getLine(), $e_init->getCode(), $e_init->getTraceAsString());
        } while ($e_init = $e_init->getPrevious());
        echo "\r\n</pre>";        
    } else if (is_file($C_glob['dir'].'503_service_unavailable.php')) {
        ob_get_clean();
        include $C_glob['dir'].'503_service_unavailable.php';
        die();
    } else {
        die('internal error');
    }
}
