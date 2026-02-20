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

{$full = isset($variable_options)}
<div class="filter af_template{if isset($variable_options)} open{/if}" data-id="{$t.id_product_variable|intval}" id="{$t.id_product_variable|intval}" data-controller="category">
	<form class="template-form form-horizontal">
	<div class="template_header clearfix">
		<div class="template-name">
			<h4 class="list-view inline-block">{$t.variable_name|escape:'html':'UTF-8'} [{$t.formula_name}]</h4>
			<div class="open-view">
			    <label class="settings-label">
					<span>
						Public name : <input type="text" name="variable_name" value="{$t.name|escape:'html':'UTF-8'}">
					</span>
				</label>
				
			</div>
		</div>
		<div class="template-actions pull-right">
			<a class="activateTemplate list-action-enable action-{if $t.active == 1}enabled{else}disabled{/if}" href="#" title="{l s='Activate' mod='productpriceconfig'}">
				<i class="icon-check"></i>
				<i class="icon-remove"></i>
				<input type="hidden" name="active" value="{$t.active|intval}">
			</a>
			<div class="btn-group pull-right">
				<a href="#" title="{l s='Edit' mod='productpriceconfig'}" class="editTemplate btn btn-default">
					<i class="icon icon-pencil"></i> {l s='Edit' mod='productpriceconfig'}
				</a>
				<a href="#" title="{l s='Scroll Up' mod='productpriceconfig'}" class="scrollUp btn btn-default">
					<i class="icon icon-minus"></i> {l s='Scroll Up' mod='productpriceconfig'}
				</a>
				<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<i class="icon-caret-down"></i>
				</button>
				<ul class="dropdown-menu">
					<li><a chref="#" class="template-action" data-action="Delete">
						<i class="icon icon-trash"></i> {l s='Delete' mod='productpriceconfig'}
					</a></li>
				</ul>
				
			</div>
		</div>
	</div>
	<div class="template_settings clearfix" style="display:none;">
		{if $full}
		 <div class="controller-settings clearfix">
			<div class="settings-item compact-option hidden-on-0">
				<label class="settings-label">
					<span>
						{l s='Select Tooltip' mod='productpriceconfig'}
					</span>
				</label>
				<div class="settings-input unlocked">
					<select class="{$input_class|escape:'html':'UTF-8'}" name="id_variable_tooltip">
					    <option value="0">Select</option>
						{foreach $tool_tips as $i => $opt}
							<option value="{$i|escape:'html':'UTF-8'}"{if $t.id_variable_tooltip == $i} selected{/if}>{$opt|escape:'html':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>

		<div class="controller-settings clearfix">
			<div class="settings-item compact-option hidden-on-0">
				<label class="settings-label">
					<span>
						{l s='Formula name' mod='productpriceconfig'}
					</span>
				</label>
				<div class="settings-input unlocked">
					<input type="text" name="formula_name" value="{$t.formula_name|escape:'html':'UTF-8'}">
				</div>
			</div>
		</div>
		{if $t.variable_type == '5' || $t.variable_type == '4' || $t.variable_type == '1'}
			<div class="controller-settings clearfix">
				<div class="settings-item compact-option hidden-on-0">
					<label class="settings-label">
						<span>
							{l s='Minimum' mod='productpriceconfig'}
						</span>
					</label>
					<div class="settings-input unlocked">
						<input type="number" class="form-control" name="minimum" value="{$t.minimum|escape:'html':'UTF-8'}">
					</div>
				</div>
			</div>
			<div class="controller-settings clearfix">
				<div class="settings-item compact-option hidden-on-0">
					<label class="settings-label">
						<span>
							{l s='Maximum' mod='productpriceconfig'}
						</span>
					</label>
					<div class="settings-input unlocked">
						<input type="number" class="form-control" name="maximum" value="{$t.maximum|escape:'html':'UTF-8'}">
					</div>
				</div>
			</div>
		{/if}
		
		{if $t.id_variable == '3' || $t.id_variable == '2'}
			<div class="controller-settings clearfix">
				<div class="settings-item compact-option hidden-on-0">
					<label class="settings-label">
						<span>
							{l s='Multiple of' mod='productpriceconfig'}
						</span>
					</label>
					<div class="settings-input unlocked">
						<input type="number" class="form-control" name="multiplier" value="{$t.multiplier|escape:'html':'UTF-8'}">
					</div>
				</div>
			</div>
		{/if}
		
		<a href="#filters" class="template-tab-option first active">
		{l s='Options' mod='productpriceconfig'}</a>
		
		<div id="filters" class="template-tab-content clearfix first active">
			<div class="f-list sortable">
				{foreach $variable_options as $key => $option}
					{if !empty($option)}{include file="./option-form.tpl"}{/if}
				{/foreach}
			</div>
		</div>
		
		<div class="tempate-footer clear-both">
			<input type="hidden" name="id_product_variable" value="{$t.id_product_variable|intval}">
			<input type="hidden" name="id_variable" value="{$t.id_variable|intval}">
			
			<button type="button" name="saveTemplate" class="saveTemplate btn btn-default">
				<i class="process-icon-save"></i>
				{l s='Save variable' mod='productpriceconfig'}
			</button>
		</div>
		{/if}
	</div>
	</form>
</div>
{* since 2.8.2 *}
