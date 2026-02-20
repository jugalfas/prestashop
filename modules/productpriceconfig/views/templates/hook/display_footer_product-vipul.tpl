<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<div class="configure-area">
    <h2>Configure product</h2>
    <form action="#" id="kd_form">
		<span class="hidden_inputs">
				<input type="hidden" id="kd_id_product" name="id_product" value="{$id_product}">
				<input type="hidden" id="kd_id_product_setting" name="id_product_setting" value="{$product_setting->id}">
				<input type="hidden" id="kd_baned_comb" name="baned_comb" value="{$product_setting->baned_comb}">
		</span>
	{foreach $variables as $t}
		<div class="frm-section" data-id="{$t.id_product_variable|intval}" >
			<div class="left-frm-section-label">{$t.name|escape:'html':'UTF-8'}
			{if $t.id_variable_tooltip }
				<div class="tooltip"><i class="fa fa-info-circle" aria-hidden="true"></i>
				<span class="tooltiptext">{$t.tooltip_text nofilter}</span>
				</div>
			{/if}
			</div>
			<div class="right-frm-section">
				{if $t.type == 1}
				 <div class="quantity-label" >
					<div class="input-group-prepend">
						<a class="minas qty-down" id="minus-btn"><i class="fa fa-minus"></i></a>
					</div>
					<input type="number" id="qty_input" name="variable_{$t.id_product_variable|intval}" class="form-control form-control-sm number-area input-quantity-wanted" value="1" min="{$t.minimum}" max="{$t.maximum}">
					<div class="input-group-prepend">
						<a class="plus qty-up" id="plus-btn"><i class="fa fa-plus"></i></a>
					</div>
				</div>
				{elseif $t.type == 2}
				   {if count($t.options_data)}

					<a class="btn btn-select dropdown">
						<input type="hidden" class="btn-select-input" id="" name="variable_{$t.id_product_variable|intval}" value="">
						<span class="btn-select-value">Please select</span>
						<i class="fa fa-caret-down" aria-hidden="true"></i>
						<ul style="display: none;">
						    {foreach $t.options_data as $key => $option}
							  <li data-id_option="{$option.id}" data-price="{$option.price}" data-weight="{$option.weight}" >{$option.name}</li>
							{/foreach}
							
						</ul>
					</a>
					
					{/if}
				{elseif $t.type == 3}
				 <div class="fixed-label" >
					<div class="input-group-prepend">
						<button class="minas" id="minus-btn">{$t.fixed_price}</button>
					</div>
					<input type="hidden" id="qty_input" name="variable_{$t.id_product_variable|intval}" class="" value="{$t.fixed_price}">
					
				</div>

				{elseif $t.type == 4}
				 <div class="quantity-label" >
					<div class="input-group-prepend">
						<a class="minas qty-down" id="minus-btn"><i class="fa fa-minus"></i></a>
					</div>
					<input type="number" id="custom_input" name="variable_{$t.id_product_variable|intval}" class="form-control form-control-sm number-area input-quantity-wanted" value="1" min="{$t.minimum}" max="{$t.maximum}">
					<div class="input-group-prepend">
						<a class="plus qty-up" id="plus-btn"><i class="fa fa-plus"></i></a>
					</div>
				</div>
				
				
				{/if}
			</div>
		</div>

	{/foreach}
   </form>
    <div class="frm-section">
    	<div class="calculate-price"><a href="#" class="calculate_totals" ><span>Calculate Price</span></a></div>
    </div>

    <div class="frm-section">
    	<div class="left-frm-section">excl VAT</div>
    	<div class="right-frm-section-price price_wot">$0.0000</div>
    </div>

    <div class="frm-section">
    	<div class="left-frm-section">VAT</div>
    	<div class="right-frm-section-price tax">$0.0000</div>
    </div>

    <div class="frm-section no-bottom-boder">
    	<div class="left-frm-section">incl VAT</div>
    	<div class="right-frm-section-price total">$0.0000</div>
    </div>

    <div class="frm-section">
    	<div class="calculate-price-green"><a href="#" class="main-add-to-cart">In the shopping cart </a></div>
    </div>

</div>