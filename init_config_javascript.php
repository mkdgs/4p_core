<?php
$O = $this;
$session = $O->session();
$session->close();
$O->raw();
$O->header()->typeJs();
$glob = $O->glob();	
foreach ( $glob as $k => $v ) {
	if ( !is_scalar($v) ) 	continue;
	if ( substr($k, 0, 3) == 'dir' )   continue;
	if ( substr($k, 0, 4) == 'mail' )  continue;
	if ( substr($k, 0, 5) == 'block' ) continue;
	if ( substr($k, 0, 5) == 'table' ) continue;
	if ( substr($k, 0, 4) == 'html' )  continue;
	$g[] = "$4p.glob('$k','$v');";
}
header("Pragma:");	
header("Expires:");	
header("Last-Modified:");
header("Cache-Control:");	
header("Date:");		
header("Cache-Control: max-age=29030400, public");	
header('Content-type: application/javascript');
echo implode("\n",$g)."\n";
exit();