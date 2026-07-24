<html>
<head>
    <meta charset="UTF-8" />
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #333; }
        .offer-header { border-bottom: 2px solid #2fbad8; padding-bottom: 15px; margin-bottom: 20px; }
        .offer-header h1 { font-size: 24px; margin: 0; }
        .offer-header .reference { font-size: 14px; color: #666; }
        .offer-header .meta { font-size: 11px; color: #999; margin-top: 5px; }
        .product-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .product-table th { background: #f0f0f0; padding: 8px; text-align: left; border: 1px solid #ddd; font-size: 11px; }
        .product-table td { padding: 8px; border: 1px solid #ddd; font-size: 11px; vertical-align: top; }
        .product-table .total-row td { font-weight: bold; background: #f9f9f9; }
        .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #999; text-align: center; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .status-accepted { background: #d4edda; color: #155724; }
        .status-offered { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-waiting { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="offer-header">
        {if file_exists($shop_logo)}
            <img src="{$shop_logo}" alt="{$shop_name}" style="max-height: 50px; margin-bottom: 10px;" />
        {/if}
        <h1>{$shop_name}</h1>
        <div class="reference">
            <strong>{l s='Quotation' mod='productpriceconfig'}: {$offer.reference}</strong>
        </div>
        <div class="meta">
            {l s='Date' mod='productpriceconfig'}: {$date_today}
            {if $offer.expire_date} | {l s='Valid Until' mod='productpriceconfig'}: {$offer.expire_date}{/if}
        </div>
        <div class="meta">
            {l s='Customer' mod='productpriceconfig'}: {$customer_name} ({$customer_email})
        </div>
    </div>

    {if $offer.title}
        <h2>{$offer.title}</h2>
    {/if}

    <table class="product-table">
        <thead>
            <tr>
                <th>{l s='Product' mod='productpriceconfig'}</th>
                <th>{l s='Reference' mod='productpriceconfig'}</th>
                <th>{l s='Qty' mod='productpriceconfig'}</th>
                <th>{l s='Configuration' mod='productpriceconfig'}</th>
                <th>{l s='Weight' mod='productpriceconfig'}</th>
                <th>{l s='Production' mod='productpriceconfig'}</th>
                <th>{l s='Price' mod='productpriceconfig'}</th>
                <th>{l s='Total' mod='productpriceconfig'}</th>
                <th>{l s='Status' mod='productpriceconfig'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$products item=p}
                <tr>
                    <td>{$p.name}</td>
                    <td>{$p.reference}</td>
                    <td>{$p.quantity}</td>
                    <td>{$p.configuration}</td>
                    <td>{$p.weight}</td>
                    <td>{$p.production_time}</td>
                    <td>{$p.offered_price}</td>
                    <td>{$p.line_total}</td>
                    <td><span class="status-badge status-{if $p.status == 'Accepted'}accepted{elseif $p.status == 'Price Offered'}offered{elseif $p.status == 'Rejected'}rejected{else}waiting{/if}">{$p.status}</span></td>
                </tr>
            {/foreach}
            <tr class="total-row">
                <td colspan="7" style="text-align: right;">{l s='Total' mod='productpriceconfig'}:</td>
                <td colspan="2">{$total_price}</td>
            </tr>
        </tbody>
    </table>

    {if $offer.customer_note}
        <div style="margin-top: 15px;">
            <strong>{l s='Customer Note' mod='productpriceconfig'}:</strong>
            <p>{$offer.customer_note nofilter}</p>
        </div>
    {/if}

    {if $offer.admin_note}
        <div style="margin-top: 10px;">
            <strong>{l s='Admin Note' mod='productpriceconfig'}:</strong>
            <p>{$offer.admin_note nofilter}</p>
        </div>
    {/if}

    <div class="footer">
        {$shop_name} | {l s='Generated on' mod='productpriceconfig'} {$date_today}
    </div>
</body>
</html>
