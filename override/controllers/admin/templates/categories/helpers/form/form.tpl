{**
	* overried_by_hinh_ets
	 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 *}

{extends file="helpers/form/form.tpl"}

{block name="script"}
    var ps_force_friendly_product = false;
{/block}
{block name="fieldset"}
    <div class="tab-content ets_seo_categories">
        <ul class="nav nav-tabs ets_seo_extra_tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#ets_seo_content_tabs" class="js-ets-seo-tab-customize" data-tab="js-ets-seo-tab-content"
                   role="tab" data-toggle="tab">{l s='Content' mod='ets_seo'}</a>
            </li>
            <li role="presentation">
                <a href="#ets_seo_setting_tabs" class="js-ets-seo-tab-customize" data-tab="js-ets-seo-tab-setting"
                   role="tab" data-toggle="tab">{l s='Seo settings' mod='ets_seo'}</a>
            </li>
            <li role="presentation">
                <a href="#ets_seo_analysis_tabs" class="js-ets-seo-tab-customize" data-tab="js-ets-seo-tab-analysis"
                   role="tab" data-toggle="tab">{l s='Seo analysis' mod='ets_seo'}</a>
            </li>
        </ul>
        {$smarty.block.parent}
        <div class="ets-seo-right-column col-lg-3">
            {if isset($ets_seo_preview_analysis)}
                {$ets_seo_preview_analysis nofilter}
            {/if}
        </div>
    </div>
{/block}
{block name="legend"}
    <div class="panel-heading">&nbsp;</div>
{/block}
{block name="input_row"}
    <div class="row">
        <div class="col-lg-9">
            {if $input.name == 'meta_title'}
                <div class="ets-seo-meta-data ets-seo-customize-item js-ets-seo-customize-item js-ets-seo-tab-setting">
                    <h3>{l s='Search Engine Optimization' mod='ets_seo'}</h3>
                    <p class="meta-data-desc">{l s='Improve your ranking and how your product page will appear in search engines results.' mod='ets_seo' }</p>
                </div>
            {/if}
            <div class="ets-seo-customize-item js-ets-seo-customize-item js-ets-seo-tab-{if $input.name == 'meta_title' || $input.name == 'meta_description' || $input.name == 'meta_keywords' || $input.name == 'link_rewrite'}setting{else}content active{/if}">
                {$smarty.block.parent}
                {if ($input.name == 'thumbnail')}
                    {$displayBackOfficeCategory nofilter}
                {/if}
            </div>
        </div>
    </div>
{/block}
{block name="input"}
    {if $input.name == "link_rewrite"}
        <script type="text/javascript">
            {if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}
            var PS_ALLOW_ACCENTED_CHARS_URL = 1;
            {else}
            var PS_ALLOW_ACCENTED_CHARS_URL = 0;
            {/if}
        </script>
        {$smarty.block.parent}
    {elseif $input.type == 'textarea'}
        <div class="form-group">
            {assign var=use_textarea_autosize value=true}
            {if isset($input.lang) AND $input.lang}
            {foreach $languages as $language}
            {if $languages|count > 1}
                <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}"{if $language.id_lang != $defaultFormLanguage} style="display:none;"{/if}>
                <div class="col-lg-9">
            {/if}
            {if isset($input.maxchar) && $input.maxchar}
                <div class="input-group">
                <span id="{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}{/if}_counter"
                      class="input-group-addon">
								<span class="text-count-down">{$input.maxchar|intval}</span>
							</span>
            {/if}
                <textarea{if isset($input.readonly) && $input.readonly} readonly="readonly"{/if} name="{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}"
                                                                                                 id="{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}{/if}_{$language.id_lang|escape:'html':'UTF-8'}"
                                                                                                 class="{if isset($input.autoload_rte) && $input.autoload_rte}rte autoload_rte{else}textarea-autosize{/if}{if isset($input.class)} {$input.class|escape:'html':'UTF-8'}{/if}"{if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval|escape:'html':'UTF-8'}"{/if}{if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval|escape:'html':'UTF-8'}"{/if}>{$fields_value[$input.name][$language.id_lang]|escape:'html':'UTF-8'}</textarea>
            {if isset($input.maxchar) && $input.maxchar}
                </div>
            {/if}
            {if $languages|count > 1}
                </div>
                <div class="col-lg-2">
                    <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                        {$language.iso_code|escape:'html':'UTF-8'}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        {foreach from=$languages item=language}
                            <li>
                                <a href="javascript:hideOtherLanguage({$language.id_lang|escape:'html':'UTF-8'});"
                                   tabindex="-1">{$language.name|escape:'html':'UTF-8'}</a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
                </div>
            {/if}
            {/foreach}
            {if isset($input.maxchar) && $input.maxchar}
                <script type="text/javascript">
                    $(document).ready(function () {
                        {foreach from=$languages item=language}
                        countDown($("#{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}{/if}"), $("#{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}{/if}_counter"));
                        {/foreach}
                    });
                </script>
            {/if}
            {else}
            {if isset($input.maxchar) && $input.maxchar}
                <div class="input-group">{/if}
                    {if isset($input.maxchar) && $input.maxchar}
                        <span id="{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}{/if}_counter"
                              class="input-group-addon">
					<span class="text-count-down">{$input.maxchar|intval|escape:'html':'UTF-8'}</span>
				</span>
                    {/if}
                    <textarea{if isset($input.readonly) && $input.readonly} readonly="readonly"{/if} name="{$input.name|escape:'html':'UTF-8'}"
                                                                                                     id="{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}{/if}" {if isset($input.cols)}cols="{$input.cols|escape:'html':'UTF-8'}"{/if} {if isset($input.rows)}rows="{$input.rows|escape:'html':'UTF-8'}"{/if} class="{if isset($input.autoload_rte) && $input.autoload_rte|escape:'html':'UTF-8'}rte autoload_rte{else}textarea-autosize{/if}{if isset($input.class)} {$input.class|escape:'html':'UTF-8'}{/if}"{if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval|escape:'html':'UTF-8'}"{/if}{if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval|escape:'html':'UTF-8'}"{/if}>{$fields_value[$input.name]|escape:'html':'UTF-8'}</textarea>
                    {if isset($input.maxchar) && $input.maxchar}
                        <script type="text/javascript">
                            $(document).ready(function () {
                                countDown($("#{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}{/if}"), $("#{if isset($input.id)}{$input.id|escape:'html':'UTF-8'}{else}{$input.name|escape:'html':'UTF-8'}{/if}_counter"));
                            });
                        </script>
                    {/if}
                    {if isset($input.maxchar) && $input.maxchar}</div>
            {/if}
            {/if}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}

    {if isset($ets_seo_meta_codes)}
        {$ets_seo_meta_codes nofilter}
    {/if}
    {if ($input.name == 'active')}
        <div class="col-lg-12">
            <div class="help-block">
                {l
                s='If you want a category to appear in the menu of your shop, go to [1]Modules > Modules & Services > Installed modules.[/1] Then, configure your menu module.'
                sprintf=[
                '[1]' => "<a href=\"{$link->getAdminLink('AdminModulesSf') nofilter}\" class=\"_blank\">",
                '[/1]' => '</a>'
                ]
                mod='ets_seo'
                }
            </div>
        </div>
    {/if}
    {if in_array($input.name, ['image', 'thumb'])}
        <div class="col-lg-6">
            <div class="help-block">{l s='Recommended dimensions (for the default theme): %1spx x %2spx' sprintf=[$input.format.width, $input.format.height] mod='ets_seo'}
            </div>
        </div>
    {/if}
{/block}
{block name="description"}
    {$smarty.block.parent}
    {if ($input.name == 'groupBox')}
        <div class="alert alert-info">
            <h4>{$input.info_introduction|escape:'html':'UTF-8'}</h4>
            <p>{$input.unidentified|escape:'html':'UTF-8'}<br/>
                {$input.guest|escape:'html':'UTF-8'}<br/>
                {$input.customer|escape:'html':'UTF-8'}</p>
        </div>
    {/if}
{/block}
{block name="footer"}
    <div class="ets-seo-customize-item js-ets-seo-customize-item js-ets-seo-tab-setting">
        <div class="row">
            <div class="col-lg-9">
                {$ets_category_seo_setting_html nofilter}
            </div>
        </div>
    </div>
    <div class="ets-seo-customize-item js-ets-seo-customize-item js-ets-seo-tab-analysis">
        <div class="form-wrapper">
            <div class="row">
                <div class="col-lg-9">
                    {$ets_category_seo_analysis_html nofilter}
                </div>
            </div>
        </div>
    </div>
    {$smarty.block.parent}
{/block}





