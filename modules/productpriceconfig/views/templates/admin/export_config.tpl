<form action="{$current|escape:'html':'UTF-8'}&token={$token|escape:'html':'UTF-8'}" method="post" class="form-horizontal" id="export_config_form">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Export Configuration' mod='productpriceconfig'}
        </div>
        
        <div class="form-wrapper" style="padding: 20px;">
            
            <!-- Section 1: Variables & Options -->
            <div class="form-group">
                <h3>
                    <input type="checkbox" class="select-all-section" data-target="section-variables">
                    {l s='Variables & Options' mod='productpriceconfig'}
                </h3>
                <div class="alert alert-info">
                    {l s='Select variables and their specific options to export.' mod='productpriceconfig'}
                </div>
                <ul class="list-unstyled tree-list" id="section-variables">
                    {foreach from=$variables item=variable}
                        <li>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" class="variable-parent-checkbox" data-code="{$variable.code}">
                                    <strong>{$variable.label} ({$variable.code})</strong>
                                </label>
                            </div>
                            <ul class="list-unstyled" style="margin-left: 25px; border-left: 1px solid #ccc; padding-left: 10px;">
                                {foreach from=$variable.options item=option}
                                    <li>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" class="option-child-checkbox" name="variables[{$variable.code}][]" value="{$option.value}" data-parent="{$variable.code}">
                                                {$option.value}
                                            </label>
                                        </div>
                                    </li>
                                {/foreach}
                            </ul>
                        </li>
                    {/foreach}
                </ul>
            </div>

            <hr>

            <!-- Section 2: Tooltips -->
            <div class="form-group">
                <h3>
                    <input type="checkbox" class="select-all-section" data-target="section-tooltips">
                    {l s='Tooltips' mod='productpriceconfig'}
                </h3>
                <ul class="list-unstyled" id="section-tooltips">
                    {foreach from=$tooltips item=tooltip}
                        <li style="display:inline-block; width: 30%; vertical-align:top;">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="tooltips[]" value="{$tooltip.code}">
                                    <strong>{$tooltip.label}</strong>
                                </label>
                            </div>
                        </li>
                    {/foreach}
                </ul>
            </div>

            <hr>

            <!-- Section 3: Alerts -->
            <div class="form-group">
                <h3>
                    <input type="checkbox" class="select-all-section" data-target="section-alerts">
                    {l s='Alert Messages' mod='productpriceconfig'}
                </h3>
                <ul class="list-unstyled tree-list" id="section-alerts">
                    {foreach from=$alerts key=product_ref item=product_alerts}
                        <li>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" class="alert-parent-checkbox" data-product="{$product_ref}">
                                    <strong>{$product_ref}</strong>
                                </label>
                            </div>
                            <ul class="list-unstyled" style="margin-left: 25px; border-left: 1px solid #ccc; padding-left: 10px;">
                                {foreach from=$product_alerts item=alert}
                                    <li>
                                        <div class="checkbox">
                                            <label>
                                                <!-- Value format: product_ref|variable_code|option_value -->
                                                <input type="checkbox" class="alert-child-checkbox" name="alerts[]" value="{$alert.product_reference}|{$alert.variable_code}|{$alert.option_value}" data-parent="{$product_ref}">
                                                <span class="label label-info">{$alert.variable_code}</span> : {$alert.option_value}
                                                <br>
                                                <small class="text-muted">{$alert.message_text|truncate:100}</small>
                                            </label>
                                        </div>
                                    </li>
                                {/foreach}
                            </ul>
                        </li>
                    {/foreach}
                </ul>
            </div>

            <hr>

            <!-- Section 4: Product Pricing Config -->
            <div class="form-group">
                <h3>
                    <input type="checkbox" class="select-all-section" data-target="section-products">
                    {l s='Product Pricing Configuration' mod='productpriceconfig'}
                </h3>
                <ul class="list-unstyled tree-list" id="section-products">
                    {foreach from=$products item=product}
                        <li>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" class="product-parent-checkbox" data-product="{$product.product_reference}">
                                    <strong>{$product.product_name} ({$product.product_reference})</strong>
                                </label>
                            </div>
                            <ul class="list-unstyled" style="margin-left: 25px; border-left: 1px solid #ccc; padding-left: 10px;">
                                <li>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" class="product-child-checkbox" name="products[{$product.product_reference}][]" value="formula" data-parent="{$product.product_reference}">
                                            {l s='Formula settings' mod='productpriceconfig'}
                                        </label>
                                    </div>
                                </li>
                                <li>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" class="product-child-checkbox" name="products[{$product.product_reference}][]" value="tiered_price" data-parent="{$product.product_reference}">
                                            {l s='Tiered price' mod='productpriceconfig'}
                                        </label>
                                    </div>
                                </li>
                                <li>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" class="product-child-checkbox" name="products[{$product.product_reference}][]" value="banned_combinations" data-parent="{$product.product_reference}">
                                            {l s='Banned combinations' mod='productpriceconfig'}
                                        </label>
                                    </div>
                                </li>
                                <li>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" class="product-child-checkbox" name="products[{$product.product_reference}][]" value="odd_quantity" data-parent="{$product.product_reference}">
                                            {l s='Odd quantity percentage' mod='productpriceconfig'}
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    {/foreach}
                </ul>
            </div>

        </div>

        <div class="panel-footer">
            <button type="submit" value="1" id="submitExportProductPriceConfig" name="submitExportProductPriceConfig" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Export JSON' mod='productpriceconfig'}
            </button>
        </div>
    </div>
</form>

<script type="text/javascript" src="{$module_dir}views/js/admin_export.js"></script>
