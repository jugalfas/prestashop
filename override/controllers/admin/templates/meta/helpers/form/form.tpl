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
{block name="fieldset"}
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
{/block}
{block name="legend"}
    <div class="panel-heading">&nbsp;</div>
{/block}
{block name="input_row"}
    <div class="row">
        <div class="col-lg-9">
            {if $input.name == 'description'}
                <div class="ets-seo-meta-data ets-seo-customize-item js-ets-seo-customize-item js-ets-seo-tab-setting">
                    <h3>{l s='Search Engine Optimization' mod='ets_seo'}</h3>
                    <p class="meta-data-desc">{l s='Improve your ranking and how your product page will appear in search engines results.' mod='ets_seo' }</p>
                </div>
                {if isset($ets_meta_seo_meta_title)}
                    <div class="ets-seo-customize-item js-ets-seo-customize-item js-ets-seo-tab-setting">
                        {$ets_meta_seo_meta_title nofilter}
                    </div>
                {/if}
            {/if}
            <div class="ets-seo-customize-item js-ets-seo-customize-item js-ets-seo-tab-{if $input.name == 'description' || $input.name == 'keywords' || $input.name == 'url_rewrite'}setting{else}content active{/if}">
                {$smarty.block.parent}
            </div>
        </div>
    </div>
{/block}
{* =========== ETS SEO =========*}
{block name='footer'}
    <div class="ets-seo-customize-item js-ets-seo-customize-item js-ets-seo-tab-setting">
        <div class="row">
            <div class="col-lg-9">
                {$ets_meta_seo_setting_html nofilter}
            </div>
        </div>
    </div>
    <div class="ets-seo-customize-item js-ets-seo-customize-item js-ets-seo-tab-analysis">
        <div class="form-wrapper">
            <div class="row">
                <div class="col-lg-9">
                    {$ets_meta_seo_analysis_html nofilter}
                </div>
            </div>
        </div>
    </div>
    {$smarty.block.parent}
{/block}

