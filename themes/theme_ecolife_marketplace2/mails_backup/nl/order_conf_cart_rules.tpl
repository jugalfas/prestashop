{foreach $list as $cart_rule}
    <tr class="conf_body">
        <td colspan="3" align="left" style="padding: 10px 0;">
            <p class="name-product" style="margin: 0; padding: 0; font-weight: bold;">{$cart_rule['voucher_name']}</p>
        </td>
        <td colspan="2" align="right" style="padding: 10px 0;">
            <p style="margin: 0; padding: 0;">{$cart_rule['voucher_reduction']}</p>
        </td>
    </tr>
{/foreach}
