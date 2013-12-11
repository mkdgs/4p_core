<?php if( !$O->tpl()->processing() ) { return ; }

$O->tpl()->head()->title($A->is('titre')->v());
$O->tpl()->head()->metaDescription($A->is('extrait')->v());

$url_node_jeux_edit = $A->url_node_jeux_edit->r($A->id_node_jeux);
$url_node_jeux_delete = $A->url_node_jeux_delete->r($A->id_node_jeux);
?>

<div class="b-title">
			<h2><?php $A->is('titre')->e()?></h2>
</div>

<div class="b-nav">
	<ul>
<?php 	while ( $l = $A->page->iterate() ) {?>
		<?php if ( $l->iteratePosition() == 0 ) { ?> 
			<li data-active-on="^<?php $l->route->e();?>$" >
		<?php } else { ?>
			<li data-active-on="<?php $l->route->e();?>" >
		<?php } ?>
		<a href="./<?php $l->route->e();?>"><?php echo $l->label->v();?></a></li>		
<?php } ?>
	</ul>		
</div>

<div class="b-content">
	<?php echo $A->article->e();?>
	<div class="hr" style="clear: both;" >
			<a class="anchor" href="#a-header">Haut de page</a>	
	</div>
</div>

<script type="text/javascript">
$(function() {	
	$('.b-content li h2').on('click', function() {
		 var $el = $(this).parent();
		 if ( $el.hasClass('active') ) {	  
			$el.removeClass('active');
		 }
		 else {
			 $el.addClass('active');
		 } 
	});

	$('a.anchor').on('click', function(ev) {
		ev.preventDefault();
		$('body, html').animate({  
            scrollTop:0
        }, 'slow');
	});
});
</script>


<?php if ( $O->permission()->isInGroup('admin') ) { ?>
	<div class="do">
		<a class="edit" href="<?php echo $url_node_jeux_edit ?>"> Editer </a>
		<a class="delete" href="<?php echo $url_node_jeux_delete ?>"> Supprimer </a>
	</div>
<?php } ?>	