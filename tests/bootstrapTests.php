<?php

try {
    $C_glob = array();
    $C_glob_Private = array();
    // 0 prod sans log
    // 1 prod log silencieux
    // 2 dev
    // 3 full dev
    $C_glob['debug']        = 1; #! affichage et enregistrement des erreurs
    // 0 en prod
    // 1 en dev
    // $C_glob['cache'] 	= 0; #! empeche la mise en cache des fichiers js et css
    $C_glob['version']	= '3.2.3'; #!
    $C_glob['memory_limit'] = '100M';
    $C_glob['max_filesize'] = '20M';
    // * LC_COLLATE pour la comparaison de chaînes de caractères. Voir strcoll()
    $C_glob['lc.collate']		= 'fr_FR.UTF-8';
    // * LC_CTYPE pour la classification et la conversion de caractères. Voir strtoupper()
    $C_glob['lc.ctype']			= 'fr_FR.UTF-8';
    // * LC_MONETARY pour localeconv()
    $C_glob['lc.monetary']		= 'fr_FR.UTF-8';
    // * LC_TIME pour le format de date et d'heure avec strftime()
    $C_glob['lc.timezone']     = 'Europe/Paris';
    // * LC_MESSAGES pour les réponses système (disponible si PHP a été compilé avec libintl)
    $C_glob['lc.message']		= 'fr_FR.UTF-8';
    $C_glob['crypt_function'] = 'sha1';

    # parametre db
    $C_glob_Private['db'] = array(
        0 => array(
            'host'	=> '10.0.215.178',
            'user'	=> 'root',
            'pass'	=> 'test',
            'base'	=> 'test_4p',
            'id'	=> 'fp',
            'type'	=> 'mysql'
        )
    ); #! tableau avec tout les paramètres des db mysql

    $_SERVER['HTTP_HOST']   = '127.0.0.1';
    $_SERVER['domain']      = 'test.com';
    $_SERVER['SERVER_PORT'] = 80;
    $_SERVER["SERVER_NAME"] = 'test';
            
    $C_glob['charset'] = 'UTF-8';
    $C_glob['domain'] = $_SERVER['HTTP_HOST'];					#! domain (pour les cookies)

    # chemin url
    $C_glob['protocol'] = 'http';
    if ( $_SERVER['SERVER_PORT'] == 443 ) $C_glob['protocol'] = 'https';

    # chemin url
    $C_glob['url'] = $C_glob['protocol'].'://'.$_SERVER['domain'];      # url complete sans / à la fin
    $C_glob['url_relative'] 	 = '';			             # url relative du site a la racine du serveur
    $C_glob['url_static']        = $C_glob['url'].'/1_static';
    $C_glob['url_media']         = $C_glob['url'].'/1_media';
    $C_glob['url_static_core']   = $C_glob['url'].'/4p/1_static';

    # chemin serveur
    $C_glob['dir_data']   = dirname(__FILE__).'/../';
    $C_glob['dir']        = $C_glob['dir_data'].''; # racine  du site sur le serveur
    $C_glob['dir_tpl']    = $C_glob['dir_data'].'template/';
    $C_glob['dir_lib']	    = $C_glob['dir'].'';
    $C_glob['dir_cache']    = $C_glob['dir'].'cache/';
    $C_glob['dir_media']    = $C_glob['dir'].'media/';
    $C_glob['dir_module']   = array(
            $C_glob['dir'].'Module/',
            $C_glob['dir_data'].''
    );
    $C_glob['dir_class']    = $C_glob['dir_data'].'class/';

    # Table Sql
    $C_glob['prefix'] = '';
    $C_glob['table_prefix'] = $C_glob['prefix'];
    $C_glob['table_media'] =  $C_glob['table_prefix'].'media';

    # E-mail
    $C_glob['mail_name']  = 'Robot '.$C_glob['domain'];	   #! nom d'expediteur des mails envoyés par le site
    $C_glob['mail_from']  = 'postmaster@'.$C_glob['domain'];  #! addresse mail d'envoi
    $C_glob['mail_reply'] = 'postmaster@'.$C_glob['domain'];  #! addresse mail de réponse

    # surchage config pour dev
    if ( strstr($_SERVER["SERVER_NAME"],'beta.') ) {
        require('config_beta.php');
    }
    else if ( strstr($_SERVER["SERVER_NAME"],'dev.') ) {
        require('config_dev.php');
    }
    else if ( strstr($_SERVER["SERVER_NAME"],'127.0.0.1')) {
        require('config_dev.php');
    }
    else if ( strstr($_SERVER["SERVER_NAME"],'192.168.56')) {
        require('config_dev_virtualbox.php');
    }
    else if ( strstr($_SERVER["SERVER_NAME"],'192.168.')) {
        require('config_dev.php');
    }


    $C_glob['jquery']          = $C_glob['url_static_core'].'/jquery/jquery-ui-1.9.1.custom/js/jquery-1.8.3.min.js';
    $C_glob['jquery_ui']       = $C_glob['url_static_core'].'/jquery/jquery-ui-1.9.1.custom/js/jquery-ui-1.9.1.custom.min.js';
    $C_glob['jquery_ui_style'] = $C_glob['url_static_core'].'/jquery/jquery-ui-1.9.1.custom/css/smoothness/jquery-ui-1.9.1.custom.min.css';

    $C_glob['html_titre']			 = 'titre';
    $C_glob['html_meta_description'] = 'description';

    # les blocks de templates
    $C_glob['block_error'] 		= 'central'; #! nom du block ou vont s'afficher les erreurs 404,500 & cie

    $C_glob['block_central_1'] 	= 'central'; #! nom du block ou vont s'afficher les contenus principaux (module)
    $C_glob['block_side_1'] 	= 'block_side_1'; #! nom du block ou vont s'afficher les contenus secondaire (module)
    $C_glob['block_side_2'] 	= 'block_side_2'; #! nom du block ou vont s'afficher les contenus secondaire (module)


    ##################################################################################
    /*
    * 			MODULES CONFIG
    */

    // les points d'entrée autorisé pour les modules
    $C_glob['module_entry'] = array();

    # media manager
    //$C_glob['url_theme_mediaManager'] = $C_glob['url_static_core'].'//themes/4p';
    $C_glob['allowedFiletype']    = array('video','image');
    $C_glob['allowedFileSubtype'] = array('acrobat','x-pdf','pdf','zip','x-zip','x-winzip','x-zip-compressed','tar','x-tar','x-gzip','x-gzip-compressed','jpg','png','jpeg','gif');

    /*
     *  FACEBOOK CONFIG
    */
    $C_glob['facebook_api_key'] 	= 	'apikey';
    $C_glob_Private['facebook_api_secret']	=   '';
    $C_glob['facebook_url_canvas']	=   $C_glob['protocol'].'://apps.facebook.com/canvas';
    $C_glob['facebook_app_id']		=   $C_glob['facebook_api_key'];

    /*
     * Google API KEY
     */
    $C_glob['google_browser_api_key'] = 'apikey';

    require_once $C_glob['dir_lib'].'init_set.php';
    require_once $C_glob['dir_data'].'tests/init.php';
    $O = new init($C_glob, $C_glob_Private);
}  catch (Exception $e_init) {
    die('internal error');
}

