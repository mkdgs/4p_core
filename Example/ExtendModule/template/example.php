<?php if( !$O->tpl()->processing() ) { return ; }

$O->tpl()->head()->title($A->is('titre')->v());
$O->tpl()->head()->metaDescription($A->is('extrait')->v());

$url_node_jeux_edit = $A->url_node_jeux_edit->r($A->id_node_jeux);
$url_node_jeux_delete = $A->url_node_jeux_delete->r($A->id_node_jeux);
?>

<h2><?php $A->is('titre')->e()?></h2>


	<ul>
<?php 	while ( $l = $A->page->iterate() ) {?>
		<?php if ( $l->iteratePosition() == 0 ) { ?> 
			<li data-active-on="^<?php $l->route->e();?>$" >
		<?php } else { ?>
			<li data-active-on="<?php $l->route->e();?>" >
		<?php } ?>
		<a href="./<?php $l->route->e();?>"><?php echo $l->label->v();?></a></li>		
<?php } ?>
	</ul>		

<div>
	<?php echo $A->article->e();?>
</div>


<?php if ( $O->permission()->isInGroup('admin') ) { ?>
	<a class="edit" href="<?php echo $url_node_jeux_edit ?>"> Edit </a>
	<a class="delete" href="<?php echo $url_node_jeux_delete ?>"> Delete </a>
<?php } ?>	