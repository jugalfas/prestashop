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
<div class="panel"><h3></i> {l s='Type Of case' mod='cdesigner'}
	<span class="panel-heading-action">
		<a id="desc-product-new"
		class="list-toolbar-btn" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=cdesigner&addItemType=1&category={$category|escape:'htmlall':'UTF-8'}&id_phone={$id_phone|escape:'htmlall':'UTF-8'}">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Add new Type" data-html="true">
				<i class="process-icon-new "></i>
			</span>
		</a>
	</span>
	</h3>
	<div id="itemsContent">
		<div id="items" class="cls-type">
			{foreach from=$items item=item}
				<div id="items_{$item.id_cdesigner_type|escape:'htmlall':'UTF-8'}" class="panel">
					<div class="row">
						<div class="col-lg-1">
							<span><i class="icon-arrows"></i></span>
						</div>
						<div class="col-md-3 text-center">
							<img src="{$image_baseurl|escape:'htmlall':'UTF-8'}{$item.vignette|escape:'htmlall':'UTF-8'}" alt="{$item.title|escape:'htmlall':'UTF-8'}" style="max-height:50px"/>
						</div>
						<div class="col-md-8">
							<h4 class="pull-left" style="margin-bottom:0px;">{$item.title|escape:'htmlall':'UTF-8'}</h4>
							<div class="btn-group-action pull-right">
								<a class="btn btn-default" title="Edit"
									href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=cdesigner&id_cdesigner_type={$item.id_cdesigner_type|escape:'htmlall':'UTF-8'}&category={$item.category|escape:'htmlall':'UTF-8'}&id_phone={$id_phone|escape:'htmlall':'UTF-8'}">
									<i class="icon-edit"></i>
								</a>
								<a class="btn btn-default" title="Delete"
									href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=cdesigner&delete_id_cdesigner_type={$item.id_cdesigner_type|escape:'htmlall':'UTF-8'}">
									<i class="icon-trash"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
	<div class="panel-footer">
		<a class="btn btn-default pull-right" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=cdesigner&addItemType=1&category={$category|escape:'htmlall':'UTF-8'}&id_phone={$id_phone|escape:'htmlall':'UTF-8'}"><i class="process-icon-new"></i> New Type Case</a>
	</div>
</div>