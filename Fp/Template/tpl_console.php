<?php
use \Fp\Template\TemplateConsole;
if( !$O->tpl()->processing() ) return;
?>
<style type="text/css">
#console-4p {
	padding: 15px !important;
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
	font-size: 14px;
}

#console-4p div.console-line {
	padding: 10px;
	margin: 5px;
	color: #666;
	background-color: #EEFDFF;
	border-top: 1px #333 dashed;
}

#console-4p div.console-line h1 {
	font-size: 16px;
	color: #FF0000;
}

#console-4p div.console-line h1:HOVER {
	background-color: #E8FA81;
}
#console-4p div.console-line>pre {
	background-color: #EBF5DE;
	padding: 20px;
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
	float:left;	
	color:#666;
}
#console-4p span.console-label {	
	min-width:10%; 
	float:left;
	padding-right:4px;
	color: #000;
}
#console-4p .spoiler {}

#console-4p ul {
	padding-left: 10px; 
	border-left: #ccc 1px dotted;
	clear: both;
}
#console-4p li {
	border-top: #ccc 0x dotted;
	background-color: #EEFDFF;
	clear: both;
	max-height: 16px;		
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
	font-size: 10px;
}
</style>
<script type="text/javascript">
$(function(){
	// fix for jquery mobile
	// @todo orientationchange
	if ( $('.ui-page-active').length ) {			
		$('#console-4p').css('top',$('.ui-page-active')
				.height()+'px')
				.css('position','relative');
		//$(document).bind('pagechange', function(ev,o){			 
		//	 $('#console-4p').replaceWith($(o.toPage).find('#console-4p'));
		//});
	}
	
	$('.console-line h1', $('#console-4p')).click(function() {
		$(this).parent().find('.spoiler').toggle();
	});

	$('.spoiler', $('#console-4p')).not(':first').toggle();

	$('#console-4p li').on('click', function(ev) {
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
	<?php foreach ( $A->block as $v ) {	?>
	<div class="console-line">
		<h1><?php echo $v['name']?></h1>		
		<div> <?php echo $v['tplfile'] ?></div> 		
		<div  class="spoiler"><?php echo TemplateConsole::T_dump($v->value()); ?></div>
		<div> <?php echo " {$v['duration']} / {$v['memory']} / {$v['memory_peak']} " ?></div>
	</div>
	<?php } ?>
	<div class="console-line">
		<h1>statistique</h1>
		<div>				
			total_time: <?php echo $A->stats['total_time'] ?> <br />
			memoire: <?php echo $A->stats['memory'] ?>  <br />
			memoire peak: <?php echo $A->stats['memory_peak'] ?>  <br />
		</div>
	</div>
</div>
