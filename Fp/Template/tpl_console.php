<html>
<?php
use \Fp\Template\TemplateConsole;
?>
<head>
<base target="_parent" />

<style type="text/css">
body {
        font-size: 12px;
}

#console-4p {
	padding: 1em !important;
	background-color: #fff !important;	
	clear:both !important;
	margin: 0 !important;
	padding: 0 !important;
}

#console-4p * {
	color: #000;
	font-family: Verdana, sans-serif;	
	margin: 0;
	padding: 0;
	font-size: 1em;
}

#console-4p div.console-line {
	padding: 0.2em;
	margin: 0.2em;
	color: #666;
	background-color: #EEFDFF;
        clear: both;
}

#console-4p div.console-line hr {
    border: 0px;
    border-top: 1px #999 dashed;
    height: 0px;
    margin-top: 8px;
}


#console-4p div.console-line h1 {
	font-size: 1.2em;
	color: #FF0000;
}

#console-4p div.console-line h1:HOVER {
	background-color: #E8FA81;
}
#console-4p div.console-line>pre {
	background-color: #EBF5DE;
	padding: 1.4em;
}

#console-4p div.console-block { 
	width: 100%;
	background-color: #eee;
	margin: 0px;
	padding: 0px;
}

#console-4p pre.console-data { 
	width:80%;	
	display: block;
	color:#666;
}
#console-4p span.console-label {	
	min-width:10%; 
	float:left;
	padding-right:0.5em;
	color: #000;
}
#console-4p .spoiler {
    display: none;
    clear: both;
}
#console-4p .spoiler.show {
    display: block;
}

#console-4p ul {
	padding-left: 10px; 
	border-left: #ccc 1px dotted;
	clear: both;
}
#console-4p li {	
	background-color: #EEFDFF;
	clear: both;
	max-height: 1em;		
	overflow: hidden;
	position: relative;
	padding-left: 20px;
	cursor: pointer;
}

#console-4p li:AFTER {
	content: '.';
	font-size:0px;
	clear: both;
}

#console-4p li:HOVER {
	background-color: #EEFD00;
}

#console-4p li.open {
	max-height: none;
	background-color: #F1FFA5;
}

#console-4p li:BEFORE {
	content: '->';
	color: #999;
	position: absolute;
	left: 0px;	
	font-size: 0.8em;
}
</style>
</head>
<body id="body-console-4p">
<div id="console-4p" >
	<hr style="clear: both;" />
	<a style="color: #FF1119;" href="./?console=off&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']) ?>">Désactiver</a> | 	
	<a style="color: #FF1119;" href="./?console=cache_reset&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']) ?>">Vider le cache</a> |
	<?php if( !$O->glob('cache') )  {?>
	    <a style="color: #FF1119;" href="./?console=cache_stop&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']) ?>">désactiver le cache</a> |
	<?php } else {?>
	    <a style="color: #FF1119;" href="./?console=cache_start&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']) ?>">activer le cache</a> |
	<?php } ?>
	<a style="color: #FF1119;" href="<?php $G->url->e() ?>/mod/FpModule%5CCrud%5CModule">Crud</a> |
	<a style="color: #FF1119;" href="<?php $G->url->e() ?>/mod/FpModule%5CWebservice%5CModule">WebService</a>
		<div class="console-line">
		<h1>Log</h1>
		<div class="spoiler"><?php echo TemplateConsole::T_dumpLog($A->log) ?></div>
	</div>
	<div class="console-line">
		<h1>SQL</h1>
		<div class="spoiler"><?php echo TemplateConsole::T_dumpSql($A->logSql) ?></div>
	</div>
	<div class="console-line">
		<h1>$_GET</h1>
		<div class="spoiler"><?php echo TemplateConsole::V_dump($_GET) ?></div>
	</div>
	<div class="console-line">
		<h1>$_POST</h1>
		<div class="spoiler"><?php echo TemplateConsole::V_dump($_POST) ?></div>
	</div>
	<div class="console-line">
		<h1>$_COOKIE</h1>
		<div class="spoiler"><?php echo TemplateConsole::V_dump($_COOKIE) ?></div>
	</div>
	<div class="console-line">
		<h1>$_SESSION</h1>
		<div class="spoiler"><?php echo TemplateConsole::V_dump($_SESSION) ?></div>
	</div>
	<div class="console-line">
		<h1>$G</h1>
		<div class="spoiler"><?php echo TemplateConsole::V_dump($G->toArray()) ?></div>
	</div>
        
        <h1>Template Block</h1>
	<?php foreach ( $A->block as $v ) { ?>         
	<div class="console-line">
		<h1><?php echo $v['name']?></h1>		
		<div><?php echo $v['tplfile'] ?></div> 		
		<div  class="spoiler"><?php echo TemplateConsole::T_dump($v); ?></div>
		<div> <?php echo " {$v['duration']} / {$v['memory']} / {$v['memory_peak']} " ?></div>
                <hr style="clear: both;" />
	</div>
	<?php } ?>
        
        <h1>Data Block</h1>
        <?php foreach ( $A->data as $v ) { ?>           
            <div class="console-line">
                    <h1><?php echo $v['name']?></h1>		
                    <div> <?php echo $v['current_file'] ?></div> 		
                    <div  class="spoiler"><?php echo TemplateConsole::T_dump($v); ?></div>                   
                    <hr style="clear: both;" />
            </div>
	<?php } ?>
                
        <h1>Parsed Block</h1>
        <?php 

        foreach ( $A->parsed as $v ) {?>            
            <div class="console-line">
                    <h1><?php echo $v['name']?> --</h1>		
                    <div>file: <?php echo $v['file'] ?></div> 	
                    <div>in: <?php echo $v['current_file'] ?></div> 		
                    <div  class="spoiler"><?php echo TemplateConsole::T_dump($v['data']);  ?></div>                   
                    <hr style="clear: both;" />
            </div>
	<?php } ?>        
        
	<div class="console-line">
		<h1>statistique</h1>
		<div>				
			total_time: <?php echo $A->stats['total_time'] ?> <br />
			memoire: <?php echo $A->stats['memory'] ?>  <br />
			memoire peak: <?php echo $A->stats['memory_peak'] ?>  <br />
		</div>
                <hr style="clear: both;" />
	</div>
</div>
<script src="<?php $G->url_static_core->e() ?>/jquery/jquery-2.0.3.min.js" ></script>
<script type="text/javascript">
$(function(){
    var $console = $('#console-4p');
    
	// fix for jquery mobile
	// @todo orientationchange
	 
	if ( $('.ui-page-active').length ) {			
	    $console.css('top',$('.ui-page-active')
				.height()+'px')
				.css('position','relative');
		//$(document).bind('pagechange', function(ev,o){			 
		//	 $('#console-4p').replaceWith($(o.toPage).find('#console-4p'));
		//});
	}
	
	$('.console-line h1', $console).click(function() {
		var $spoiler = $(this).parent().find('.spoiler');
		$spoiler.toggleClass('show');
	});



	$('li', $console).on('click', function(ev) {
		ev.stopPropagation();
		var fx = {
				addOpen: function ($el) {						
					var $t = $el.parent('ul').parent('pre').parent('li').addClass('open');
					if ( $t.length ) fx.addOpen($t); 	
				}
		}; 
		if ($(this).hasClass('open') ) {
			$(this).removeClass('open');	
			if ( !$(this).siblings('li.open').length ) {
				$(this).parent('ul').parent('pre').parent('li').trigger('click');	
			}
		} 
		else {
			$(this).addClass('open');
			fx.addOpen($(this)); 			
		}
	});
	
});
</script>

</body>
</html>