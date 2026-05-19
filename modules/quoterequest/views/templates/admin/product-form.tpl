{*
* 2007-2020 Amazzing
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright 2007-2020 Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}

{$full = isset($t.id_quote_product)}
<div class="filter af_template{if isset($t.id_quote_product)} open{/if}" data-id="{$t.id_quote_product|intval}"
	id="{$t.id_quote_product|intval}" data-controller="category">
	<form class="template-form form-horizontal">
		<div class="template_header clearfix">
			<div class="template-name">
				<h4 class="list-view inline-block">{$t.product_name|escape:'html':'UTF-8'} [#{$t.id_quote_product}]</h4>
				<div class="open-view table-responsive">

					<table class="table nil-table" id="product_review_table">
						<thead>
							<tr>
								<th style="width: 15%;">Photo</th>
								<th style="width: 35%;">Product Details</th>
								<th style="width: 40%;">Product configuration</th>
								<th style="width: 10%;">Price</th>
								<th style="width: 10%;">Files</th>
							</tr>
						</thead>
						<tbody id="product_review_table_body">
							<tr>
								<td>
									<div class="product-img">
										<img src="{$t.product_image|escape:'html':'UTF-8'}">

									</div>
								</td>
								<td>
									<div class="product-row">
										<div class="product-heading">Product </div>
										<div class="product-details">: {$t.product_name|escape:'html':'UTF-8'} </div>
									</div>
									<div class="product-row">
										<div class="product-heading">Production</div>
										<div class="product-details production">: {$t.production} day</div>
									</div>
									<div class="product-row">
										<div class="product-heading">Weight </div>
										<div class="product-details weight">: {$t.weight} kg</div>
									</div>
								</td>
								<td class="additional-info">
									{foreach $t.customizations as $c}
										{if isset($c->additional_information) && $c->additional_information != ''}
											<div class="product-row">
												<div class="product-heading">{l s='Additional information' mod='quoterequest'}
												</div>
												<div class="product-details">: {$c->additional_information}</div>
											</div>
										{else}
											<div class="product-row">
												<div class="product-heading">{$c->variable_name}</div>
												<div class="product-details">: {$c->valueText}</div>
											</div>
										{/if}
									{/foreach}
								</td>
								<td>
									<a href="#" class="btn btn-outline-danger btn-sm price-button"
										data-product_id="0">{$t.price_formted}</a>
								</td>
								<td>
									<div class="product-img">
										{foreach $t.file as $file}
											{if $file|regex_replace:'/.*\.(.*)/':'\1'|lower == 'pdf'}
												<a href="{$smarty.const._PS_BASE_URL_}{$smarty.const.__PS_BASE_URI__}modules/quoterequest/uploads/{$file|basename}" target="_blank">
													{$file|basename}
												</a>
											{else}
												<img src="{$file}" alt="Product Image">
											{/if}
										{/foreach}
									</div>
								</td>

							</tr>
						</tbody>
					</table>

				</div>
			</div>
			<div class="template-actions pull-right">

				<div class="btn-group pull-right">
					<a href="#" title="{l s='Edit' mod='quoterequest'}" class="editTemplate btn btn-default">
						<i class="icon icon-pencil"></i> {l s='Edit' mod='quoterequest'}
					</a>
					<a href="#" title="{l s='Scroll Up' mod='quoterequest'}" class="scrollUp btn btn-default">
						<i class="icon icon-minus"></i> {l s='Scroll Up' mod='quoterequest'}
					</a>
					<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						<i class="icon-caret-down"></i>
					</button>
					<ul class="dropdown-menu">
						<li><a chref="#" class="template-action" data-action="Delete">
								<i class="icon icon-trash"></i> {l s='Delete' mod='quoterequest'}
							</a></li>
					</ul>

				</div>
			</div>
		</div>
		<div class="template_settings clearfix">
			

				<div class="controller-settings clearfix">
					<div class="settings-item compact-option hidden-on-0">
						<label class="settings-label">
							<span>
								{l s='Price' mod='quoterequest'}
							</span>
						</label>
						<div class="settings-input unlocked">
							<input type="number" name="price" value="{$t.price|escape:'html':'UTF-8'}">
						</div>
					</div>
				</div>

				<div class="controller-settings clearfix">
					<div class="settings-item compact-option hidden-on-0">
						<label class="settings-label">
							<span>
								{l s='Production Days' mod='quoterequest'}
							</span>
						</label>
						<div class="settings-input unlocked">
							<input type="number" name="production" value="{$t.production|escape:'html':'UTF-8'}">
						</div>
					</div>
				</div>

				<div class="controller-settings clearfix">
					<div class="settings-item compact-option hidden-on-0">
						<label class="settings-label">
							<span>
								{l s='Weight' mod='quoterequest'}
							</span>
						</label>
						<div class="settings-input unlocked">
							<input type="number" name="weight" value="{$t.weight|escape:'html':'UTF-8'}">
						</div>
					</div>
				</div>



				<div class="tempate-footer clear-both">
					<input type="hidden" name="id_quote_product" value="{$t.id_quote_product|intval}">
					<input type="hidden" name="id_product" value="{$t.id_product|intval}">

					<button type="button" name="saveTemplate" class="saveTemplate btn btn-default">
						<i class="process-icon-save"></i>
						{l s='Save' mod='quoterequest'}
					</button>
				</div>
		
		</div>
	</form>
</div>
{* since 2.8.2 *}