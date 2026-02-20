$(document).ready(function() {
    setTimeout(function() {
        rulesForbannedComb();
        // alert_messages_combination();
        add_color_when_not_selected();
    }, 100);
    $(".btn-select").each(function(e) {
        var value = $(this).find("ul li.selected").html();
        var value_input = $(this).find("ul li.selected").data("id_option");

        if (value != undefined) {
            $(this).find(".btn-select-input").val(value_input);
            $(this).find(".btn-select-value").html(value);

            prestashop.emit("ppc-select-updated", {
                value,
                value_input,
                name: $(this).find(".btn-select-input").attr("data-formula-name"),
            });
        }
    });
});

function calculate_totals($table) {
    var params = $("#kd_form").serialize();

    $.ajax({
        type: "POST",
        url: kd_ajax_path,
        dataType: "json",
        data: {
            params: params,
            action: "calculate",
            current_url: window.location.href,
        },
        success: function(r) {
            var $priceLabel = $(".price_wot", $table);
            $priceLabel.html(r.price_wot);
            var $taxLabel = $(".tax", $table);
            $taxLabel.html(r.tax);

            var $totalLabel = $(".total", $table);
            $totalLabel.html(r.total);

            $(".calculate_totals").removeClass("spinning");
        },
        error: function(r) {
            console.warn($(r.responseText).text() || r.responseText);
        },
    });
}

$(document).on("click", ".select_box_for_url li", function(e) {
    // $('.select_box_for_url li').on('mousedown', function(event) {
    var $table = $(".configure-area");
    var flag = false;
    $(e.target)
        .parent()
        .find("li")
        .each(function(index) {
            if (this.classList.contains("disabled")) {
                flag = true;
            }
        });
    if (!flag) {
        setTimeout(function() {
            rulesForbannedComb();
            alert_messages_combination(e.target);
            calculate_totals($table);
        }, 100);
    }
    add_color_when_not_selected();
});

$(document).on("click", ".btn-select", function(e) {
    e.preventDefault();
    var ul = $(this).find("ul");
    if ($(this).hasClass("active")) {
        if (ul.find("li").is(e.target)) {
            var target = $(e.target);
            target.addClass("selected").siblings().removeClass("selected");
            var value = target.html();
            var value_input = target.data("id_option");

            $(this).find(".btn-select-input").val(value_input);
            $(this).find(".btn-select-value").html(value);
            ul.hide();
            $(this).removeClass("active");

            prestashop.emit("ppc-select-updated", {
                value,
                value_input,
                name: $(this).find(".btn-select-input").attr("data-formula-name"),
            });
            // conditionsForBanedComb();
        }
    } else {
        $(".btn-select")
            .not(this)
            .each(function() {
                $(this).removeClass("active").find("ul").hide();
            });
        ul.slideDown(300);
        $(this).addClass("active");
    }
    var $table = $(".configure-area");
    // calculate_totals($table);
});

$(document).on("click", function(e) {
    var target = $(e.target).closest(".btn-select");
    if (!target.length) {
        $(".btn-select").removeClass("active").find("ul").hide();
        // } else {
        //     conditionsForBanedComb();
    }
});

function rulesForbannedComb2() {
    if ($(".Size li.selected").text() == "A3") {
        $(".Colour").parent().addClass("disabled");
    } else if ($(".Pages").val() >= 150) {
        $(".Perforation").parent().addClass("disabled");
        $(".Stapled").parent().addClass("disabled");
        $(".Fold").parent().addClass("disabled");
    } else if (
        $(".Pages").val() <= 100 &&
        ($(".Material li.selected").text() === "100g/m² recycled papier FSC®" ||
            $(".Material li.selected").text() === "120g/m² recycled papier FSC®" ||
            $(".Material li.selected").text() === "160g/m² recycled papier FSC®")
    ) {
        $(".Perforation").parent().addClass("disabled");
        $(".Stapled").parent().addClass("disabled");
        $(".Fold").parent().addClass("disabled");
    } else {
        $(".Perforation").parent().removeClass("disabled");
        $(".Stapled").parent().removeClass("disabled");
        $(".Fold").parent().removeClass("disabled");
        $(".Colour").parent().removeClass("disabled");
    }
}

function conditionsForBanedComb($table) {
    var folding = $(".Folding");
    var stapling = $(".Stapling");
    var hidden_folding = $("#Folding");
    var hidden_stapling = $("#Stapling");

    rulesForbannedComb();
    // alert_messages_combination();

    if (folding.parent().hasClass("disabled")) {
        $(".dropdown_error .disabled .btn-select-value.Folding").text(
            "Niet vouwen"
        );
        $(".dropdown_error .disabled ul.Folding li.selected").removeClass(
            "selected"
        );
        $(".dropdown_error .disabled ul.Folding li:first-child").addClass(
            "selected"
        );
        hidden_folding.val(
            $(".dropdown_error .disabled ul.Folding li:first-child").data("id_option")
        );
    }

    //if disabled, show first option in stapling
    if (stapling.parent().hasClass("disabled")) {
        $(".dropdown_error .disabled .btn-select-value.Stapling").text(
            "Geen nietjes"
        );
        $(".dropdown_error .disabled ul.Stapling li.selected").removeClass(
            "selected"
        );
        $(".dropdown_error .disabled ul.Stapling li:first-child").addClass(
            "selected"
        );
        hidden_stapling.val(
            $(".dropdown_error .disabled ul.Stapling li:first-child").data(
                "id_option"
            )
        );
    }

    var params = $("#kd_form").serialize();

    $.ajax({
        type: "POST",
        url: kd_ajax_path,
        dataType: "json",
        data: {
            params: params,
            action: "calculate",
            current_url: window.location.href,
        },
        success: function(r) {
            var $priceLabel = $(".price_wot", $table);
            $priceLabel.html(r.price_wot);
            var $taxLabel = $(".tax", $table);
            $taxLabel.html(r.tax);

            var $totalLabel = $(".total", $table);
            $totalLabel.html(r.total);

            $(".calculate_totals").removeClass("spinning");
        },
        error: function(r) {
            console.warn($(r.responseText).text() || r.responseText);
        },
    });

    add_color_when_not_selected();

    // That the total price gets updated, every time you select a new option.
}

function round(value, exp) {
    if (typeof exp === "undefined" || +exp === 0) return Math.round(value);

    value = +value;
    exp = +exp;

    if (isNaN(value) || !(typeof exp === "number" && exp % 1 === 0)) return NaN;

    // Shift
    value = value.toString().split("e");
    value = Math.round(+(value[0] + "e" + (value[1] ? +value[1] + exp : exp)));

    // Shift back
    value = value.toString().split("e");
    return +(value[0] + "e" + (value[1] ? +value[1] - exp : -exp));
}

function getPricePerItemForBlank(qty) {
    var price = 0;

    var blank_price = [];
    obj = Object.keys(blank_price);
    for (var x in obj) {
        if (obj[x] == qty || obj[x] < qty) {
            price = blank_price[obj[x]];
        }
    }

    return price;
}

function getPricePerItemForCustom(qty) {
    var price = 0;

    obj = Object.keys(custom_price);
    for (var x in obj) {
        if (obj[x] == qty || obj[x] < qty) {
            price = custom_price[obj[x]];
        }
    }

    return price;
}

(function($) {
    function init() {
        var $table;
        var $total_qty;
        var $total_amount;
        var $total_time;

        function formatPrice(price, decimal, group, sign, signFirst, signBlank) {
            var result = price
                .toFixed(2)
                .replace(".", decimal)
                .replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1" + group);
            var blank = signBlank ? " " : "";

            if (signFirst) {
                result = sign + blank + result;
            } else {
                result = result + blank + sign;
            }

            return result;
        }

        $(document).ready(function() {
            $table = $(".configure-area");
            calculate_totals($table);
            $total_qty = parseInt($(".product-quantity .total_qty").text());
            if (0 === $table.length) {
                return;
            }
            $table.on("click", ".main-add-to-cart", function(event) {
                add_color_when_not_selected();

                var qty = parseInt($("#qty_input").val()) || 0;
                var getCartUrl = $("#add-to-cart-or-refresh").val();
                id_customization = 0;
                var id_product_attribute = 0;
                var product_data = $("#product-details").data("product");
                id_product_attribute = product_data.id_product_attribute;
                $("#kd_id_product_attribute").val(id_product_attribute);

                if (qty == 0) {
                    alert("Please add quantity");
                    return false;
                }
                //$(this).text("Adding to cart");
                $(this).addClass("spinning");

                // if (!$("#kd_id_customization").val()) {
                //     $.post(getCartUrl + '?rand=' + new Date().getTime(), {
                //             action: "update",
                //             id_product: parseInt($('#kd_id_product').val()),
                //             id_product_attribute: parseInt(id_product_attribute),
                //             ajax: true,
                //             qty: parseInt($('#qty_input').val()),
                //             headers: {
                //                 "cache-control": "no-cache"
                //             },
                //             async: false,
                //             token: prestashop["static_token"],
                //             add: 1,
                //             cache: false,
                //         },
                //         null,
                //         'json').then(function(resp) {
                //         prestashop.emit('updatedCart', { eventType: 'updateCart', resp: resp });
                //     });
                // }

                setTimeout(function() {
                    var params = $("#kd_form").serialize();
                    $.ajax({
                        type: "POST",
                        url: kd_ajax_path,
                        dataType: "json",
                        async: false, //add
                        data: {
                            params: params,
                            action: "addtocart",
                            current_url: window.location.href,
                        },
                        success: function(r) {

                            $(".main-add-to-cart").removeClass("spinning");
                            if (!r.error) {
                                id_customization = r.id_customization;
                                console.log("inside ajax",id_customization);
                                
                            } else {
                                // alert(r.error);
                            }

                            // window.location.href = getCartUrl;
                        },
                        error: function(r) {
                            console.warn($(r.responseText).text() || r.responseText);
                        },
                    });

                    prestashop.blockcart = prestashop.blockcart || {};

                    var showModal =
                        prestashop.blockcart.showModal ||
                        function(modal) {
                            var $body = $("body");
                            $body.append(modal);
                            $body.one("click", "#blockcart-modal", function(event) {
                                if (event.target.id === "blockcart-modal") {
                                    $(event.target).remove();
                                }
                            });
                        };

                    var refreshURL = $(".blockcart").data("refresh-url");
                    var requestData = {};

                    var customizationInput = document.querySelector(
                        "#product_customization_id"
                    );
                    console.log("outside ajax after",id_customization);
                    if (customizationInput) id_customization = customizationInput.value;

                    console.log("outside ajax before",id_customization);
                    requestData = {
                        id_product_attribute: parseInt(id_product_attribute),
                        id_product: $("#kd_id_product").val(),
                        action: "add-to-cart",
                        id_customization: id_customization,
                        // price: $('.right-frm-section-price.total').text()
                    };

                    refreshURL = "https:" + refreshURL;

                    $.post(refreshURL, requestData)
                        .then(function(resp) {
                            $(".blockcart").replaceWith($(resp.preview).find(".blockcart"));
                            if (resp.modal) {
                                showModal(resp.modal);
                                $(this).addClass("disable");

                                setTimeout(function() {
                                    // window.location = getCartUrl;
                                }, 500);
                            }
                        })
                        .fail(function(resp) {
                            prestashop.emit("handleError", {
                                eventType: "updateShoppingCart",
                                resp: resp,
                            });
                        });
                }, 500);

                return false;
            });

            $table.on("click", ".calculate_totals", function(event) {
                $(this).addClass("spinning");

                var params = $("#kd_form").serialize();

                $.ajax({
                    type: "POST",
                    url: kd_ajax_path,
                    dataType: "json",
                    data: {
                        params: params,
                        action: "calculate",
                        current_url: window.location.href,
                    },
                    success: function(r) {
                        var $priceLabel = $(".price_wot", $table);
                        $priceLabel.html(r.price_wot);
                        var $taxLabel = $(".tax", $table);
                        $taxLabel.html(r.tax);

                        var $totalLabel = $(".total", $table);
                        $totalLabel.html(r.total);

                        $(".calculate_totals").removeClass("spinning");
                    },
                    error: function(r) {
                        console.warn($(r.responseText).text() || r.responseText);
                    },
                });
                add_color_when_not_selected();

                return false;
            });

            $table.on("change", ".dropdown", function(event) {
                var $input = $(this);
                var $td = $input.closest(".quantity-label");
                var $sum = $td.find(".sum-label");
                var qty = parseInt($input.val()) || 0;
                var maxQty = parseInt($input.attr("max"));
                var minQty = parseInt($input.attr("min"));
                var multiplyByMinQty = 1 === $input.data("multiply-by-min-qty");
                var getPriceUrl = $input.data("get-price-url");

                if (qty < 0) {
                    qty = minQty;
                } else if (qty > maxQty) {
                    qty = maxQty;
                }

                if (multiplyByMinQty && minQty > 1) {
                    var remain = qty % minQty;
                    if (0 !== remain) {
                        qty -= remain;
                    }
                }

                $input.prop("value", qty);

                $total_qty = 0;

                $(".configure-area .input-quantity-wanted").each(function() {
                    var qty2 = $(this).val();
                    $total_qty = $total_qty + parseInt(qty2);
                });

                $(".product-quantity .total_qty").text($total_qty);

                var $price_per_item = getPricePerItemForBlank($total_qty);
                //alert($price_per_item);
                var totalResult = parseFloat($price_per_item) * $total_qty;
                if ($total_qty > 1) {
                    var $priceLabel = $(".current-price span", $table);
                    $priceLabel.html(prestashop.currency.sign + $price_per_item);
                }

                totalResult = round(totalResult, 2);

                var $totalLabel = $(".total_amount .total", $table);
                $totalLabel.html(prestashop.currency.sign + totalResult);
                // conditionsForBanedComb();
            });

            $table.on(
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

            $table.on("blur", ".input-quantity-wanted", function(event) {
                var inputValue = parseInt($(this).val());
                var minValue = parseInt($(this).attr("min"));
                var maxValue = parseInt($(this).attr("max"));
                var multiplier = parseInt($(this).attr("step"));
                var remains = 0;
                console.log(inputValue, minValue, maxValue, inputValue % multiplier);
                if (event.key === "-") {
                    event.preventDefault();
                }

                if (
                    multiplier > 1 &&
                    inputValue < maxValue &&
                    inputValue % multiplier !== 0
                ) {
                    remains = inputValue % multiplier;
                    inputValue = inputValue - remains;
                    inputValue = inputValue + multiplier;
                    $(this).val(inputValue);
                    $(this)
                        .parent()
                        .next(".custom_input_error")
                        .html(
                            "<span class='text-danger'>Number should be multipal of  " +
                            multiplier +
                            "</span>"
                        );

                    $(".main-add-to-cart").addClass("disable_add_to_cart");
                    $(".calculate-price-green").addClass("cursor_not_allowed");
                    return false;
                } else {
                    $(this).parent().next(".custom_input_error").html("");
                    $(".main-add-to-cart").removeClass("disable_add_to_cart");
                    $(".calculate-price-green").removeClass("cursor_not_allowed");
                }

                if (isNaN(inputValue)) {
                    $(this).val(minValue);
                } else if (inputValue < minValue) {
                    $(this).val(minValue);
                    $(this)
                        .parent()
                        .next(".custom_input_error")
                        .html(
                            "<span class='text-danger'>Minimum number is " +
                            minValue +
                            "</span>"
                        );

                    $(".main-add-to-cart").addClass("disable_add_to_cart");
                    $(".calculate-price-green").addClass("cursor_not_allowed");
                    return false;
                } else if (inputValue > maxValue) {
                    $(this).val(maxValue);
                    $(this)
                        .parent()
                        .next(".custom_input_error")
                        .html(
                            "<span class='text-danger'>Maximum number is " +
                            maxValue +
                            "</span>"
                        );
                    $(".main-add-to-cart").addClass("disable_add_to_cart");
                    $(".calculate-price-green").addClass("cursor_not_allowed");
                    return false;
                } else {
                    $(this).parent().next(".custom_input_error").html("");
                    $(".main-add-to-cart").removeClass("disable_add_to_cart");
                    $(".calculate-price-green").removeClass("cursor_not_allowed");
                }
                conditionsForBanedComb();
            });
        });
    }

    init();
})(jQuery);

// $('.calculate_totals').on('click', function(e) {
//     e.preventDefault();
//     add_color_when_not_selected();

// });

// $(".btn-select-value").change(function(){
//     alert("The text has been changed.");
// });

function add_color_when_not_selected() {
    // Initialize errorfound variable
    var errorfound = false;

    // Loop through each ".btn-select-value" element
    $(".btn-select-value").each(function() {
        var $parent = $(this).parent();

        // Check if the parent does not have the "disabled" class
        setTimeout(() => {
            if (!$parent.hasClass("disabled")) {
                if (
                    $(this).text() == "Bitte auswählen" ||
                    $(this).text() == "Kies een optie" ||
                    $(this).text() == "Choose an option"
                ) {
                    $(this).addClass("text-danger");
                    $(".calculate-error").html(
                        "<div class='left-frm-section pull-left required-error' style='font-weight: normal;color: red;width: 50%;'><span>Bitte wählen Sie alle erforderlichen Felder aus</span></div>"
                    );
                    errorfound = true;
                } else {
                    $(this).removeClass("text-danger");
                    $(".required-error").hide();
                }
            }
        }, 100);
    });
    // Check for the presence of ".custom_input_error" elements
    $(".configure-area .custom_input_error").each(function() {
        if (!$(this).is(":empty")) {
            errorfound = true;
            return false; // Exit the loop if a non-empty element is found
        }
    });
    // Toggle classes based on errorfound value
    if (errorfound) {
        $(".main-add-to-cart, .calculate-price-green").addClass(
            "disable_add_to_cart cursor_not_allowed"
        );
    } else {
        $(".main-add-to-cart, .calculate-price-green").removeClass(
            "disable_add_to_cart cursor_not_allowed"
        );
    }
}

function add_color_when_not_selected2() {
    var errorfound = false;
    $(".btn-select-value").each(function() {
        if (!$(this).parent().hasClass("disabled")) {
            if (
                $(this).text() == "Bitte auswählen" ||
                $(this).text() == "Kies een optie" ||
                $(this).text() == "Choose an option"
            ) {
                $(this).addClass("text-danger");
                $(".calculate-error").html(
                    "<div class='left-frm-section pull-left required-error' style='font-weight: normal;color: red;width: 50%;'><span>Bitte wählen Sie alle erforderlichen Felder aus</span></div>"
                );
                errorfound = true;
            } else {
                $(this).removeClass("text-danger");
                $(".required-error").hide();
            }
        }

        if (errorfound) {
            $(".main-add-to-cart").addClass("disable_add_to_cart");
            $(".calculate-price-green").addClass("cursor_not_allowed");
        } else {
            $(".main-add-to-cart").removeClass("disable_add_to_cart");
            $(".calculate-price-green").removeClass("cursor_not_allowed");
        }
    });

    errorfound = $(".configure-area .custom_input_error").length > 0;

    if (errorfound) {
        $(".main-add-to-cart").addClass("disable_add_to_cart");
        $(".calculate-price-green").addClass("cursor_not_allowed");
    } else {
        $(".main-add-to-cart").removeClass("disable_add_to_cart");
        $(".calculate-price-green").removeClass("cursor_not_allowed");
    }
}

$(document).ready(function() {
    if ($("body").attr("id") === "product") {
        var currentParams = new URLSearchParams(window.location.search);

        var variable_form = $("#kd_form");
        var inputs = variable_form.find(".input_for_url");
        $("#loader").show();

        setTimeout(function() {
            currentParams.forEach(function(value, key) {
                $(inputs).each(function(index) {
                    if (
                        $(this).data("formula-name").replace(/\s/g, "_").toLowerCase() ==
                        key.toLowerCase()
                    ) {
                        if ($(this).data("type") == 1) {
                            $(this).val(value);
                        } else if ($(this).data("type") == 2) {
                            $(this).val(value);

                            // $(this)
                            //     .parents()
                            //     .find(
                            //         'ul[data-formula-name="' +
                            //         key +
                            //         '"] li[data-id_option="' +
                            //         value +
                            //         '"]'
                            //     )
                            //     .addClass("selected")
                            //     .siblings()
                            //     .removeClass("selected");
                            $(this).parents().find('select.btn-select-value[data-formula-name="'+key+'"]').val(value).trigger('change');
                            var option_name = $(this)
                                .parents()
                                .find(
                                    'ul[data-formula-name="' +
                                    key +
                                    '"] li[data-id_option="' +
                                    value +
                                    '"]'
                                )
                                .text();

                            $(this)
                                .parents()
                                .find(
                                    "span[data-formula-name='" +
                                    $(this).data("formula-name").replace(/\s/g, ".") +
                                    "']"
                                )
                                .text(option_name);

                            $(this)
                                .parents()
                                .find(
                                    "input[data-formula-name='" +
                                    $(this).data("formula-name") +
                                    "']"
                                )
                                .val(value);
                        } else if ($(this).data("type") == 3) {} else if ($(this).data("type") == 4) {
                            $(this).val(value);
                        } else if ($(this).data("type") == 5) {
                            $(this).val(value);
                        }
                    }
                });
            });

            $(inputs).each(function(index) {
                if ($(this).data("type") == 1) {
                    $(this).on("input", function() {
                        var inputValue = parseInt($(this).val());
                        var minValue = parseInt($(this).attr("min"));
                        var maxValue = parseInt($(this).attr("max"));

                        var param_value = inputValue;
                        if (isNaN(inputValue)) {
                            param_value = minValue;
                        } else if (inputValue < minValue) {
                            param_value = minValue;
                        } else if (inputValue > maxValue) {
                            param_value = maxValue;
                        }

                        var param_name = $(this)
                            .data("formula-name")
                            .replace(/\s/g, ".")
                            .toLowerCase();

                        currentParams.set(param_name, param_value);
                        var updatedURL =
                            window.location.origin +
                            window.location.pathname +
                            "?" +
                            currentParams.toString();
                        history.replaceState({}, "", updatedURL);
                    });
                    $(document).on("click", "#plus-btn,#minus-btn", function() {
                        var param_value = $("#qty_input").val();
                        var param_name = $("#qty_input")
                            .data("formula-name")
                            .replace(/\s/g, ".")
                            .toLowerCase();
                        currentParams.set(param_name, param_value);
                        var updatedURL =
                            window.location.origin +
                            window.location.pathname +
                            "?" +
                            currentParams.toString();
                        history.replaceState({}, "", updatedURL);
                    });
                } else if ($(this).data("type") == 2) {
                    $(this)
                        .parents(".dropdown")
                        .on("click", function(e) {
                            e.preventDefault();
                            var ul = $(this).find("ul");
                            if ($(this).hasClass("active")) {
                                if (ul.find("li").is(e.target)) {
                                    var target = $(e.target);
                                    setTimeout(() => {
                                        var param_value = $(target)
                                            .parents(".btn-select")
                                            .find(".input_for_url")
                                            .val();
                                        var param_name = $(target)
                                            .parents(".btn-select")
                                            .find(".input_for_url")
                                            .data("formula-name")
                                            .replace(/\s/g, "_")
                                            .toLowerCase();
                                        currentParams.set(param_name, param_value);
                                        var updatedURL =
                                            window.location.origin +
                                            window.location.pathname +
                                            "?" +
                                            currentParams.toString();
                                        history.replaceState({}, "", updatedURL);
                                    }, 100);
                                }
                            }
                        });
                } else if ($(this).data("type") == 3) {} else if ($(this).data("type") == 4) {
                    $(this).on("input", function() {
                        var inputValue = parseInt($(this).val());
                        var minValue = parseInt($(this).attr("min"));
                        var maxValue = parseInt($(this).attr("max"));

                        var param_value = inputValue;
                        if (isNaN(inputValue)) {
                            param_value = minValue;
                        } else if (inputValue < minValue) {
                            param_value = minValue;
                        } else if (inputValue > maxValue) {
                            param_value = maxValue;
                        }

                        // var param_value = $(this).val();
                        var param_name = $(this)
                            .data("formula-name")
                            .replace(/\s/g, ".")
                            .toLowerCase();
                        currentParams.set(param_name, param_value);
                        var updatedURL =
                            window.location.origin +
                            window.location.pathname +
                            "?" +
                            currentParams.toString();
                        history.replaceState({}, "", updatedURL);
                    });
                    $(document).on("click", "#plus-btn,#minus-btn", function() {
                        var param_value = $("#custom_input").val();
                        var param_name = $("#custom_input")
                            .data("formula-name")
                            .replace(/\s/g, ".")
                            .toLowerCase();
                        currentParams.set(param_name, param_value);
                        var updatedURL =
                            window.location.origin +
                            window.location.pathname +
                            "?" +
                            currentParams.toString();
                        history.replaceState({}, "", updatedURL);
                    });
                } else if ($(this).data("type") == 5) {
                    $(this).on("input", function() {
                        var param_value = $(this).val();
                        var param_name = $(this)
                            .data("formula-name")
                            .replace(/\s/g, ".")
                            .toLowerCase();
                        currentParams.set(param_name, param_value);
                        var updatedURL =
                            window.location.origin +
                            window.location.pathname +
                            "?" +
                            currentParams.toString();
                        history.replaceState({}, "", updatedURL);
                    });
                }
            });
            rulesForbannedComb();
            add_color_when_not_selected();
            $table = $(".configure-area");
            var params = $("#kd_form").serialize();

            $.ajax({
                type: "POST",
                url: kd_ajax_path,
                dataType: "json",
                data: {
                    params: params,
                    action: "calculate",
                    current_url: window.location.href,
                },
                success: function(r) {
                    var $priceLabel = $(".price_wot", $table);
                    $priceLabel.html(r.price_wot);
                    var $taxLabel = $(".tax", $table);
                    $taxLabel.html(r.tax);

                    var $totalLabel = $(".total", $table);
                    $totalLabel.html(r.total);

                    $(".calculate_totals").removeClass("spinning");
                    $("#loader").hide();
                },
                error: function(r) {
                    console.warn($(r.responseText).text() || r.responseText);
                },
            });
        }, 1000);
    }
});

$(document).on("change", 'input.btn-select-input[type="hidden"]', function() {
    $table = $(".configure-area");
    var params = $("#kd_form").serialize();

    $.ajax({
        type: "POST",
        url: kd_ajax_path,
        dataType: "json",
        data: {
            params: params,
            action: "calculate",
            current_url: window.location.href,
        },
        success: function(r) {
            var variable_form = $("#kd_form");
            var inputs = variable_form.find('input.btn-select-input[type="hidden"]');
            $(inputs).each(function(index) {
                if ($(this).data("type") == 2) {
                    var hidden_value = $(this).val();
                    $(this)
                        .parents(".btn-select.dropdown")
                        .find("ul.select_box_for_url li")
                        .removeClass("selected");
                    var new_hidden_value_text = $(this)
                        .parents(".btn-select.dropdown")
                        .find(
                            'ul.select_box_for_url li[data-id_option="' + hidden_value + '"]'
                        )
                        .text();
                    $(this)
                        .parents(".btn-select.dropdown")
                        .find("span.btn-select-value")
                        .text(new_hidden_value_text);
                    $(this)
                        .parents(".btn-select.dropdown")
                        .find(
                            'ul.select_box_for_url li[data-id_option="' + hidden_value + '"]'
                        )
                        .addClass("selected");
                }
            });

            var $priceLabel = $(".price_wot", $table);
            $priceLabel.html(r.price_wot);
            var $taxLabel = $(".tax", $table);
            $taxLabel.html(r.tax);

            var $totalLabel = $(".total", $table);
            $totalLabel.html(r.total);

            $(".calculate_totals").removeClass("spinning");
            $("#loader").hide();
        },
        error: function(r) {
            console.warn($(r.responseText).text() || r.responseText);
        },
    });
});