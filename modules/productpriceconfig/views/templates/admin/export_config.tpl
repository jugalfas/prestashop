<style>
    .md-checkbox {
        position: relative;
        margin: 0;
        margin: initial;
        text-align: left;
        display: block;
        margin-bottom: 6px;
    }

    .md-checkbox.md-checkbox-inline {
        display: inline-block;
        margin-bottom: 0;
    }

    .md-checkbox.disabled {
        color: #6c868e
    }

    .md-checkbox label {
        padding-left: 28px;
        margin-bottom: 0;
        display: inline-block;
        font-weight: 400;
        line-height: 20px;
    }

    .md-checkbox label strong {
        font-weight: 400;
    }

    .md-checkbox .md-checkbox-control {
        cursor: pointer
    }

    .md-checkbox .md-checkbox-control::before,
    .md-checkbox .md-checkbox-control::after {
        position: absolute;
        top: 0;
        left: 0;
        content: ""
    }

    .md-checkbox .md-checkbox-control::before {
        width: 20px;
        height: 20px;
        cursor: pointer;
        background: #fff;
        border: 2px solid #b3c7cd;
        border-radius: 2px;
        -webkit-transition: background 0.3s;
        transition: background 0.3s
    }

    .md-checkbox [type="checkbox"] {
        display: none;
        outline: 0
    }

    .md-checkbox [type="checkbox"]:disabled+.md-checkbox-control {
        cursor: not-allowed;
        opacity: .5
    }

    .md-checkbox [type="checkbox"]:disabled+.md-checkbox-control::before {
        cursor: not-allowed
    }

    .md-checkbox [type="checkbox"]:checked+.md-checkbox-control::before,
    .md-checkbox .indeterminate+.md-checkbox-control::before {
        background: #25b9d7;
        border: none
    }

    .md-checkbox [type="checkbox"]:checked+.md-checkbox-control::after,
    .md-checkbox .indeterminate+.md-checkbox-control::after {
        top: 4.5px;
        left: 3px;
        width: 14px;
        height: 7px;
        border: 2px solid #fff;
        border-top-style: none;
        border-right-style: none;
        -webkit-transform: rotate(-45deg);
        transform: rotate(-45deg)
    }

    .md-checkbox .indeterminate+.md-checkbox-control::after {
        top: 9px;
        height: 0;
        -webkit-transform: rotate(0);
        transform: rotate(0)
    }
</style>
<style>
    .tree-list {
        margin-top: 10px;
    }

    /* Masonry flow for variables to avoid tall-row gaps */
    #section-variables {
        column-count: 2;
        column-gap: 16px;
    }

    /* Flex rows for alerts and products */
    #section-alerts,
    #section-products {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-start;
    }

    #section-tooltips {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    #section-tooltips>li {
        width: calc(33.333% - 12px);
        min-width: 260px;
        margin: 0;
        vertical-align: top;
    }

    /* Cards in alerts/products */
    #section-alerts>li,
    #section-products>li {
        flex: 1 1 48%;
        min-width: 380px;
        background: #fff;
        border: 1px solid #eaeef1;
        border-radius: 4px;
        padding: 10px 12px;
    }

    /* Cards in variables (columns) */
    #section-variables>li {
        display: inline-block;
        width: 100%;
        background: #fff;
        border: 1px solid #eaeef1;
        border-radius: 4px;
        padding: 10px 12px;
        margin: 0 0 16px;
        break-inside: avoid;
        -webkit-column-break-inside: avoid;
        page-break-inside: avoid;
    }

    /* Let cards size to their content to avoid large blank areas */
    #section-variables>li,
    #section-alerts>li {
        max-height: none;
        overflow: visible;
    }

    #section-variables>li>ul.list-unstyled,
    #section-alerts>li>ul.list-unstyled,
    #section-products>li>ul.list-unstyled {
        margin-left: 0 !important;
        border-left: 0 !important;
        padding-left: 0 !important;
    }

    #section-variables>li>ul.list-unstyled,
    #section-products>li>ul.list-unstyled {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 16px;
    }

    #section-variables>li>ul.list-unstyled>li {
        width: calc(50% - 16px);
    }

    @media (min-width: 1400px) {
        #section-variables {
            column-count: 2;
        }

        #section-variables>li>ul.list-unstyled>li {
            width: calc(33.333% - 16px);
        }
    }

    @media (max-width: 991px) {

        #section-alerts>li,
        #section-products>li {
            flex: 1 1 100%;
            min-width: 0;
        }

        #section-variables {
            column-count: 1;
        }

        #section-tooltips>li {
            width: 100%;
            min-width: 0;
        }

        #section-variables>li>ul.list-unstyled>li {
            width: 100%;
        }
    }

    .separator {
        display: block;
        height: 0;
        border-bottom: 1px solid #F1F1F2;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
</style>
<form action="{$current|escape:'html':'UTF-8'}&token={$token|escape:'html':'UTF-8'}" method="post"
    class="form-horizontal" id="export_config_form">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Export Configuration' mod='productpriceconfig'}
        </div>

        <div style="padding: 20px;">

            <!-- Section 1: Variables & Options -->
            <div class="form-group">
                <h3>
                    <div class="md-checkbox md-checkbox-inline">
                        <label>
                            <input type="checkbox" id="bulk_action_selected_products-43" class="select-all-section"
                                data-target="section-variables" name="bulk_action_selected_products[]" value="43">
                            <i class="md-checkbox-control"></i>
                        </label>
                        {l s='Variables & Options' mod='productpriceconfig'}
                    </div>
                </h3>
                <!--<div class="alert alert-info">
                    {l s='Select variables and their specific options to export.' mod='productpriceconfig'}
                </div>-->
                <ul class="list-unstyled tree-list" id="section-variables">
                    {foreach from=$variables item=variable}
                        <li>
                            <div class="md-checkbox md-checkbox-inline">
                                <label>
                                    <input type="checkbox" class="variable-parent-checkbox" data-code="{$variable.code}">
                                    <i class="md-checkbox-control"></i>
                                    <strong>{$variable.label} ({$variable.code})</strong>
                                </label>
                            </div>
                            <div class="separator"></div>
                            <ul class="list-unstyled"
                                style="margin-left: 25px; border-left: 1px solid #ccc; padding-left: 10px;">
                                {foreach from=$variable.options item=option}
                                    <li>
                                        <div class="md-checkbox md-checkbox-inline">
                                            <label>
                                                <input type="checkbox" class="option-child-checkbox"
                                                    name="variables[{$variable.code}][]" value="{$option.value}"
                                                    data-parent="{$variable.code}">
                                                <i class="md-checkbox-control"></i>
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
                    <div class="md-checkbox md-checkbox-inline">
                        <label>
                            <input type="checkbox" class="select-all-section" data-target="section-tooltips">
                            <i class="md-checkbox-control"></i>
                        </label>
                        {l s='Tooltips' mod='productpriceconfig'}
                    </div>
                </h3>
                <ul class="list-unstyled" id="section-tooltips">
                    {foreach from=$tooltips item=tooltip}
                        <li style="display:inline-block; width: 30%; vertical-align:top;">
                            <div class="md-checkbox md-checkbox-inline">
                                <label>
                                    <input type="checkbox" name="tooltips[]" value="{$tooltip.id}">
                                    <i class="md-checkbox-control"></i>
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
                    <div class="md-checkbox md-checkbox-inline">
                        <label>
                            <input type="checkbox" class="select-all-section" data-target="section-alerts">
                            <i class="md-checkbox-control"></i>
                        </label>
                        {l s='Alert Messages' mod='productpriceconfig'}
                    </div>
                </h3>
                <ul class="list-unstyled tree-list" id="section-alerts">
                    {foreach from=$alerts key=product_ref item=product_alerts}
                        <li>
                            <div class="md-checkbox md-checkbox-inline">
                                <label>
                                    <input type="checkbox" class="alert-parent-checkbox" data-product="{$product_ref}">
                                    <i class="md-checkbox-control"></i>
                                    <strong>{$product_ref}</strong>
                                </label>
                            </div>
                            <div class="separator"></div>
                            <ul class="list-unstyled"
                                style="margin-left: 25px; border-left: 1px solid #ccc; padding-left: 10px;">
                                {foreach from=$product_alerts item=alert}
                                    <li>
                                        <div class="md-checkbox md-checkbox-inline">
                                            <label>
                                                <input type="checkbox" class="alert-child-checkbox" name="alerts[]"
                                                    value="{$alert.id_product}|{$alert.product_reference}|{$alert.variable_code}|{$alert.option_value}|{$alert.message_text|escape:'url'}"
                                                    data-parent="{$product_ref}">
                                                <i class="md-checkbox-control"></i>
                                                <span class="label label-info">{$alert.variable_code}</span> :
                                                {$alert.option_value}
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
                    <div class="md-checkbox md-checkbox-inline">
                        <label>
                            <input type="checkbox" class="select-all-section" data-target="section-products">
                            <i class="md-checkbox-control"></i>
                        </label>
                        {l s='Product Pricing Configuration' mod='productpriceconfig'}
                    </div>
                </h3>
                <ul class="list-unstyled tree-list" id="section-products">
                    {foreach from=$products item=product}
                        <li>
                            <div class="md-checkbox md-checkbox-inline">
                                <label>
                                    <input type="checkbox" class="product-parent-checkbox"
                                        data-product="{$product.id_product}">
                                    <i class="md-checkbox-control"></i>
                                    <strong>{$product.product_name} ({$product.product_reference})</strong>
                                </label>
                            </div>
                            <div class="separator"></div>
                            <ul class="list-unstyled"
                                style="margin-left: 25px; border-left: 1px solid #ccc; padding-left: 10px;">
                                <li>
                                    <div class="md-checkbox md-checkbox-inline">
                                        <label>
                                            <input type="checkbox" class="product-child-checkbox"
                                                name="products[{$product.id_product}][]" value="formula"
                                                data-parent="{$product.id_product}">
                                            <i class="md-checkbox-control"></i>
                                            {l s='Formula settings' mod='productpriceconfig'}
                                        </label>
                                    </div>
                                </li>
                                <li>
                                    <div class="md-checkbox md-checkbox-inline">
                                        <label>
                                            <input type="checkbox" class="product-child-checkbox"
                                                name="products[{$product.id_product}][]" value="tiered_price"
                                                data-parent="{$product.id_product}">
                                            <i class="md-checkbox-control"></i>
                                            {l s='Tiered price' mod='productpriceconfig'}
                                        </label>
                                    </div>
                                </li>
                                <li>
                                    <div class="md-checkbox md-checkbox-inline">
                                        <label>
                                            <input type="checkbox" class="product-child-checkbox"
                                                name="products[{$product.id_product}][]" value="banned_combinations"
                                                data-parent="{$product.id_product}">
                                            <i class="md-checkbox-control"></i>
                                            {l s='Banned combinations' mod='productpriceconfig'}
                                        </label>
                                    </div>
                                </li>
                                <li>
                                    <div class="md-checkbox md-checkbox-inline">
                                        <label>
                                            <input type="checkbox" class="product-child-checkbox"
                                                name="products[{$product.id_product}][]" value="odd_quantity"
                                                data-parent="{$product.id_product}">
                                            <i class="md-checkbox-control"></i>
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
            <button type="submit" value="1" id="submitExportProductPriceConfig" name="submitExportProductPriceConfig"
                class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Export JSON' mod='productpriceconfig'}
            </button>
        </div>
    </div>
</form>

<script type="text/javascript" src="{$module_dir}views/js/admin_export.js"></script>
