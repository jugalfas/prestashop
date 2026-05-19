/*
 * 2007-2017 PrestaShop
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
 *  @author    ST-themes <hellolee@gmail.com>
 *  @copyright 2007-2017 ST-themes
 *  @license   Use, by you or one client for one Prestashop instance.
 */
jQuery(function($) {
    $('#products').autocomplete(st_product_url, {
        minChars: 1,
        autoFill: true,
        max: 200,
        matchContains: true,
        mustMatch: true,
        scroll: true,
        cacheLength: 0,
        extraParams: { excludeIds: '-1' },
        formatItem: function(item) {
            if (item.length == 2) {
                return item[1] + ' - ' + item[0];
            } else {
                return '--';
            }
        }
    }).result(function(event, data, formatted) {
        if (data == null || data.length != 2)
            return false;
        var id = data[1];
        var name = data[0];
        // Clear other items, just keep the last one.
        $('#curr_products').empty().append('<li>' + name + '<a href="javascript:;" class="del_product"><img src="../img/admin/delete.gif" /></a>');
        $('input[name="id_product"]').val(id);
    });
    $('#curr_products').delegate('.del_product', 'click', function() {
        $(this).closest('li').remove();
        $('input[name="id_product"]').val('');
    });

});