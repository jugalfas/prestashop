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

<a
		class                       = "go-to-customize btn btn-primary"
		rel                         = "nofollow"
		href                        = "{$url_base}index.php?fc=module&module=productpriceconfig&controller=frontproductpriceconfig&id_product={$product.id_product|escape:'javascript'}&id_product_attribute={$product.id_product_attribute|escape:'javascript'}&id_customization={$product.id_customization|escape:'javascript'}"
		data-id-product             = "{$product.id_product|escape:'javascript'}"
		data-id-product-attribute   = "{$product.id_product_attribute|escape:'javascript'}"
		data-id-customization   	  = "{$product.id_customization|escape:'javascript'}"
>
	{if !isset($product.is_gift) || !$product.is_gift}
	Customise
	{/if}
</a>
