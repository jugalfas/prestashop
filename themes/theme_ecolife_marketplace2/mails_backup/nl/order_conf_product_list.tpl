{foreach $list as $product}
    <tr>
        <td style="padding: 10px;" width="55%">
            <p class="name-product" style="margin: 0; padding: 0 5px; font-size: 13px;">
                <span style="display: block;font-weight: bold;">{$product['name']}</span>
                {if count($product['customization']) == 1}
                    {foreach $product['customization'] as $customization}
                        <span style="display: block;">{$customization['customization_text']|strip_tags}</span>
                    {/foreach}
                {/if}
                {if $product['reference'] != ''}
                    <span style="display: block;font-weight: normal;">({$product['reference']})</span>  
                {/if}    
            </p>
            {hook h='displayProductPriceBlock' product=$product type="unit_price"}
        </td>
        <td align="center" style="padding: 10px;" width="15%">
            <p style="margin: 0; padding: 0 5px;">{$product['unit_price']}</p>
        </td>
        <td align="center" style="padding: 10px;" width="15%">
            <p style="margin: 0; padding: 0 5px;">{$product['quantity']}</p>
        </td>
        <td align="right" style="padding: 10px;" width="15%">
            <p style="margin: 0; padding: 0 5px;">{$product['price']}</p>
        </td>
    </tr>
    {if count($product['customization']) > 1}
        {foreach $product['customization'] as $customization}
            <tr>
                <td colspan="3" style="padding: 10px;">
                    <p style="margin: 0; padding: 0; font-size: 13px">{$customization['customization_text']}</p>
                </td>
                <td colspan="1" align="center" style="padding: 10px;">
                    {if count($product['customization']) > 1}
                        <p style="margin: 0; padding: 0; font-size: 13px;">{$customization['customization_quantity']}</p>
                    {/if}
                </td>
                <td style="padding: 10px;"></td>
            </tr>
        {/foreach}
    {/if}
{/foreach}
