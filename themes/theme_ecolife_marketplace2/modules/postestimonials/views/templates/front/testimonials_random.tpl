{if $testimonials}
<div class="col-md-8 offset-md-2">
	<div class="testimonials_container">
		<div class="pos_title">
			<h2>{l s='Client testimonials' d='Shop.Theme.Catalog'}</h2>				
		</div>
		<div class=" row pos_content">
			<div class="testimonialsSlide owl-carousel">
			  {foreach from=$testimonials name=myLoop item=testimonial}
				{if $testimonial.active == 1}
					<div class="item-testimonials ">
						<div class="item">										
							<div class="content_author">
								<div class="content_test">
									<h3 class="des_title">{$testimonial.address|escape:'html':'UTF-8'}</h3>										
									<div class="des_testimonial">{$testimonial.content|escape:'html':'UTF-8'}</div>	
									<div class="des_inner">	
										<p class="des_namepost"><span>{$testimonial.name_post}</span></p>
									</div>
								</div>							
							</div>						
						</div>
					</div>
				{/if}
			  {/foreach}
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	var testi = $(".testimonialsSlide");
	testi.owlCarousel({
		autoplay :true,
		autoplayHoverPause: true,
		smartSpeed : 1000,
		nav :false,
		dots : false, 
		responsiveClass:true,
		responsive : {
		  0 : {
	          items: 1,
	      }, 
		  360 : {
	          items: 1,
	      },
	      576 : {
	          items: 1,
	      },
	      768 : {
	          items: 1,
	      },
	      992 : {
	          items:1,
	      },
		  1200 : {
	          items: 1,
	      }
		},
		//Onlyprint changes
		items: 1,
		center: true,
	});
});


</script>

 {/if}