{*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    Prestaeg <infos@presta.com>
* @copyright Prestaeg
* @version   1.0.0
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{extends file='page.tpl'}
{block name="page_content"}
<script type="text/javascript">
    var baseDir = '{$urls_site|escape:'htmlall':'UTF-8'}';
    var baseUri = '{$urls_site|escape:'htmlall':'UTF-8'}';
    var must_select_layout = "{l s='You must select the layout' mod='productcustomizer'}";
    var image_only = "{l s='You must select an image file only' mod='productcustomizer'}";
    var max_size = "{l s='Please upload a smaller image, max size is 12 MB' mod='productcustomizer'}";
    var reset_design = "{l s='You want to reset your design ?' mod='productcustomizer'}";
    var back_string = "{l s='Back' mod='productcustomizer'}";
    var ok_string = "{l s='OK' mod='productcustomizer'}";
    var ok_str = "{l s='OK' mod='productcustomizer'}";
    var crop_pic = "{l s='Crop this picture' mod='productcustomizer'}";
    var cancel_str = "{l s='Cancel' mod='productcustomizer'}";
    var delete_pic = "{l s='delete this picture' mod='productcustomizer'}";
    var on_process = "{l s='Save in process' mod='productcustomizer'}";
    var redirection = "{l s='redirection' mod='productcustomizer'}";
    var success_save = "{l s='Your design was saved with success' mod='productcustomizer'}";
    var error_save = "{l s='You must be connected for you can save your design' mod='productcustomizer'}";
</script>


<script type="text/javascript" src="{$urls_site|escape:'htmlall':'UTF-8'}themes/core.js"></script>
<script type="text/javascript" src="{$urls_site|escape:'htmlall':'UTF-8'}modules/productcustomizer/views/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="{$urls_site|escape:'htmlall':'UTF-8'}modules/productcustomizer/views/js/front.js"></script>



<div class="container">
	<div class="row" >
   <form id="register-form" action="{$urls_site|escape:'htmlall':'UTF-8'}index.php?fc=module&module=productcustomizer&controller=frontproductcustomizer" class="default-form" enctype="multipart/form-data" method="post">
	 <div id="configurator_block" class="configurator_block ">
		 <div id="register-form-tab" class="tabbed_form col-xs-9 col-sm-9">
		 		{* <div class="main-title">Customize Product</div>
		 		<div class="social-label">
		 			Signing up wis super quick. No extra passwords to remember. Don’t worry, we’d never share any of your data or post anything on your behalf
		 		</div>
		 		<div class="separator-container">
		 			<div class="separator_word">***</div>
		 		</div> *}


	<div class="step_list">

		{foreach from=$product.customizations.fields item="field" }
				<div id="step_{$field.id_variable}" class="form-line step_group">
					<label>{$field.label}:{if $field.required==1}<span class="red" style="color:red; font-size:16px;">*</span>{/if}</label>
				 {if $field.type == 'text'}
					 <div class="form-line-wrapper">
						{if $field.detail != ""}<div class="sublabel">{$field.detail}</div>{/if}
						<textarea type="text" {if $field.required==1}required="required" {/if} name="{$field.input_name}">{$field.text}</textarea>
						{if $field.note != ""}<div class="sublabel">{$field.note}</div>{/if}
				   </div>
				 {elseif $field.type == 'image'}
					 <div class="form-line-wrapper">
						{if $field.detail != ""}<div class="sublabel">{$field.detail}</div>{/if}
            <br>
            {if $field.is_customized}
              {if $field.image.small.url} <img src="{$field.image.small.url}" class="preview" width="100px;" /> {elseif $field.attachment}<a href="{$field.attachment}" >Uploaded file</a> {/if}
              {if $field.remove_image_url}<a class="remove-image" href="{$field.remove_image_url}" rel="nofollow">{l s='Remove' d='Shop.Theme.Actions'}</a>{/if}
            {else}
            <img src="" class="preview" width="100px;" />
            {/if}
							<a href="#" title="{l s='Import from your computer' mod='productcustomizer'}" onchange="priview_img($('#fileupload'), '.preview');" class="cp-btn-more-pic fileinput-button">
									<span id="cp-img-lo-w">
											<i class="fa fa-refresh fa-spin" id="cp-img-lo"></i>
											<span class="cp-loader-number"></span>
									</span>
									<i class="fa fa-picture-o"></i>
									<samp>{l s='Browse' mod='productcustomizer'}</samp>
									<input id="fileupload" type="file" name="file_{$field.id_variable}" onchange="priview_img(this, '.preview');"  multiple>
									<input type="hidden" class="cp-token" value="">
							</a>
						{if $field.note != ""}<div class="sublabel">{$field.note}</div>{/if}
					 </div>
				 {elseif $field.type == 'radio' and isset($field.options)}
					 <div class="form-line-wrapper">
						{if $field.detail != ""}<div class="sublabel">{$field.detail}</div>{/if}
						<ul id="gender-radio-btn-wrapper" class="step_options" >
							{foreach from=$field.options key=id_option item=option}
								<li id="step_option_{$field.id_variable}_{$option.id}" class="option_block option_group custom" >
									<div  class="option_img" > <img class="img-responsive" alt="{$option.label}" src="{$img_path}{$option.image}" width="100" /></div>
									<input type="radio" id="{$option.id}-option"  name="selector_{$field.id_variable}" value="{$option.id}" class="checkradio" data-price="{$option.price}"  {if $field.selected == $option.id} checked {/if} >
									<label for="{$option.id}-option">{$option.label}{if $option.price} <span>(+{$currency.sign}{$option.price})</span>{/if}</label>
									<div class="check"></div>
								</li>
							{/foreach}
						</ul>
						{if $field.note != ""}<div class="sublabel">{$field.note}</div>{/if}
					 </div>
				 {elseif $field.type == 'checkbox'}
					 <div class="form-line-wrapper">
						{if $field.detail != ""}<div class="sublabel">{$field.detail}</div>{/if}
						<div class="row">
						 <div class="col-xs-12">
							 <div class="step_options" id="collapse_{$field.id_variable}">
								 {foreach from=$field.options key=id_option item=option}
									 <div id="step_option_{$field.id_variable}_{$option.id}" class="option_block option_group custom {if isset($field.selected) and in_array($option.id, $field.selected)} selected {/if}" >
										 <div class="option_img" > <img class="img-responsive" alt="{$option.label}" src="{$img_path}{$option.image}"></div>
										 <div class="checker" id="uniform-option_{$field.id_variable}_{$option.id}" >
											 <span><input class="hidden" data-step="{$field.id_variable}" id="option_{$field.id_variable}_{$option.id}" type="checkbox" name="selector_{$field.id_variable}[]" value="{$option.id}" data-price="{$option.price}" {if isset($field.selected) and in_array($option.id, $field.selected)} checked {/if}></span>
										 </div>
										 <span>{$option.label}</span><br/>{if $option.price}<span>(+{$currency.sign}{$option.price})</span>{/if}
									 </div>
								 {/foreach}
								 <div class="clearfix">&nbsp;</div>
							 </div>
						 </div>
					 </div>
						{if $field.note != ""}<div class="sublabel">{$field.note}</div>{/if}
					 </div>
				 {elseif $field.type == 'setup_price'}
					 <div class="form-line-wrapper">
						{if $field.detail != ""}<div class="sublabel">{$field.detail}</div>{/if}
						<ul id="gender-radio-btn-wrapper">
							<li>
								<input type="radio" id="f-option" name="selector_{$field.id_variable}" class="yes_radio" data-price="{$field.fee_amount}" value="1" {if $field.selected == 1} checked {/if}>
								<label for="f-option">Yes{if $field.fee_amount}<span>(+{$currency.sign}{$field.fee_amount})</span>{/if}</label>
								<div class="check"></div>
							</li>
							<li>
								<input type="radio" id="s-option" name="selector_{$field.id_variable}" class="no_radio" data-price="{$field.fee_amount}" value="2" {if $field.selected == 2} checked {/if}>
								<label for="s-option">No</label>
								<div class="check"><div class="inside"></div></div>
							</li>
						</ul>
						{if $field.note != ""}<div class="sublabel">{$field.note}</div>{/if}
					 </div>
				 {/if}
				</div>
	  {/foreach}

</div>

</div>
<div class="col-xs-3">
	<div id="configurator_preview" class="" style="position: relative; top: 0px;">
		<div class="box tabbed_form">
			<div class="main-title">You are customising:</div>
			<hr>
			<div class="customisation_item">
			<p class="customisation_title block-subtitle">{$product.name|truncate:30:'...'}</p>
			<dl>
			<dt>Reference : </dt>
			<dd>{if isset($product.reference_to_display)}{$product.reference_to_display}{/if}</dd>

			</dl>
			<img  src="{$product.cover.bySize.large_default.url}" alt="{$product.cover.legend}" title="{$product.cover.legend}" style="width:70%;" itemprop="image">
			<div class="clear"></div>
			</div>
			<div class="price_display" >
			{if $product.show_price}<br/>
				<h5>Original price</h5>
				<p class="fee-or-price">
					<span class="price-excluding-tax-inline"><span id="configurator_item_price">{$product.price} <span class="ps">(excl. VAT) per item</span></span></span>
					<span class="price-including-tax-inline" style="display: none;"><span id="configurator_item_price">£7.87 <span class="ps">(incl. VAT) per item</span></span></span>
				</p>
			{/if}<br/>
			<h5>Customisation price</h5>
			<p class="fee-or-price">
				<span class="price-excluding-tax-inline"><span >{$currency.sign}<span id="customize_price">{$product.customize_price}</span> <span class="ps">(excl. VAT) per item</span></span></span>
        <input type="hidden" value="{$product.customize_price}" id="total_customize_price" name="total_customize_price" />
			</p><br/>
			<h5>One-off setup fee</h5>
			<p class="fee-or-price">
				<span class="price-excluding-tax-inline"><span id="configurator_setup_cost_excluding_tax">{$currency.sign}<span id="setup_price">{$product.setup_price}</span></span> <span class="ps">(excl. VAT) if you have already been charged for set-up of this logo, you will not be charged again</span></span>
        <input type="hidden" value="{$product.setup_price}" id="total_setup_price" name="total_setup_price" />
			</p>
		</div>
			<div class="buttons_container">
					<button type="submit" id="add_configurator_to_cart" name="submitCustomizedData" class="fileinput-button"> <span>Save Customisation</span> </button>
			</div>
			<br/><span style="font-size:10px; font-style: italic;">Please note we will send you a proof to approve before starting your order. You can change this at any time prior to the proof being approved.</span>
		</div>
	</div>
</div>
</div>
<input type="hidden" value="{$product.id}" name="id_product" />
<input type="hidden" value="{$product.id_product_attribute}" name="id_product_attribute" />


</form>
  </div>
</div>
<div class="confing-footer"> </div>
<!-- End Main Bloc -->
{/block}
