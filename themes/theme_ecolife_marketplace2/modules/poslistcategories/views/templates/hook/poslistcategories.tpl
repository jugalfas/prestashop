{*
* 2007-2015 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="poslistcategories">
	<div class="pos_title">
	<h2>{l s='Popular Categories' d='Shop.Theme.Catalog'}</h2>
	</div>
	<div class="row pos_content">
		{$count=0}
		{foreach from=$categories item=category name=myLoop}

			<div class="item-product">
				<a href="{$link->getCategoryLink($category['id_category'])}" target="_self">
					<div class="img_block">
						<img src="{$link->getMediaLink("`$smarty.const._MODULE_DIR_`poslistcategories/images/`$category.image|escape:'htmlall':'UTF-8'`")}" alt="" />
					</div>
					<div class="product_desc-wrapper">
						<div class="inner_desc">
							<h3 itemprop="name">{$category.category_name}</h3>
							<div class="product-desc" itemprop="description">
							{$category.description|cleanHtml nofilter}
							</div>
						</div>
					</div>
				</a>
			</div>
			{$count= $count+1}
		{/foreach}		
	</div>
</div>