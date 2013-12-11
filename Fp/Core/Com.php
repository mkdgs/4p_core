<?php
namespace Fp\Core;
class Com {		
	/**
	 make an http POST request and return the response
	 */
	function http_post($url, $data=array() )	{
		$postdata = http_build_query($data);
        $opts = array('http' =>
					        array(
						        'method'  => 'POST',
						        'header'  => 'Content-type: application/x-www-form-urlencoded',
						        'content' => $postdata
					        )
        );
        $context  = stream_context_create($opts);
        return file_get_contents($url, false, $context);
	}
}