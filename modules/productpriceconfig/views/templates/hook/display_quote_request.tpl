<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="" id="loader" onclick="$(this).hide()" style="display: none;">
    <div class="modal-backdrop-loader" style="
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
                                data-formula-name="{$t.formula_name}" data-variable_id="{$t.id_variable}" data-product_variable_id="{$t.id_product_variable|intval}"
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
                                                                                									data-weight="{$option.weight}" 



                                        {if $option.id==$t.default_option} class="selected" 



                                        {/if}
                                                                                									data-option_id="{$option.id}">{$option.name}</li>






                                    {/foreach}

                                                                							</ul> *}
                                    <input type="hidden" class="btn-select-input input_for_url" id="{$t.variable_name}"
                                        name="variable_{$t.id_product_variable|intval}" value="{$t.default_option}"
                                        data-variable_id="{$t.id_variable}" data-product_variable_id="{$t.id_product_variable|intval}" data-variable-name="{$t.variable_name}"
                                        data-formula-name="{$t.formula_name}"
                                        data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                                    <select class="btn btn-select btn-select-value select_box_for_url {$t.variable_name}"
                                        data-variable_id="{$t.id_variable}" data-product_variable_id="{$t.id_product_variable|intval}" data-variable-name="{$t.variable_name}"
                                        data-formula-name="{$t.formula_name}"
                                        data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}"
                                        style="text-align:left">
                                        <option value="0">{l s='Bitte auswählen' mod='productpriceconfig'}</option>
                                        {foreach $t.options_data as $key => $option}
                                            <option data-id_option="{$option.id}" data-price="{$option.price}"
                                                data-weight="{$option.weight}" data-thickness="{$option.thickness}"
                                                {if $option.id==$t.default_option} selected class="selected" {/if}
                                                data-option_id="{$option.id}" value="{$option.id}">{$option.name}</option>
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
                                    data-variable_id="{$t.id_variable}" data-product_variable_id="{$t.id_product_variable|intval}" data-variable-name="{$t.variable_name}"
                                    data-formula-name="{$t.formula_name}"
                                    data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">

                            </div>

                        {elseif $t.type == 4}
                            <div class="quantity-label">
                                <div class="input-group-prepend">
                                    <a class="minas qty-down" id="minus-btn"><i class="fa fa-minus"></i></a>
                                </div>
                                <input type="number" id="custom_input" name="variable_{$t.id_product_variable|intval}"
                                    class="form-control form-control-sm number-area input-quantity-wanted {if $t.variable_name}{$t.variable_name}{else}{$t.variable_name}{/if} input_for_url"
                                    value="{$t.p_minimum}" {if $t.p_minimum } min="{$t.p_minimum}" {else} min="0" {/if}
                                    {if $t.p_maximum } max="{$t.p_maximum}" {/if} data-variable_id="{$t.id_variable}" data-product_variable_id="{$t.id_product_variable|intval}"
                                    data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                                    step="{if $t.multiplier != 0}{$t.multiplier}{else}{1}{/if}"
                                    data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                                <div class="input-group-prepend">
                                    <a class="plus qty-up" id="plus-btn"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                            <div class="custom_input_error"></div>

                        {elseif $t.type == 5}
                            <div class="text-label">
                                <input type="text" id="custom_text_input" name="variable_{$t.id_product_variable|intval}"
                                    class="form-control form-control-sm {$t.variable_name} input_for_url" value=""
                                    data-variable_id="{$t.id_variable}" data-product_variable_id="{$t.id_product_variable|intval}" data-variable-name="{$t.variable_name}"
                                    data-formula-name="{$t.formula_name}"
                                    data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                            </div>
                        {elseif $t.type == 6}
                            <div class="text-label">
                                <input type="text" id="thickness_input" readonly name="variable_{$t.id_product_variable|intval}"
                                    class="form-control form-control-sm {$t.variable_name} input_for_url" value=""
                                    data-variable_id="{$t.id_variable}" data-product_variable_id="{$t.id_product_variable|intval}" data-variable-name="{$t.variable_name}"
                                    data-formula-name="{$t.formula_name}"
                                    data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                            </div>
                        {/if}
                    </div>
                </div>

            {/foreach}
            <div class="left-frm-section-label">Additional Information</div>
            <div class="right-frm-section">
                <div class="text-label">
                    <textarea id="additional_information" name="additional_information"
                        class="form-control form-control-sm additional_information"></textarea>
                </div>
            </div>

            <div class="custom-file-container frm-section text-start" data-upload-id="myUniqueUploadId">
                <label>Upload File <a href="javascript:void(0)" class="custom-file-container__image-clear"
                        title="Clear Image">&times;</a></label>
                <label class="custom-file-container__custom-file">
                    <input type="file" class="custom-file-container__custom-file__custom-file-input attachments_images" accept="*" multiple max="2"
                        aria-label="Choose File">
                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
                    <span class="custom-file-container__custom-file__custom-file-control"></span>
                </label>
                <div class="custom-file-container__image-preview"></div>
            </div>
            {* <div class="upload_file frm-section text-start">
                <label class="upload_file_label">Upload Files</label>
                <input type="file" class="form-control" id="fileInput" accept=".jpg,.jpeg,.png,.pdf" multiple />
                <div class="error" id="error"></div>
                <div class="preview" id="preview">
                    <img class="default_image" src="{$base_dir}img/download.png" />
                </div>
            </div> *}
        </form>


        <div class="frm-section">
            <div class="calculate-price-green">
                <a href="#" class="add_to_quote_list">{l s='Add to quote list'
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
    {* {$html_content nofilter} *}
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

{$js nofilter}

<script>
    $(document).ready(function() {
        rulesForbannedComb();
        $(document).on('change', '.btn-select-value, .input_for_url', function() {
            rulesForbannedComb();
        });

        var upload = new FileUploadWithPreview('myUniqueUploadId')

        $(document).on(
            "click",
            ".quantity-label .qty-up, .quantity-label .qty-down",
            function(event) {
                var $btn = $(event.target);
                var $td = $btn.closest(".quantity-label");
                var $input = $td.find(".input-quantity-wanted");
                //console.log($input.attr("data-variable_id"));

                var qty = parseInt($input.val()) || 0;
                var minQty = "";
                var maxQty = "";
                if ($input.attr("max")) {
                    maxQty = parseInt($input.attr("max"));
                }
                if ($input.attr("min")) {
                    minQty = parseInt($input.attr("min"));
                }
                var multiplier = parseInt($input.attr("step"));

                var multiplyByMinQty = 1 === $input.data("multiply-by-min-qty");

                var up = $btn.hasClass("qty-up") || $btn.parent().hasClass("qty-up");
                $total_qty = 0;
                if (up) {
                    if (maxQty != "" && minQty != "") {
                        if (qty < minQty) {
                            qty = minQty;
                            $total_qty = $total_qty;
                        } else if (qty > maxQty) {
                            qty = maxQty;
                            $(".custom_input_error").html("");
                        } else if (qty < maxQty) {
                            if (multiplyByMinQty && minQty > 1) {
                                if (qty + minQty <= maxQty) {
                                    qty += minQty;
                                }
                            } else {
                                qty += multiplier;
                            }
                            $total_qty = $total_qty + multiplier;
                            $(".custom_input_error").html("");
                        }
                    } else {
                        qty++;
                    }
                } else {
                    if (maxQty != "" && minQty != "") {
                        if (qty < minQty) {
                            qty = minQty;
                            $total_qty = $total_qty;
                        } else if (qty > maxQty) {
                            qty = maxQty;
                            $(".custom_input_error").html("");
                        } else if (qty <= minQty) {
                            qty = minQty;
                            $(".custom_input_error").html("");
                        } else if (qty > 0) {
                            if (multiplyByMinQty && minQty > 1) {
                                if (qty - minQty >= 0) {
                                    qty -= minQty;
                                }
                            } else {
                                qty -= multiplier;
                            }
                            $total_qty = $total_qty - multiplier;
                            $(".custom_input_error").html("");
                        }
                    } else {
                        qty--;
                    }
                }

                //console.log(qty);

                if ($total_qty < 0) {
                    $total_qty = 0;
                }

                $(".product-quantity .total_qty").text($total_qty);

                $input.prop("value", qty);
                $input.change();
                conditionsForBanedComb();
            }
        );
    })
</script>