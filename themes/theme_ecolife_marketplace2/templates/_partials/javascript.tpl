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
{foreach $javascript.external as $js}
  <script type="text/javascript" src="{$js.uri}" {$js.attribute}></script>
{/foreach}

{foreach $javascript.inline as $js}
  <script type="text/javascript">
    {$js.content nofilter}
  </script>
{/foreach}

{if isset($vars) && $vars|@count}
  <script type="text/javascript">
    {foreach from=$vars key=var_name item=var_value}
      var {$var_name} = {$var_value|json_encode nofilter};
    {/foreach}
  </script>
{/if}

{* First-Party Tracking - Needed on every page (safe to include here too) *}
<script type="text/javascript" src="https://t.adcell.com/js/trad.js"></script>
<script>
  Adcell.Tracking.track();
</script>
{if $page.page_name == 'index'}
  <script type="text/javascript" src="https://t.adcell.com/js/inlineretarget.js?method=track&pid=9315&type=startpage"
    async></script>

{elseif $page.page_name == 'category'}
  <script type="text/javascript"
    src="https://t.adcell.com/js/inlineretarget.js?method=category&pid=9315&categoryName={$category.name|urlencode}&categoryId={$category.id}&productIds={$productIds|@implode:','}&productSeparator=,"
    async></script>

{elseif $page.page_name == 'product'}
  <script type="text/javascript"
    src="https://t.adcell.com/js/inlineretarget.js?method=product&pid=9315&productId={$product.id_product}&productName={$product.name|urlencode}&categoryId={$product.id_category_default}&productIds={$product.id_product}&productSeparator=,"
    async></script>

{elseif $page.page_name == 'cart' || $page.page_name == 'checkout'}
  {* Retargeting - Cart *}
  {assign var=productIds value=[]}
  {assign var=quantities value=[]}
  {foreach $cart.products as $p}
    {assign var=productIds value=$productIds|@array_merge:[$p.id_product]}
    {assign var=quantities value=$quantities|@array_merge:[$p.quantity]}
  {/foreach}
  <script type="text/javascript"
    src="https://t.adcell.com/js/inlineretarget.js?method=basket&pid=9315&productIds={$productIds|@implode:','}&productSeparator=,&quantities={$quantities|@implode:','}&basketProductCount={$productIds|@count}&basketTotal={$cart.totals.total.value}"
    async></script>
{elseif $page.page_name == 'search'}
  <script type="text/javascript"
    src="https://t.adcell.com/js/inlineretarget.js?method=search&pid=9315&search={$search_string|urlencode}&productIds={$search_products|@implode:','}&productSeparator=,"
    async></script>
{elseif $page.page_name == 'order-confirmation'}

  {* Retargeting - Checkout/Purchase *}
  {assign var=productIds value=[]}
  {assign var=quantities value=[]}
  {foreach $order.products as $p}
    {assign var=productIds value=$productIds|@array_merge:[$p.id_product]}
    {assign var=quantities value=$quantities|@array_merge:[$p.quantity]}
  {/foreach}
  <script type="text/javascript" src="https://t.adcell.com/js/inlineretarget.js?method=checkout&pid=9315&basketId={$order.details.reference}&basketTotal={$order.totals.total_excluding_tax.value}&basketProductCount={$productIds|@count}&productIds={$productIds|@implode:','}&productSeparator=,&quantities={$quantities|@implode:','}" async></script>
{elseif $page.page_name == 'cms'}
    {* CMS Pages *}
    {if $cms.id == 20}
      <script type="text/javascript" src="https://t.adcell.com/js/inlineretarget.js?method=category&pid=9315" async></script>
    {/if}
  
{/if}


{literal}
  <!-- START - We recommend to place the below code in footer or bottom of your website html  -->
  <script>
    window.REQUIRED_CODE_ERROR_MESSAGE = 'Please choose a country code';
    window.LOCALE = 'en';
    window.EMAIL_INVALID_MESSAGE = window.SMS_INVALID_MESSAGE = "De ingevoerde informatie is ongeldig. ";

    window.REQUIRED_ERROR_MESSAGE = "Dit veld mag niet leeg zijn. ";

    window.GENERIC_INVALID_MESSAGE = "De ingevoerde informatie is ongeldig. ";




    window.translation = {
      common: {
        selectedList: '{quantity} list selected',
        selectedLists: '{quantity} lists selected'
      }
    };

    var AUTOHIDE = Boolean(0);
  </script>

  <script defer src="https://sibforms.com/forms/end-form/build/main.js"></script>

  <script src="https://www.google.com/recaptcha/api.js?render=6Ld9bacpAAAAALOSzoj7fZcvrhQX9KOIL-UAs5FV&hl=en" async defer>
  </script>

  <!-- END - We recommend to place the above code in footer or bottom of your website html  -->
  <!-- End Brevo Form -->
{/literal}