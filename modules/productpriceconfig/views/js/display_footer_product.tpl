<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="" id="loader" onclick="$(this).hide()" style="display: none;">
    <div class="modal-backdrop-loader"
        style="
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1000;
    background-color: #000;
    opacity: 0.5;
">
        <i class="fa fa-spinner fa-spin text-white font-30"
            style="position: absolute;right: 50%;top: 50%;font-size: 26px;"></i>
    </div>
</div>

{*
<pre>
		{$product.add_to_cart_url|print_r}
		</pre> *}



<div class="configure-area">
    <h2>{l s='Product configureren' mod='productpriceconfig'}</h2>
    <form action="#" id="kd_form">
        <span class="hidden_inputs">
            <input type="hidden" id="kd_id_product" name="id_product" value="{$id_product}">
            <input type="hidden" id="kd_id_product_attribute" name="id_product_attribute" value="0">
            <input type="hidden" id="kd_id_product_setting" name="id_product_setting" value="{$product_setting->id}">
            <input type="hidden" id="kd_baned_comb" name="baned_comb" value="{$product_setting->baned_comb}">
            <input type="hidden" id="kd_id_customization" name="id_customization" value="{$id_customization}">
            <input type="hidden" id="add-to-cart-or-refresh" value="{$urls.pages.cart}">
        </span>
        {foreach $variables as $t}
        {*
        <pre>
			{$t|print_r}
			</pre> *}
        <div class="frm-section" data-id="{$t.id_product_variable|intval}">
            <div class="left-frm-section-label">{$t.name|escape:'html':'UTF-8'}
                {if $t.id_variable_tooltip }
                <div class="tooltip"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <span class="tooltiptext">{$t.tooltip_text nofilter}</span>
                </div>
                {/if}
            </div>
            <div class="right-frm-section">
                {if $t.type == 1}
                <div class="quantity-label">
                    <div class="input-group-prepend">
                        <a class="minas qty-down" id="minus-btn"><i class="fa fa-minus"></i></a>
                    </div>
                    <input type="number" id="qty_input" name="variable_{$t.id_product_variable|intval}"
                        class="form-control form-control-sm number-area input-quantity-wanted {$t.variable_name} input_for_url"
                        value="{$t.p_minimum}" min="{$t.p_minimum}" max="{$t.p_maximum}"
                        data-formula-name="{$t.formula_name}" data-variable_id="{$t.id_variable}"
                        data-variable-name="{$t.variable_name}"
                        step="{if $t.multiplier != 0}{$t.multiplier}{else}{1}{/if}"
                        data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                    <div class="input-group-prepend">
                        <a class="plus qty-up" id="plus-btn"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="custom_input_error"></div>
                {elseif $t.type == 2}
                {if count($t.options_data)}
                <div class="dropdown_error">
                    {* <a class="btn btn-select dropdown">
                        <input type="hidden" class="btn-select-input input_for_url" id="{$t.variable_name}"
                            name="variable_{$t.id_product_variable|intval}" value=""
                            data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                            data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}"
                            data-type="{$t.type}">
                        <span class="btn-select-value {$t.variable_name}" data-variable_id="{$t.id_variable}"
                            data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                            data-url_variable="

									{str_replace(' ', '_', strtolower($t.variable_name))}">

                            {l s='Bitte auswählen' mod='productpriceconfig'}</span>
                        <i class="fa fa-caret-down" aria-hidden="true"></i>
                        <ul style="display: none;" class="{$t.variable_name} select_box_for_url"
                            data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                            data-formula-name="{$t.formula_name}"
                            data-url_variable="

									{str_replace(' ', '_', strtolower($t.variable_name))}">


                            {foreach $t.options_data as $key => $option}
                            <li data-id_option="{$option.id}" data-price="{$option.price}"
                                data-weight="{$option.weight}" {if $option.id==$t.default_option} class="selected" {/if}
                                data-option_id="{$option.id}">{$option.name}</li>


                            {/foreach}

                        </ul> *}
                        <select class="btn btn-select btn-select-value {$t.variable_name}"
                            data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                            data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}"
                            style="text-align:left">
                            <option value="0">{l s='Bitte auswählen' mod='productpriceconfig'}</option>
                            {foreach $t.options_data as $key => $option}
                            <option data-id_option="{$option.id}" data-price="{$option.price}"
                                data-weight="{$option.weight}" {if $option.id==$t.default_option} class="selected"
                                {/if} data-option_id="{$option.id}">{$option.name}</option>
                            {/foreach}
                        </select>
                        {*
                    </a> *}
                </div>
                {/if}
                {elseif $t.type == 3}
                <div class="fixed-label">
                    <div class="input-group-prepend">
                        <button class="minas" id="minus-btn">{$t.fixed_price}</button>
                    </div>
                    <input type="hidden" id="qty_input" name="variable_{$t.id_product_variable|intval}"
                        class="{$t.variable_name} input_for_url" value="{$t.fixed_price}"
                        data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                        data-formula-name="{$t.formula_name}"
                        data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}"
                        data-type="{$t.type}">

                </div>

                {elseif $t.type == 4}
                <div class="quantity-label">
                    <div class="input-group-prepend">
                        <a class="minas qty-down" id="minus-btn"><i class="fa fa-minus"></i></a>
                    </div>
                    <input type="number" id="custom_input" name="variable_{$t.id_product_variable|intval}"
                        class="form-control form-control-sm number-area input-quantity-wanted {if $t.variable_name}{$t.variable_name}{else}{$t.variable_name}{/if} input_for_url"
                        value="{$t.p_minimum}" {if $t.p_minimum } min="{$t.p_minimum}" {else} min="0" {/if}
                        {if $t.p_maximum } max="{$t.p_maximum}" {/if} data-variable_id="{$t.id_variable}"
                        data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                        step="{if $t.multiplier != 0}{$t.multiplier}{else}{1}{/if}"
                        data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}"
                        data-type="{$t.type}">
                    <div class="input-group-prepend">
                        <a class="plus qty-up" id="plus-btn"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="custom_input_error"></div>

                {elseif $t.type == 5}
                <div class="text-label">
                    <input type="text" id="custom_text_input" name="variable_{$t.id_product_variable|intval}"
                        class="form-control form-control-sm {$t.variable_name} input_for_url" value=""
                        data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                        data-formula-name="{$t.formula_name}"
                        data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}"
                        data-type="{$t.type}">
                </div>
                {/if}
            </div>
        </div>

        {/foreach}
    </form>
    <div class="frm-section">
        <div class="left-frm-section calculate-error"></div>
        <div class="calculate-price"><a href="#" class="calculate_totals"><span>{l s='prijs berekenen'
                    mod='productpriceconfig'}</span></a></div>
    </div>

    <div class="frm-section">
        <div class="left-frm-section">{l s='excl. BTW' mod='productpriceconfig'}</div>
        <div class="right-frm-section-price price_wot">$0.0000</div>
    </div>
    <div class="frm-section">
        <div class="left-frm-section">{l s='BTW' mod='productpriceconfig'} <p style="font-size:14px;">
                {$product.tax_name}</P>
        </div>
        <div class="right-frm-section-price tax">$0.0000</div>
    </div>

    <div class="frm-section no-bottom-boder">
        <div class="left-frm-section">{l s='incl. BTW' mod='productpriceconfig'}</div>
        <div class="right-frm-section-price total">$0.0000</div>
    </div>

    <div class="frm-section">
        <div class="calculate-price-green">
            <a href="#" class="main-add-to-cart single_add_to_cart_button">{l s='In de winkelwagen'
                mod='productpriceconfig'}
            </a>
        </div>

        <span class="text-danger alert_message_text"></span>
        <div class="product-add-to-cart">

        </div>
        {* <div class="add">
            <button class="btn btn-primary add-to-cart" data-button-action="add-to-cart" type="submit" {if
                !$product.add_to_cart_url} disabled {/if}>
                <i class="material-icons shopping-cart">&#xE547;</i>
                {l s='Add to cart' d='Shop.Theme.Actions'}
            </button>
        </div>
        {$display_customize_hook nofilter}
        *}



    </div>
    {$html_content nofilter}
</div>

<div class="modal fade" id="alert_message" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title pull-left" id="exampleModalLabel">{l s='Warning' mod='productpriceconfig'}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="message"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
