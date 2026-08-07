<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<style>
/* ── Reset & Base ── */
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: helvetica, arial, sans-serif;
    font-size: 9pt;
    color: #1a1a1a;
    line-height: 1.45;
    background: #ffffff;
}

/* ── Brand ── */
.brand { color: #afcb31; }

/* ── Page layout helper ── */
.w100 { width: 100%; }
.clearfix::after { content: ''; display: table; clear: both; }

/* ── Header block ── */
.doc-header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
.doc-header-table td { padding: 0; vertical-align: top; }
.cell-logo { width: 55%; }
.cell-docinfo { width: 45%; text-align: right; }

.logo-img { max-height: 52px; max-width: 200px; }
.shop-name-text { font-size: 22pt; font-weight: bold; color: #afcb31; letter-spacing: -1px; }

.doc-title {
    font-size: 22pt;
    font-weight: bold;
    color: #afcb31;
    margin-top: 6px;
    letter-spacing: -0.5px;
}

.docinfo-table { width: 100%; border-collapse: collapse; }
.docinfo-table td { padding: 1px 0; font-size: 8pt; color: #333; }
.docinfo-label { font-weight: bold; text-align: right; padding-right: 6px; white-space: nowrap; }
.docinfo-value { text-align: right; }

/* ── Divider line ── */
.header-rule { border: none; border-top: 1px solid #dddddd; margin: 10px 0 14px 0; }

/* ── Address section ── */
.addr-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
.addr-table td { vertical-align: top; padding: 0; width: 50%; }
.addr-section-label {
    font-size: 8pt;
    font-weight: bold;
    color: #afcb31;
    margin-bottom: 4px;
    padding-bottom: 3px;
    border-bottom: 1px solid #afcb31;
    display: block;
}
.addr-text { font-size: 8.5pt; color: #333; line-height: 1.55; }
.addr-highlight { font-weight: bold; font-size: 9pt; color: #1a1a1a; }

/* ── Status badge ── */
.status-pill {
    display: inline;
    padding: 2px 10px;
    font-size: 7.5pt;
    font-weight: bold;
    border-radius: 10px;
}
.s-waiting    { background-color: #e3f2fd; color: #1565c0; }
.s-offered    { background-color: #fff8e1; color: #e65100; }
.s-accepted   { background-color: #e8f5e9; color: #2e7d32; }
.s-rejected   { background-color: #ffebee; color: #c62828; }
.s-cancelled  { background-color: #f5f5f5; color: #616161; }
.s-expired    { background-color: #eceff1; color: #546e7a; }
.s-converted  { background-color: #f3e5f5; color: #6a1b9a; }

/* ── Section heading ── */
.section-heading-table { width: 100%; border-collapse: collapse; margin: 0 0 4px 0; }
.section-heading-table td { padding: 0; }
.section-heading-label {
    font-size: 8pt;
    font-weight: bold;
    color: #afcb31;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding-bottom: 3px;
    border-bottom: 1px solid #afcb31;
}

/* ── Products table header ── */
.products-header-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #afcb31;
    margin-bottom: 0;
}
.products-header-table td {
    padding: 6px 8px;
    font-size: 8pt;
    font-weight: bold;
    color: #ffffff;
}
.col-product { width: 44%; }
.col-type    { width: 16%; text-align: center; }
.col-qty     { width: 8%;  text-align: center; }
.col-est     { width: 16%; text-align: right; }
.col-offered { width: 16%; text-align: right; }

/* ── Product row ── */
.product-row-table {
    width: 100%;
    border-collapse: collapse;
    border-bottom: 1px solid #eeeeee;
    margin-bottom: 0;
}
.product-row-table td {
    padding: 10px 8px;
    vertical-align: top;
    font-size: 8.5pt;
    color: #1a1a1a;
}
.product-row-alt { background-color: #fafafa; }
.product-row-norm { background-color: #ffffff; }

.prod-name-strong { font-weight: bold; font-size: 9pt; color: #1a1a1a; }
.prod-ref { font-size: 7.5pt; color: #888888; }

.td-type   { text-align: center; vertical-align: top; }
.td-qty    { text-align: center; vertical-align: top; font-weight: bold; }
.td-est    { text-align: right;  vertical-align: top; color: #555555; }
.td-offered{ text-align: right;  vertical-align: top; font-weight: bold; color: #afcb31; font-size: 9.5pt; }

.type-normal { font-size: 7.5pt; color: #555555; }
.type-custom {
    font-size: 7.5pt; font-weight: bold;
    color: #7a8b32;
    background: #f0f4e8;
    padding: 2px 7px;
    border-radius: 8px;
}

/* ── Product detail sub-table (image + config) ── */
.product-detail-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}
.product-detail-table td { padding: 0; vertical-align: top; }
.cell-image { width: 68px; }
.product-thumb {
    width: 60px;
    height: 60px;
    border: 1px solid #dddddd;
    border-radius: 4px;
}
.product-thumb-placeholder {
    width: 60px; height: 60px;
    background: #f4f4f4;
    border: 1px solid #dddddd;
    border-radius: 4px;
    text-align: center;
    font-size: 7pt;
    color: #bbbbbb;
    line-height: 60px;
}
.cell-config { padding-left: 10px; }

/* ── Config table ── */
.config-table { width: 100%; border-collapse: collapse; margin-top: 2px; }
.config-table tr td { padding: 2px 4px; font-size: 7.5pt; border-bottom: 1px solid #f2f2f2; vertical-align: top; }
.config-key { color: #777777; font-weight: bold; width: 38%; white-space: nowrap; }
.config-val { color: #222222; }

/* ── Attachments ── */
.attachments-row { margin-top: 7px; padding-top: 5px; border-top: 1px dashed #eeeeee; font-size: 7.5pt; }
.att-label { font-weight: bold; color: #555555; }
.att-item { color: #444444; margin-top: 1px; }

/* ── Printing instructions ── */
.instructions-row {
    margin-top: 7px;
    padding: 5px 8px;
    background: #f9fbf2;
    border-left: 3px solid #afcb31;
    font-size: 7.5pt;
    color: #444444;
}
.instructions-label { font-weight: bold; color: #afcb31; }

/* ── Totals block ── */
.totals-outer-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
.totals-outer-table td { vertical-align: top; padding: 0; }
.totals-spacer { width: 50%; }
.totals-block { width: 50%; }

.totals-table { width: 100%; border-collapse: collapse; }
.totals-table td { padding: 4px 8px; font-size: 8.5pt; }
.totals-label { text-align: right; color: #555555; font-weight: bold; }
.totals-value { text-align: right; color: #1a1a1a; }
.totals-rule-row td { border-top: 1px solid #cccccc; padding-top: 6px; }
.totals-grand-row td {
    background-color: #afcb31;
    color: #ffffff;
    font-weight: bold;
    font-size: 9.5pt;
    padding: 6px 8px;
}
.totals-grand-label { text-align: right; }
.totals-grand-value { text-align: right; }

/* ── Notes box ── */
.notes-box {
    margin-top: 14px;
    padding: 8px 12px;
    background: #f9fbf2;
    border: 1px solid #e0e8b8;
    border-radius: 3px;
    font-size: 8pt;
    color: #333333;
}
.notes-box-label { font-weight: bold; color: #7a8b32; margin-bottom: 3px; }

/* ── Footer ── */
.footer-rule { border: none; border-top: 1px solid #dddddd; margin-top: 22px; margin-bottom: 10px; }
.footer-table { width: 100%; border-collapse: collapse; }
.footer-table td { vertical-align: top; padding: 0 8px 0 0; font-size: 7.5pt; color: #444444; width: 33%; }
.footer-col-label { font-weight: bold; color: #1a1a1a; margin-bottom: 3px; font-size: 8pt; }
.footer-col-text { line-height: 1.6; color: #555555; }
.footer-page { text-align: right; font-size: 7.5pt; color: #aaaaaa; margin-top: 6px; }
</style>
</head>
<body>

{* ═══════════════════════════════════════════════
   DOCUMENT HEADER
   ═══════════════════════════════════════════════ *}
<table class="doc-header-table">
    <tr>
        <td class="cell-logo">
            {if file_exists($shop_logo)}
                <img src="{$shop_logo}" alt="{$shop_name}" class="logo-img" />
            {else}
                <span class="shop-name-text">{$shop_name|upper}</span>
            {/if}
            <br />
            <div class="doc-title">{l s='Quotation' mod='productpriceconfig'}</div>
        </td>
        <td class="cell-docinfo">
            <table class="docinfo-table">
                <tr>
                    <td class="docinfo-label">{l s='Quotation Nr.' mod='productpriceconfig'}:</td>
                    <td class="docinfo-value"><strong>{$offer->reference}</strong></td>
                </tr>
                <tr>
                    <td class="docinfo-label">{l s='Date' mod='productpriceconfig'}:</td>
                    <td class="docinfo-value">{$date_today}</td>
                </tr>
                {if $offer->expire_date}
                <tr>
                    <td class="docinfo-label">{l s='Valid Until' mod='productpriceconfig'}:</td>
                    <td class="docinfo-value">{$offer->expire_date}</td>
                </tr>
                {/if}
                <tr>
                    <td class="docinfo-label">{l s='Status' mod='productpriceconfig'}:</td>
                    <td class="docinfo-value">
                        {assign var="os" value=$offer->status}
                        {if $os == 'waiting_for_price'}
                            <span class="status-pill s-waiting">{l s='Waiting for Price' mod='productpriceconfig'}</span>
                        {elseif $os == 'price_offered'}
                            <span class="status-pill s-offered">{l s='Price Offered' mod='productpriceconfig'}</span>
                        {elseif $os == 'accepted'}
                            <span class="status-pill s-accepted">{l s='Accepted' mod='productpriceconfig'}</span>
                        {elseif $os == 'rejected'}
                            <span class="status-pill s-rejected">{l s='Rejected' mod='productpriceconfig'}</span>
                        {elseif $os == 'cancelled'}
                            <span class="status-pill s-cancelled">{l s='Cancelled' mod='productpriceconfig'}</span>
                        {elseif $os == 'expired'}
                            <span class="status-pill s-expired">{l s='Expired' mod='productpriceconfig'}</span>
                        {elseif $os == 'converted_to_order'}
                            <span class="status-pill s-converted">{l s='Converted to Order' mod='productpriceconfig'}</span>
                        {else}
                            <span class="status-pill s-waiting">{$offer->status}</span>
                        {/if}
                    </td>
                </tr>
                {if $offer->title}
                <tr>
                    <td class="docinfo-label">{l s='Title' mod='productpriceconfig'}:</td>
                    <td class="docinfo-value">{$offer->title}</td>
                </tr>
                {/if}
            </table>
        </td>
    </tr>
</table>

<hr class="header-rule" />

{* ═══════════════════════════════════════════════
   CUSTOMER / ADDRESS SECTION
   ═══════════════════════════════════════════════ *}
<table class="addr-table">
    <tr>
        <td style="padding-right: 20px;">
            <span class="addr-section-label">{l s='Customer' mod='productpriceconfig'}</span>
            <div class="addr-text">
                {if $customer_name}
                    <div class="addr-highlight">{$customer_name}</div>
                {/if}
                {if $customer_email}
                    <div>{$customer_email}</div>
                {/if}
            </div>
        </td>
        <td>
            <span class="addr-section-label">{l s='Quotation Details' mod='productpriceconfig'}</span>
            <div class="addr-text">
                <div><strong>{l s='Reference' mod='productpriceconfig'}:</strong> {$offer->reference}</div>
                <div><strong>{l s='Total products' mod='productpriceconfig'}:</strong> {$products|@count}</div>
                {if $offer->expire_date}
                    <div><strong>{l s='Offer expires' mod='productpriceconfig'}:</strong> {$offer->expire_date}</div>
                {/if}
            </div>
        </td>
    </tr>
</table>

{* ═══════════════════════════════════════════════
   PRODUCTS SECTION HEADING
   ═══════════════════════════════════════════════ *}
<table class="section-heading-table" style="margin-top: 6px; margin-bottom: 0;">
    <tr>
        <td><span class="section-heading-label">{l s='Products' mod='productpriceconfig'}</span></td>
    </tr>
</table>

{* ── Products table header ── *}
<table class="products-header-table">
    <tr>
        <td class="col-product">{l s='Product' mod='productpriceconfig'}</td>
        <td class="col-type">{l s='Type' mod='productpriceconfig'}</td>
        <td class="col-qty">{l s='Qty' mod='productpriceconfig'}</td>
        <td class="col-est">{l s='Estimated' mod='productpriceconfig'}</td>
        <td class="col-offered">{l s='Offered Price' mod='productpriceconfig'}</td>
    </tr>
</table>

{* ── Each product ── *}
{foreach from=$products item=p name=ploop}

{assign var="rowbg" value="product-row-norm"}
{if $smarty.foreach.ploop.iteration is odd}
    {assign var="rowbg" value="product-row-alt"}
{/if}

{assign var="pstatus" value=$p.status|lower|replace:' ':'_'}

<table class="product-row-table {$rowbg}">
    <tr>

        {* Product name + details column *}
        <td class="col-product" style="vertical-align: top; padding: 10px 8px;">
            <div class="prod-name-strong">{$p.name}</div>
            {if $p.reference}
                <div class="prod-ref">{l s='Ref' mod='productpriceconfig'}: {$p.reference}</div>
            {/if}

            {* Image + config detail sub-layout *}
            <table class="product-detail-table">
                <tr>
                    <td class="cell-image">
                        {if $p.image_path}
                            <img src="{$p.image_path}" class="product-thumb" alt="{$p.name}" />
                        {else}
                            <div class="product-thumb-placeholder">&#9634;</div>
                        {/if}
                    </td>
                    <td class="cell-config">
                        {if $p.configuration_rows}
                            <table class="config-table">
                                {foreach from=$p.configuration_rows item=row}
                                    <tr>
                                        <td class="config-key">{$row.label}</td>
                                        <td class="config-val">{$row.value}</td>
                                    </tr>
                                {/foreach}
                            </table>
                        {elseif $p.configuration}
                            <div style="font-size: 7.5pt; color: #555;">{$p.configuration}</div>
                        {/if}
                    </td>
                </tr>
            </table>

            {* Attachments *}
            {if $p.attachments}
                <div class="attachments-row">
                    <span class="att-label">{l s='Uploaded files' mod='productpriceconfig'}:</span>
                    {foreach from=$p.attachments item=att}
                        <div class="att-item">&#8227; {$att.filename} ({$att.size})</div>
                    {/foreach}
                </div>
            {/if}

            {* Printing instructions / customer note *}
            {if $p.customer_note}
                <div class="instructions-row">
                    <span class="instructions-label">{l s='Printing Instructions' mod='productpriceconfig'}:</span>
                    {$p.customer_note|strip_tags}
                </div>
            {/if}

            {* Admin note (internal memo, shown lightly) *}
            {if $p.admin_note}
                <div style="margin-top: 5px; font-size: 7pt; color: #999;">
                    <em>{l s='Note' mod='productpriceconfig'}: {$p.admin_note|strip_tags}</em>
                </div>
            {/if}

            {* Weight / production time pills *}
            {if ($p.weight && $p.weight != '-') || ($p.production_time && $p.production_time != '-')}
                <div style="margin-top: 6px; font-size: 7.5pt; color: #777777;">
                    {if $p.weight && $p.weight != '-'}
                        {l s='Weight' mod='productpriceconfig'}: <strong>{$p.weight}</strong>
                        {if $p.production_time && $p.production_time != '-'}&nbsp;&nbsp;{/if}
                    {/if}
                    {if $p.production_time && $p.production_time != '-'}
                        {l s='Production' mod='productpriceconfig'}: <strong>{$p.production_time}</strong>
                        {if $p.production_time_days} ({$p.production_time_days} {l s='days' mod='productpriceconfig'}){/if}
                    {/if}
                </div>
            {/if}
        </td>

        {* Type *}
        <td class="td-type col-type">
            {if $p.product_type == 'custom'}
                <span class="type-custom">{l s='Custom' mod='productpriceconfig'}</span>
            {else}
                <span class="type-normal">{l s='Standard' mod='productpriceconfig'}</span>
            {/if}
            <br /><br />
            {assign var="pslab" value=$p.status|lower|replace:' ':'_'}
            {if $pslab == 'accepted'}
                <span class="status-pill s-accepted">{$p.status}</span>
            {elseif $pslab == 'price_offered'}
                <span class="status-pill s-offered">{$p.status}</span>
            {elseif $pslab == 'rejected'}
                <span class="status-pill s-rejected">{$p.status}</span>
            {elseif $pslab == 'cancelled'}
                <span class="status-pill s-cancelled">{$p.status}</span>
            {elseif $pslab == 'expired'}
                <span class="status-pill s-expired">{$p.status}</span>
            {else}
                <span class="status-pill s-waiting">{$p.status}</span>
            {/if}
        </td>

        {* Quantity *}
        <td class="td-qty col-qty">{$p.quantity}</td>

        {* Estimated price *}
        <td class="td-est col-est">
            {if $p.estimated_price && $p.estimated_price != '--'}
                {$p.estimated_price}
            {else}
                <span style="color: #cccccc;">—</span>
            {/if}
        </td>

        {* Offered price *}
        <td class="td-offered col-offered">
            {if $p.offered_price && $p.offered_price != '--'}
                {$p.offered_price}
                {if $p.line_total && $p.line_total != '--' && $p.quantity > 1}
                    <br /><span style="font-size: 7pt; color: #555555; font-weight: normal;">{l s='Total' mod='productpriceconfig'}: {$p.line_total}</span>
                {/if}
            {else}
                <span style="color: #cccccc; font-size: 8pt; font-weight: normal;">{l s='Pending' mod='productpriceconfig'}</span>
            {/if}
        </td>
    </tr>
</table>

{/foreach}

{* ═══════════════════════════════════════════════
   TOTALS
   ═══════════════════════════════════════════════ *}
{if $total_price && $total_price != '--'}
<table class="totals-outer-table">
    <tr>
        <td class="totals-spacer">
            {* Left spacer – intentionally empty *}
        </td>
        <td class="totals-block">
            <table class="totals-table">
                <tr class="totals-rule-row">
                    <td class="totals-label">{l s='Sub-total' mod='productpriceconfig'} :</td>
                    <td class="totals-value">{$total_price}</td>
                </tr>
                <tr>
                    <td class="totals-label">{l s='Discount' mod='productpriceconfig'} :</td>
                    <td class="totals-value">—</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 4px 0;">
                        <table style="width:100%;border-collapse:collapse;"><tr><td style="border-top:1px solid #cccccc;"></td></tr></table>
                    </td>
                </tr>
                <tr class="totals-grand-row">
                    <td class="totals-grand-label">{l s='Grand Total' mod='productpriceconfig'} :</td>
                    <td class="totals-grand-value">{$total_price}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{/if}

{* ═══════════════════════════════════════════════
   OFFER-LEVEL NOTES
   ═══════════════════════════════════════════════ *}
{if $offer->customer_note}
<div class="notes-box" style="margin-top: 16px;">
    <div class="notes-box-label">{l s='Customer Note' mod='productpriceconfig'}</div>
    <div>{$offer->customer_note|strip_tags}</div>
</div>
{/if}

{if $offer->admin_note}
<div class="notes-box">
    <div class="notes-box-label">{l s='Remarks' mod='productpriceconfig'}</div>
    <div>{$offer->admin_note|strip_tags}</div>
</div>
{/if}

{* ═══════════════════════════════════════════════
   FOOTER
   ═══════════════════════════════════════════════ *}
<hr class="footer-rule" />
<table class="footer-table">
    <tr>
        <td>
            <div class="footer-col-label">{l s='Company' mod='productpriceconfig'}</div>
            <div class="footer-col-text">{$shop_name}</div>
        </td>
        <td>
            <div class="footer-col-label">{l s='Quotation' mod='productpriceconfig'}</div>
            <div class="footer-col-text">
                {l s='Reference' mod='productpriceconfig'}: {$offer->reference}<br />
                {l s='Date' mod='productpriceconfig'}: {$date_today}
            </div>
        </td>
        <td style="text-align: right;">
            <div class="footer-col-label">&nbsp;</div>
            <div class="footer-col-text" style="color: #aaaaaa; font-size: 7pt;">
                {l s='This is a non-binding quotation.' mod='productpriceconfig'}<br />
                {l s='Prices are subject to final confirmation.' mod='productpriceconfig'}
            </div>
        </td>
    </tr>
</table>

</body>
</html>
