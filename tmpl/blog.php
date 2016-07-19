<?php foreach ($displayData['data'] as $c => $art): ?>
	<?php 
		$categoria = JCategories::getInstance('Content')->get($art->catid)->params;
		$img_categoria = json_decode($categoria);	
	 ?>

	 <script>
	 	a = jQuery.noConflict();
	 	a(document).ready(function() {

	 		a('[data-nextefect]').each(function(i){
	 		
	 			a(this).show('slow');
	 		});

	 		//a('[data-nextefect]')
	 	});
	 </script>
	<div data-nextefect  style="display:none;" class="uk-width-medium-1-<?=$displayData['col'];?>">
		<div class="uk-panel-box-secondary">

			<div class="uk-grid uk-grid-collapse">
				<div class="uk-width-6-10">
					<div class="title-category">
						<?php if (!empty($img_categoria->image)) : ?>
								<span><img src="<?=JURI::base( true ).'/'.$img_categoria->image;?>" title="<?=$art->category_title; ?>"> <a href="<?=JRoute::_('index.php?view=category&id='.$art->catslug);?>"><?=$art->category_title; ?></a></span>
						<?php else : ?>		
							<span><a href="<?=JRoute::_('index.php?view=category&id='.$art->catslug);?>"><?=$art->category_title; ?></a></span>
						<?php endif; ?>
					</div>					
				</div>
				<div class="uk-width-4-10">
					<div class="right">
					<?php $regiao = explode(':', $art->catslug); ?>
						<?php if (isset($regiao) && $regiao[1] != 'times' ): ?>
						<?php echo (empty($img_categoria->image_alt))?$regiao[1]:$img_categoria->image_alt; ?>
						<!-- <span class="local"><?=$regiao[1]; ?> </span> -->
						<?php endif; ?>
						<span class="sobre-clube"><a href="<?=JRoute::_('index.php?view=category&id='.$art->catslug);?>">NEXTCONTENTSCROLL_LEIAME</a></span>
					</div>
				</div>
			</div>
			<?php $images =  json_decode($art->images); ?>
			<?php if(isset($images->image_intro)):?>
				<a href="<?=$art->link; ?>" title="<?=$art->title;?>">
					<figure class="uk-overlay uk-overlay-hover">
							<img class="img-intro-artigo uk-overlay-scale" title="<?=$art->title;?>" src="<?=JURI::base( true ).'/'.htmlspecialchars($images->image_intro); ?>" >
					</figure>
				</a>
			<?php endif; ?>
			<a class="mod-articles-category-title" title="<?=$art->title;?>" href="<?=$art->link; ?>"><?=$art->title;?></a>

			<div class="tm-article-content">
				<p><strong><?=strip_tags($art->introtext);?></strong></p>
			</div>
			<a title="<?=$art->title;?>" href="<?=$art->link; ?>">NEXTCONTENTSCROLL_LEIAME</a>
			
		</div>
	</div> 
<?php endforeach; ?>

</div>
