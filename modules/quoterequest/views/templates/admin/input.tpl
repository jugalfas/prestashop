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

{if empty($input_class)}{$input_class = ''}{/if}
{if !empty($field.input_class)}{$input_class = ($input_class|cat:' '|cat:$field.input_class)|trim}{/if}

{if $field.type == 'switcher'}
    {if !empty($group_identifier)}{$id = $group_identifier}{else}{$id = Tools::str2url($name)}{/if}
    <span class="switch prestashop-switch {$input_class|escape:'html':'UTF-8'}">
        <input type="radio" id="{$id|escape:'html':'UTF-8'}" name="{$name|escape:'html':'UTF-8'}" value="1"{if !empty($value)} checked{/if}{if !empty($field.blocked)} disabled{/if}>
        <label for="{$id|escape:'html':'UTF-8'}">{l s='Yes' mod='amazzingfilter'}</label>
        <input type="radio" id="{$id|escape:'html':'UTF-8'}_0" name="{$name|escape:'html':'UTF-8'}" value="0"{if empty($value)} checked{/if}{if !empty($field.blocked)} disabled{/if}>
        <label for="{$id|escape:'html':'UTF-8'}_0">{l s='No' mod='amazzingfilter'}</label>
        <a class="slide-button btn"></a>
    </span>
{else if $field.type == 'select'}
    <select class="{$input_class|escape:'html':'UTF-8'}" name="{$name|escape:'html':'UTF-8'}">
        {foreach $field.options as $i => $opt}
            <option value="{$i|escape:'html':'UTF-8'}"{if $value|cat:'' == $i} selected{/if}>{$opt|escape:'html':'UTF-8'}</option>
        {/foreach}
    </select>
{else if $field.type == 'multiple_options'}
    {include file="./options.tpl" name=$name data=$field}
{else if $field.type != 'checkbox'} {* checkbox is added inside label*}
    {$use_group = !empty($field.input_prefix) || !empty($field.input_suffix)}
    {if $use_group}<div class="input-group">
        {if !empty($field.input_prefix)}<span class="input-group-addon">{$field.input_prefix|escape:'html':'UTF-8'}</span>{/if}
    {/if}
    <input type="text" name="{$name|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}" class="{$input_class|escape:'html':'UTF-8'}">
    {if $use_group}
        {if !empty($field.input_suffix)}<span class="input-group-addon">{$field.input_suffix|escape:'html':'UTF-8'}</span>{/if}
        </div>
    {/if}
{/if}
{* since 3.0.0 *}
