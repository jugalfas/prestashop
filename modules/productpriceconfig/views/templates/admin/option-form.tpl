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

<div class="filter clearfix" data-key="{$option.id_option|escape:'html':'UTF-8'}">
	<div class="f-name">
		<span class="prefix">{l s='Weergavenaam' mod='productpriceconfig'}</span>
		<span class="name" data-name="{$option.label|escape:'html':'UTF-8'}">{$option.label|escape:'html':'UTF-8'}</span>
	</div>
	
	<div class="f-quick-settings pull-right">
		<div class="inline-block type-exc not-for-4">
			<label class="inline-block">
				<span>
					{l s='Gewicht' mod='productpriceconfig'}
				</span>
			</label>
			<div class="inline-block">
				<span>{$option.weight}</span>
			</div>
		</div>


		<div class="inline-block">
			<label class="inline-block">
				<span>
					{l s='Prijs' mod='productpriceconfig'}
				</span>
			</label>
			<div class="inline-block">
				<span>{$option.price}</span>
			</div>
		</div>
		<div class="inline-block">
			<label class="inline-block checkbox-label">
			{$default_option|print_r}
				<input type="checkbox" name="options[]" value="{$option.id_option}" {if $option.selected} checked {/if}> <span>
				  {l s='Selecteer voor dit product' mod='productpriceconfig'}
				</span>
				<input type="radio" name="default_value" value="{$option.id_option}" {if $default_option == $option.id_option} checked {/if}> <span>
				  {l s='Standaard instellen' mod='productpriceconfig'}
				</span>
			</label>
			<div class="inline-block">

			</div>
		</div>
	</div>
	
</div>
{* since 2.8.0 *}
