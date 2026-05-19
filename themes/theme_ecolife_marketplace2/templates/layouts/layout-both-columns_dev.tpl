{**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<!doctype html>
<html lang="{$language.iso_code}" frrrrrrr>

<head>
	{block name='head'}
		{include file='_partials/head.tpl'}
	{/block}
</head>
{if $customer}
	{assign var='groups' value=Group::getGroups(Context::getContext()->language->id)}
	{assign var='customer_groups' value=Context::getContext()->customer->getGroups()}
	{foreach from=$groups item=group}
		{$idGroup = $group.id_group}
		{if in_array($idGroup, $customer_groups)}
			{$group_name[] = $group.name}
		{/if}
	{/foreach}
{/if}

<body itemscope itemtype="http://schema.org/WebPage" id="{$page.page_name}"
	class="{$page.body_classes|classnames} {implode(' ',$group_name)}">

	{block name='hook_after_body_opening_tag'}
		{hook h='displayAfterBodyOpeningTag'}
	{/block}
	<main>
		{block name='product_activation'}
			{include file='catalog/_partials/product-activation.tpl'}
		{/block}

		<header id="header">
			{block name='header'}
				{include file='_partials/header.tpl'}
			{/block}
		</header>
		{if $page.page_name == 'index'}
			<div class=" pos_bannerslide">
				<div class=" container-fluid">
					<div class=" row">
						<div class="col-left col-sm-12 col-xl-3">
						</div>
						<div class="col-right col-xl-9 col-sm-12 position-static">
							{hook h='displayTopColumn'}
						</div>

					</div>
				</div>
			</div>
		{/if}

		{block name='notifications'}
			{include file='_partials/notifications.tpl'}
		{/block}
		{block name='breadcrumb'}
			{include file='_partials/breadcrumb.tpl'}
		{/block}
		<div id="wrapper">
			{hook h="displayWrapperTop"}
			<div class="container">
				<div class="row">
					{block name="left_column"}
						<div id="left-column" class="col-xs-12 col-sm-4 col-md-3">
							{if $page.page_name == 'product'}
								{hook h='displayLeftColumnProduct'}
							{else}
								{hook h="displayLeftColumn"}
							{/if}
						</div>
					{/block}

					{block name="content_wrapper"}
						<div id="content-wrapper" class="left-column right-column col-sm-4 col-md-6">
							{hook h="displayContentWrapperTop"}
							{block name="content"}
								<p>Hello world! This is HTML5 Boilerplate.</p>
							{/block}
							{hook h="displayContentWrapperBottom"}
						</div>
					{/block}

					{block name="right_column"}
						<div id="right-column" class="col-xs-12 col-sm-4 col-md-3">
							{if $page.page_name == 'product'}
								{hook h='displayRightColumnProduct'}
							{else}
								{hook h="displayRightColumn"}
							{/if}
						</div>
					{/block}
				</div>
				{if $page.page_name == 'product'}
					{block name='product_accessories'}
						{if $accessories}
							<section class="products-accessories clearfix related-products">
								<div class="pos_title">
									<h2>{l s='You might also like' d='Shop.Theme.Catalog'}</h2>
								</div>
								<div class="row pos_content pos-featured-products">
									<div class="col-flexbox-wrapper">
										{foreach from=$accessories item="product_accessory"}
											<div class="item-product">
												{block name='product_miniature'}
													{include file='catalog/_partials/miniatures/product.tpl' product=$product_accessory}
												{/block}
											</div>
										{/foreach}
									</div>
								</div>
							</section>
						{/if}
					{/block}
					{block name='product_footer'}
						{hook h='displayFooterProduct' product=$product category=$category}
					{/block}
				{/if}

			</div>
			{if $page.page_name == 'index'}
				<div id="home-popular-categories" class="container-fluid">
					<div class="container">
						<div class="row">
							{hook h="displayContainerbottom2"}
						</div>
					</div>
				</div>
				<div id="home-steps" class="container-fluid">
					<div class="container">
						<div class="row home-block-static">
							{hook::exec('homeOrderSteps') nofilter}
						</div>
					</div>
				</div>

				<div id="home-popular-products" class="container-fluid">
					<div class="container">
						<div class="row">
							{hook h="displayContainertop"}
						</div>
					</div>
				</div>
				<div id="home-testimonials" class="container-fluid">
					<div class="container">
						<div class="row">
							{hook h="displayContainerbottom"}
						</div>
					</div>
				</div>
				<!--
					<div id="home-seotext" class="container">
						<div class="row">
							<div class="col-md-12">
								{hook h=('HomeSeoText')}
							</div>
						</div>
					</div>
				-->
			{/if}
			{hook h="displayWrapperBottom"}


			<div class="container-fluid" id="footer-wrapper">
				<div class="container">
					<div class="row footer-top--wrapper">
						{hook::exec('footerTop') nofilter}
					</div>
				</div>
			</div>

		</div>

		<footer id="footer">
			{block name="footer"}
				{include file="_partials/footer_dev.tpl"}
			{/block}
		</footer>

	</main>
	<div class="back-top"><a href="#" class="back-top-button"></a></div>
	{block name='javascript_bottom'}
		{include file="_partials/javascript.tpl" javascript=$javascript.bottom}
	{/block}

	{block name='hook_before_body_closing_tag'}
		{hook h='displayBeforeBodyClosingTag'}
	{/block}
</body>

</html>