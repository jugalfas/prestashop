{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *}


<table style="width: 100%; padding-bottom:10px;">
  <tr>
    <td style="width: 60%; text-align:left;">
      <img src="{$shop_logo}" style="width:200px;">
    </td>
    <td style="width: 40%; text-align:right; font-size:10pt;">
    <b style="font-size:16px">{l s='Invoice' d='Shop.Pdf' pdf='true'}</b><br>
    <b>{l s='Invoice Date' d='Shop.Pdf' pdf='true'}:</b> {dateFormat date=$order->invoice_date full=0}<br>
		<b>{l s='Invoice Number' d='Shop.Pdf' pdf='true'}:</b> {$title|escape:'html':'UTF-8'}<br>
		<b>{l s='Order Reference' d='Shop.Pdf' pdf='true'}:</b> {$order->getUniqReference()}<br>
		<b>{l s='Carrier' d='Shop.Pdf' pdf='true'}:</b> {$carrier->name}<br>
    </td>
  </tr>
</table>
