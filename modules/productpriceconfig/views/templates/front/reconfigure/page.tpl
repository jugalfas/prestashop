{* Reconfigure & Reorder - Single Order Page *}
{extends file='page.tpl'}

{block name='page_title'}
  <span>{l s='Reconfigure & Reorder' mod='productpriceconfig'}</span>
{/block}

{block name='page_content'}

<div id="reconfigure-app" class="reconfigure-container">

  {* Error state *}
  {if $error}
    <div class="reconfigure-error-banner">
      <p>{$error}</p>
      <a href="{$history_url nofilter}" class="btn-back-link">{l s='Back to order history' mod='productpriceconfig'}</a>
    </div>

  {* Main content *}
  {else}

    <div class="reconfigure-header">
      <div class="order-info">
        <h3 class="order-ref">{l s='Order' mod='productpriceconfig'} {$order_reference}</h3>
        <span class="order-date">{$order_date|date_format:'%d/%m/%Y'}</span>
      </div>
      <a href="{$history_url nofilter}" class="btn-back-link">{l s='Back to order history' mod='productpriceconfig'}</a>
    </div>

    {* Product Blocks *}
    <div class="product-blocks">
      {foreach from=$products item=block}

      <div class="product-block {if $block.is_valid}block-valid{else}block-invalid{/if}"
           data-id-order-detail="{$block.id_order_detail}"
           data-id-product="{$block.id_product}"
           data-id-product-setting="{$block.id_product_setting}"
           data-id-product-attribute="{$block.id_product_attribute}">

        {* Product Header: Image + Name + Prices *}
        <div class="product-header">
          {if $block.image_url}
          <div class="product-image">
            <img src="{$block.image_url nofilter}" alt="{$block.product_name|escape:'html':'UTF-8'}" />
          </div>
          {/if}
          <div class="product-info">
            <h4 class="product-name">{$block.product_name|escape:'html':'UTF-8'}</h4>
            <div class="price-comparison">
              <div class="price-row">
                <span class="price-label">{l s='Original purchase price' mod='productpriceconfig'}:</span>
                <span class="price-value price-original">{$block.original_price_formatted nofilter}</span>
              </div>
              <div class="price-row">
                <span class="price-label">{l s='Current recalculated price' mod='productpriceconfig'}:</span>
                <span class="price-value price-current" id="price-current-{$block.id_order_detail}">
                  {if $block.current_total_formatted}{$block.current_total_formatted nofilter}{else}{l s='N/A' mod='productpriceconfig'}{/if}
                </span>
              </div>
            </div>
          </div>
        </div>

        {* Configuration Details (Read-Only) *}
        <div class="config-details">
          <h5 class="config-title">{l s='Configuration Details' mod='productpriceconfig'}</h5>

          {* Quantity (editable) *}
          {if $block.qty_variable}
          <div class="config-row config-qty" id="qty-row-{$block.id_order_detail}">
            <span class="config-label">{$block.qty_variable.label|escape:'html':'UTF-8'}:</span>
            <span class="qty-display" id="qty-display-{$block.id_order_detail}">
              <span class="qty-value" id="qty-value-{$block.id_order_detail}">{$block.qty_variable.current_quantity}</span>
              <button class="qty-edit-btn" id="qty-edit-btn-{$block.id_order_detail}"
                      data-target="{$block.id_order_detail}"
                      data-id-pv="{$block.qty_variable.id_product_variable}"
                      data-min="{$block.qty_variable.min}"
                      data-max="{$block.qty_variable.max}"
                      data-step="{$block.qty_variable.step}"
                      title="{l s='Edit quantity' mod='productpriceconfig'}">
                &#9998;
              </button>
            </span>
            {* Editable quantity (hidden by default) *}
            <span class="qty-editor" id="qty-editor-{$block.id_order_detail}" style="display:none;">
              <button class="qty-dec" data-target="{$block.id_order_detail}">-</button>
              <input type="number"
                     class="qty-input"
                     id="qty-input-{$block.id_order_detail}"
                     name="variable_{$block.qty_variable.id_product_variable}"
                     value="{$block.qty_variable.current_quantity}"
                     min="{$block.qty_variable.min}"
                     {if $block.qty_variable.max}max="{$block.qty_variable.max}"{/if}
                     step="{$block.qty_variable.step}"
                     data-id-pv="{$block.qty_variable.id_product_variable}" />
              <button class="qty-inc" data-target="{$block.id_order_detail}">+</button>
            </span>
            <span class="qty-range" id="qty-range-{$block.id_order_detail}" style="display:none;">
              {l s='Allowed range' mod='productpriceconfig'}: {$block.qty_variable.min} - {if $block.qty_variable.max}{$block.qty_variable.max}{else}&infin;{/if}
            </span>
          </div>
          {/if}

          {* Other variables (read-only) *}
          {foreach from=$block.display_variables item=var}
          <div class="config-row {if !$var.is_valid}config-invalid{/if}">
            <span class="config-label">{$var.label|escape:'html':'UTF-8'}:</span>
            <span class="config-value">{$var.display_value|escape:'html':'UTF-8'}</span>
            {if !$var.is_valid}
            <span class="config-warning" title="{$var.validity_message|escape:'html':'UTF-8'}">&#9888;</span>
            {/if}
          </div>
          {/foreach}
        </div>

        {* Inline Validation Status *}
        <div class="block-validation" id="validation-{$block.id_order_detail}">
          {if $block.is_valid}
            <span class="val-icon val-success">&#10003;</span>
            <span class="val-text">{l s='Configuration is valid' mod='productpriceconfig'}</span>
          {else}
            <span class="val-icon val-error">&#10007;</span>
            <span class="val-text">{l s='Configuration is no longer valid' mod='productpriceconfig'}</span>
            <div class="val-details">
              {foreach from=$block.validation.checks item=check}
                {if !$check.valid}
                <div class="val-detail severity-{$check.severity}">
                  {if $check.severity === 'error'}&#10007;{else}&#9888;{/if}
                  {$check.message|escape:'html':'UTF-8'}
                </div>
                {/if}
              {/foreach}
            </div>
          {/if}
        </div>

        {* Hidden fields for form submission *}
        <input type="hidden" class="block-params" id="block-params-{$block.id_order_detail}"
               value='{json_encode($block.params)|escape:'html':'UTF-8'}' />
      </div>

      {/foreach}
    </div>

    {* Add to Cart Button *}
    {if !empty($products)}
    <div class="reconfigure-footer">
      <button id="btn-add-to-cart" class="btn-add-all">
        <span class="btn-text">{l s='Add Valid Products To Cart' mod='productpriceconfig'}</span>
        <span class="btn-spinner" style="display:none;"></span>
      </button>
    </div>
    {/if}

  {/if}
</div>

{* Hidden data for JS *}
<script type="text/javascript">
  var reconfigure_ajax_url = '{$ajax_url nofilter}';
  var reconfigure_cart_url = '{$cart_url nofilter}';
</script>

{/block}
