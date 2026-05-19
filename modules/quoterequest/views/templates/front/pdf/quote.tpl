<style>
{literal}
body {
    font-family: Arial, sans-serif;
    font-size: 10pt;
    color: {/literal}{$text_color|default:'#333333'}{literal};
}

.quote-header {
    background-color: {/literal}{$header_bg|default:'#f8f9fa'}{literal};
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
}

.quote-info {
    margin-bottom: 10px;
}

.quote-label {
    font-weight: bold;
    color: #666;
}

.quote-status {
    color: {/literal}{$accent_color|default:'#28a745'}{literal};
    font-weight: bold;
}

.product-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.product-table th {
    background-color: {/literal}{$table_head_bg|default:'#343a40'}{literal};
    color: {/literal}{$table_head_color|default:'#ffffff'}{literal};
    padding: 10px;
    text-align: left;
    font-weight: bold;
    border: 1px solid #dee2e6;
}

.product-table td {
    padding: 10px;
    border: 1px solid #dee2e6;
    vertical-align: top;
}

.product-table tr:nth-child(even) {
    background-color: #f8f9fa;
}

.product-image {
    max-width: 80px;
    max-height: 80px;
}

.product-detail-row {
    margin-bottom: 5px;
}

.product-detail-label {
    font-weight: bold;
    color: #666;
    display: inline-block;
    width: 100px;
}

.price-badge {
    color: {/literal}{$accent_color|default:'#28a745'}{literal};
    font-weight: bold;
}

.config-row {
    margin-bottom: 3px;
    font-size: 9pt;
}

.config-label {
    font-weight: bold;
    color: #666;
    display: inline-block;
    width: 120px;
}

.file-preview {
    max-width: 60px;
    max-height: 60px;
    margin: 2px;
}
{/literal}
</style>

<div class="quote-header">
    <div class="quote-info">
        <span class="quote-label">Quote:</span> 
        #{$quote.id_quote} {$quote.title}
        <span class="quote-label" style="margin-left: 20px;">Status:</span>
        <span class="quote-status">{$quote.status_text}</span>
    </div>
    <div class="quote-info">
        <span class="quote-label">Posted on:</span> {$quote.date_add}
    </div>
</div>

<table class="product-table">
    <thead>
        <tr>
            <th width="15%">Item</th>
            <th width="30%">Product Details</th>
            <th width="35%">Product Configure</th>
            <th width="20%">Files</th>
        </tr>
    </thead>
    <tbody>
        {foreach $quote.products as $product}
        <tr>
            <td>
                {if $product.image_link}
                <img src="{$product.image_link}" class="product-image" alt="Product Image" />
                {/if}
            </td>
            <td>
                <div class="product-detail-row">
                    <span class="product-detail-label">Product:</span>
                    {$product.product_title}
                </div>
                <div class="product-detail-row">
                    <span class="product-detail-label">Price:</span>
                    {if isset($product.price) && $product.price != '0.0000'}
                        <span class="price-badge">{$product.price}</span> ✓
                    {else}
                        ---
                    {/if}
                </div>
            </td>
            <td>
                {foreach json_decode($product.details) as $configuration}
                <div class="config-row">
                    {if isset($configuration->additional_information)}
                        <span class="config-label">Additional Info:</span>
                        {$configuration->additional_information|default:'-'}
                    {else}
                        <span class="config-label">{$configuration->variable_name}:</span>
                        {$configuration->valueText|default:'-'}
                    {/if}
                </div>
                {/foreach}
            </td>
            <td>
                {foreach $product.file as $file}
                    {if $file|regex_replace:'/.*\.(.*)/':'\1'|lower == 'pdf'}
                        <div>📄 {$file|basename}</div>
                    {else}
                        <img src="{$file}" class="file-preview" alt="File" />
                    {/if}
                {/foreach}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
