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
<div id="js-product-list" data-cate="{$postheme.cate_product_per_row}" data-type="{$postheme.grid_type}"
  data-list="{$postheme.cate_default_display}" class="pos-featured-products">
  <div class="row product_content grid {if isset($smarty.cookies.show_list)}list {/if}">
    <div class="col-md-12 col-xs-12 col-flexbox-wrapper">
      {foreach from=$listing.products item="product"}
        <div class="item-product">
          <article class="style_product_default product-miniature  item_in" data-id-product="{$product.id_product}"
            data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
            <div class="img_block">
              {block name='product_thumbnail'}
                <a href="{$product.url}" class="thumbnail product-thumbnail">
                  <img class="first-image "
                    src="{if !empty($product.cover.bySize.home_default.url)}{$product.cover.bySize.home_default.url}{else}{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')}{/if}"
                    alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
                    data-full-size-image-url="{$product.cover.large.url}">
                  {hook h="rotatorImg" product=$product}
                </a>
              {/block}

              {block name='product_flags'}
                <ul class="product-flag">
                  {foreach from=$product.flags item=flag}
                    <li class="{$flag.type}"><span>{$flag.label}</span></li>
                  {/foreach}
                </ul>
              {/block}
              {hook h='displayAdvancedStocksIcon' product=$product}
            </div>
            <div class="product_desc">
              <div class="inner_desc">
                {if isset($product.id_manufacturer)}
                  <div class="manufacturer"><a
                      href="{url entity='manufacturer' id=$product.id_manufacturer }">{Manufacturer::getnamebyid($product.id_manufacturer)}</a>
                  </div>
                {/if}
                {block name='product_name'}
                  <h3 itemprop="name"><a href="{$product.url}"
                      class="product_name {if $postheme.name_length ==0 }one_line{/if}"
                      title="{$product.name}">{$product.name|truncate:50:'...'}</a></h3>
                {/block}
                {block name='product_reviews'}
                  <div class="hook-reviews">
                    {hook h='displayProductListReviews' product=$product}
                  </div>
                {/block}
                {block name='product_description_short'}
                  <div class="product-desc" itemprop="description">{$product.description_short nofilter}</div>
                {/block}

                <div class="cart">
                  {include file='catalog/_partials/customize/button-cart.tpl' product=$product}
                </div>

              </div>
              <div class="availability">
                {if $product.show_availability }
                  {if $product.quantity > 0}
                    <div class="availability-list in-stock">{l s='Availability' d='Shop.Theme.Actions'}:
                      <span>{$product.quantity} {l s='In Stock' d='Shop.Theme.Actions'}</span></div>

                  {else}

                    <div class="availability-list out-of-stock">{l s='Availability' d='Shop.Theme.Actions'}:
                      <span>{l s='Out of stock' d='Shop.Theme.Actions'}</span></div>
                  {/if}
                {/if}
              </div>

            </div>
          </article>
        </div>

      {/foreach}
    </div>
  </div>

  {block name='pagination'}
    {include file='_partials/pagination.tpl' pagination=$listing.pagination}
  {/block}
</div>

<div id="js-product-list-header">
  <div id="category-description" class="text-muted">{$category.description nofilter}</div>
</div>