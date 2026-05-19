
<div class="pos-featured-products" 
	data-items="{$slider_options.number_item}" 
	data-speed="{$slider_options.speed_slide}"
	data-autoplay="{$slider_options.auto_play}"
	data-time="{$slider_options.auto_time}"
	data-arrow="{$slider_options.show_arrow}"
	data-pagination="{$slider_options.show_pagination}"
	data-move="{$slider_options.move}"
	data-pausehover="{$slider_options.pausehover}"
	data-lg="{$slider_options.items_lg}"
	data-md="{$slider_options.items_md}"
	data-sm="{$slider_options.items_sm}"
	data-xs="{$slider_options.items_xs}"
	data-xxs="{$slider_options.items_xxs}">
	<div class="pos_title">
		{if $title}
		 <h2>
			{$title}
		</h2>
		{/if}
		{if $desc}
		<div class="desc_title">
			{$desc}	
		</div>
		{/if}	
	</div>
	{$rows= $slider_options.rows}
	<div class="row ">
		<div class="col-md-12 col-xs-12 col-flexbox-wrapper">
			{foreach from=$products item=product name=myLoop}
				<div class="item-product">
					<article class="style_product_default product-miniature  item_in" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
						<div class="img_block">
						  {block name='product_thumbnail'}
							<a href="{$product.url}" class="thumbnail product-thumbnail">
							  <img class="first-image "
							  src="{if !empty($product.cover.bySize.home_default.url)}{$product.cover.bySize.home_default.url}{else}{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')}{/if}" 
								alt = "{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
								data-full-size-image-url = "{$product.cover.large.url}"
							  >
							   {hook h="rotatorImg" product=$product}	
							</a>
						  {/block}
						  <!--
							<div class="quick-view">
								{block name='quick_view'}
								<a class="quick_view" href="#" data-link-action="quickview" title="{l s='Quick view' d='Shop.Theme.Actions'}">
								 <span>{l s='Quick view' d='Shop.Theme.Actions'}</span>
								</a>
								{/block}
							</div>
							-->
					
							{block name='product_flags'}
							<ul class="product-flag">
							{foreach from=$product.flags item=flag}
								<li class="{$flag.type}"><span>{$flag.label}</span></li>
							{/foreach}
							</ul>
							{/block}
						</div>
						<div class="product_desc">
							<div class="inner_desc">
								{if isset($product.id_manufacturer)}
								 <div class="manufacturer"><a href="{url entity='manufacturer' id=$product.id_manufacturer }">{Manufacturer::getnamebyid($product.id_manufacturer)}</a></div>
								{/if}
								{block name='product_name'}
								  <h3 itemprop="name"><a href="{$product.url}" class="product_name {if $postheme.name_length ==0 }one_line{/if}" title="{$product.name}">{$product.name|truncate:50:'...'}</a></h3> 
								{/block}
								{block name='product_reviews'}
									<div class="hook-reviews">
									{hook h='displayProductListReviews' product=$product}
									</div>
								{/block} 
								{block name='product_description_short'}
									<!--<div class="product-desc" itemprop="description">{$product.description_short nofilter}</div>-->
									<div class="product-desc" itemprop="description"><p>Goedkoop flyers drukken Op zoek naar de goedkoopste en snelste plek om flyers te laten drukken? Bereken zelf jouw prijs en ontdek wat de laagste prijs betekent voor het drukken van flyers.</p></div>
								{/block}
								<!-- 
								{block name='product_price_and_shipping'}
								  {if $product.show_price}
									<div class="product-price-and-shipping">
									  {if $product.has_discount}
										{hook h='displayProductPriceBlock' product=$product type="old_price"}

										<span class="sr-only">{l s='Regular price' d='Shop.Theme.Catalog'}</span>
										<span class="regular-price">{$product.regular_price}</span>
									  {/if}

									  {hook h='displayProductPriceBlock' product=$product type="before_price"}

									  <span class="sr-only">{l s='Price' d='Shop.Theme.Catalog'}</span>
									  <span itemprop="price" class="price {if $product.has_discount}price-sale{/if}">{$product.price}</span>
									  {hook h='displayProductPriceBlock' product=$product type='unit_price'}

									  {hook h='displayProductPriceBlock' product=$product type='weight'}
										{if $product.has_discount}
											{if $product.discount_type === 'percentage'}
											  <span class="discount-percentage discount-product">{$product.discount_percentage}</span>
											{elseif $product.discount_type === 'amount'}
											  <span class="discount-amount discount-product">{$product.discount_amount_to_display}</span>
											{/if}
										  {/if}
									</div>
								  {/if}
								{/block} 
								-->
								<div class="cart">
									{include file='catalog/_partials/customize/button-cart.tpl' product=$product}
								</div>
								
							</div>	
							<div class="availability"> 
							{if $product.show_availability }
								{if $product.quantity > 0}
								<div class="availability-list in-stock">{l s='Availability' d='Shop.Theme.Actions'}: <span>{$product.quantity} {l s='In Stock' d='Shop.Theme.Actions'}</span></div>

								{else}

								<div class="availability-list out-of-stock">{l s='Availability' d='Shop.Theme.Actions'}: <span>{l s='Out of stock' d='Shop.Theme.Actions'}</span></div> 
								{/if}
							{/if}
							</div>

						<!--
							<div class="variant-links">
							{block name='product_variants'}
							{if $product.main_variants}
							{include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
							{/if}
							{/block} 
							</div>
						-->
						
						</div>
					</article>
				</div>
			{/foreach}
		</div>
		</div>
	</div>
	<div class=" pos_content row hidden-xl-up">
		<div class="feature-item owl-carousel">
		{$j=0}	
		{foreach from=$products item=product name=myLoop}
			{if $j >= 1}
				{if ($smarty.foreach.myLoop.index -1) % $rows == 0 || $smarty.foreach.myLoop.first }
				<div class="item-product">
				{/if}
					{include file="catalog/_partials/miniatures/product.tpl" product=$product} 
				{if ($smarty.foreach.myLoop.iteration -1) % $rows == 0 || $smarty.foreach.myLoop.last  }
				</div>
				{/if}
			{/if}	
			{$j = $j+1} 	
		{/foreach}
		</div>
	</div>

</div>
