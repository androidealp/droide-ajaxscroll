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
								<span><img src="<?=JURI::base( true ).'/'.$img_categoria->image;?>" alt=""> <a href="<?=JRoute::_('index.php?view=category&id='.$art->catslug);?>"><?=$art->category_title; ?></a></span>
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
						<span class="sobre-clube"><a href="<?=JRoute::_('index.php?view=category&id='.$art->catslug);?>">Sobre o clube</a></span>
					</div>
				</div>
			</div>
			<?php $images =  json_decode($art->images); ?>
			<?php if(isset($images->image_intro)):?>
				<a href="<?=$art->link; ?>" title="<?=$art->title;?>">
					<figure class="uk-overlay uk-overlay-hover">
							<img class="img-intro-artigo uk-overlay-scale" src="<?=JURI::base( true ).'/'.htmlspecialchars($images->image_intro); ?>" >
					</figure>
				</a>
			<?php endif; ?>
			<a class="mod-articles-category-title" title="<?=$art->title;?>" href="<?=$art->link; ?>"><?=$art->title;?></a>

			<div class="tm-article-content">
				<p><?=$art->introtext;?></p>
			</div>
			<a title="<?=$art->title;?>" href="<?=$art->link; ?>">Continue Lendo</a>

			<div class="box-links">
				<div class="content-links">
					<span class="btn_investir">					
					
						<a href="index.php?option=com_content&view=article&id=83&Itemid=264"><i class="uk-icon-trophy"></i> <span>Saiba como Investir<br> nesse clube</span></a>
						</span>
					</span>
					<ul>
						<li>
							<b:if cond='data:blog.pageType == "item"'>
										<a  class="btn-facebook" href="data:post.url"onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(location.href),
										'facebook-share-dialog',
										'width=626,height=436');
										return false;">
										  <i class="uk-icon-facebook"></i></a> 
									</b:if>
						</li>

						<li>
							<a class="btn-twitter" href="http://twitter.com/share?text=<?php echo $title; ?>" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=436,width=626');return false;"  data-counturl="https://twitter.com/" data-count="none" data-via="<?php echo $title; ?>" rel="nofollow"><i class="uk-icon-twitter"></i></a>
									<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
						</li>

						<li>
						<!-- <a  class="btn-google-plus" href="https://plus.google.com/share?url=<?php echo $permalink; ?>" onclick="javascript:window.open(this.href,
							  '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;"><i class="uk-icon-google-plus"></i></a>
 							-->
 							<a href="https://instagram.com/" target="_blank"><i class="uk-icon-instagram"></i></a>
					  	</li>
					</ul>
				</div>
			</div>
		</div>
	</div> 
<?php endforeach; ?>

</div>
