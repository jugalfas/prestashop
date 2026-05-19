{*
* 2007-2023 PrestaShop
*
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
* @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $ps_version == 16 && $ps_version_17 == 0}
<li>
    <a href="{$link->getModuleLink('uploadfileonorder','default',['process'=>'summary'],true)|escape:'htmlall':'UTF-8'}" title="{l s='My files by orders' mod='uploadfileonorder'}" rel="nofollow">
        <i class="icon-file fa fa-file"></i>
        <span>{l s='My files' mod='uploadfileonorder'}</span>
    </a>
</li>
{elseif $ps_version == 16 && $ps_version_17 == 1}
<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="messages-link" href="{$link->getModuleLink('uploadfileonorder','default',['process'=>'summary'],true)|escape:'htmlall':'UTF-8'}" title="{l s='My files' mod='uploadfileonorder'}" rel="nofollow">
  <span class="link-item">
    <i class="icop-docments">&#xE2C8;</i>
    {l s='My files' mod='uploadfileonorder'}
  </span>
</a>
{else}
<li>
    <a href="{$link->getModuleLink('uploadfileonorder','default',['process'=>'summary'],true)|escape:'htmlall':'UTF-8'}" title="{l s='My files by orders' mod='uploadfileonorder'}" rel="nofollow">
            <img src="{$module_template_dir|escape:'htmlall':'UTF-8'}views/img/messages.png" alt="{l s='My files' mod='uploadfileonorder'}" class="icon" />{l s='My files' mod='uploadfileonorder'}
    </a>
</li>
{/if}