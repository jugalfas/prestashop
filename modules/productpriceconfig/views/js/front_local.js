$(document).ready(function() {
    $(".btn-select").each(function(e) {
        var value = $(this).find("ul li.selected").html();
        var value_input = $(this).find("ul li.selected").data('id_option');

        if (value != undefined) {
            $(this).find(".btn-select-input").val(value_input);
            $(this).find(".btn-select-value").html(value);
        }
    });
});

$(document).on('click', '.btn-select', function(e) {
    e.preventDefault();
    var ul = $(this).find("ul");
    if ($(this).hasClass("active")) {
        if (ul.find("li").is(e.target)) {
            var target = $(e.target);
            target.addClass("selected").siblings().removeClass("selected");
            var value = target.html();
            var value_input = target.data('id_option');

            $(this).find(".btn-select-input").val(value_input);
            $(this).find(".btn-select-value").html(value);
        }
        ul.hide();
        $(this).removeClass("active");
    } else {
        $('.btn-select').not(this).each(function() {
            $(this).removeClass("active").find("ul").hide();
        });
        ul.slideDown(300);
        $(this).addClass("active");
    }
});

$(document).on('click', function(e) {
    var target = $(e.target).closest(".btn-select");
    if (!target.length) {
        $(".btn-select").removeClass("active").find("ul").hide();
    }
});

function round(value, exp) {
    if (typeof exp === 'undefined' || +exp === 0)
        return Math.round(value);

    value = +value;
    exp = +exp;

    if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0))
        return NaN;

    // Shift
    value = value.toString().split('e');
    value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp)));

    // Shift back
    value = value.toString().split('e');
    return +(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp));
}

function getPricePerItemForBlank(qty) {
    var price = 0;

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
            var result = price.toFixed(2).replace('.', decimal).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1' + group);
            var blank = (signBlank ? ' ' : '');

            if (signFirst) {
                result = sign + blank + result;
            } else {
                result = result + blank + sign;
            }

            return result;
        }



        $(document).ready(function() {
            $table = $('.configure-area');
            $total_qty = parseInt($('.product-quantity .total_qty').text());
            if (0 === $table.length) {
                return;
            }

            $table.on('click', '.main-add-to-cart', function(event) {

                var qty = parseInt($('#qty_input').val()) || 0;

                var getCartUrl = $('#add-to-cart-or-refresh').attr('action');

                var id_customization = 0;

                if (qty == 0) {
                    alert('Please add quantity');
                    return false;
                }

                $(this).text('Adding to cart');

                $(this).addClass('spinning');


                $.post(getCartUrl + '?rand=' + new Date().getTime(), {
                        action: "update",
                        id_product: parseInt($('#kd_id_product').val()),
                        //id_product_attribute: parseInt($('#kd_id_product_setting').val()),
                        ajax: true,
                        qty: parseInt($('#qty_input').val()),
                        headers: {
                            "cache-control": "no-cache"
                        },
                        async: false,
                        token: prestashop["static_token"],
                        add: 1,
                        cache: false,
                    },
                    null,
                    'json').then(function(resp) {
                    prestashop.emit('updatedCart', { eventType: 'updateCart', resp: resp });

                });



                setTimeout(function() {


                    var params = $('#kd_form').serialize();

                    $.ajax({
                        type: 'POST',
                        url: kd_ajax_path,
                        dataType: 'json',
                        data: {
                            params: params,
                            action: 'addtocart',
                            current_url: window.location.href
                        },
                        success: function(r) {

                            $('.main-add-to-cart').removeClass('spinning');
                            if (!r.error) {
                                id_customization = r.id_customization;
                            } else {
                                alert(r.error);
                            }


                        },
                        error: function(r) {
                            console.warn($(r.responseText).text() || r.responseText);
                        }
                    });

                }, 500);


                prestashop.blockcart = prestashop.blockcart || {};

                var showModal = prestashop.blockcart.showModal || function(modal) {
                    var $body = $('body');
                    $body.append(modal);
                    $body.one('click', '#blockcart-modal', function(event) {
                        if (event.target.id === 'blockcart-modal') {
                            $(event.target).remove();
                        }
                    });
                };


                var refreshURL = $('.blockcart').data('refresh-url');
                var requestData = {};

                requestData = {
                    id_product_attribute: 0,
                    id_product: $('#kd_id_product').val(),
                    action: 'add-to-cart',
                    id_customization: id_customization,
                };


                $.post(refreshURL, requestData).then(function(resp) {
                    $('.blockcart').replaceWith($(resp.preview).find('.blockcart'));
                    if (resp.modal) {
                        showModal(resp.modal);
                        $(this).addClass('disable');

                    }
                }).fail(function(resp) {
                    prestashop.emit('handleError', { eventType: 'updateShoppingCart', resp: resp });
                });



                return false;
            });

            $table.on('click', '.calculate_totals', function(event) {

                $(this).addClass('spinning');

                var params = $('#kd_form').serialize();

                $.ajax({
                    type: 'POST',
                    url: kd_ajax_path,
                    dataType: 'json',
                    data: {
                        params: params,
                        action: 'calculate',
                        current_url: window.location.href
                    },
                    success: function(r) {

                        var $priceLabel = $('.price_wot', $table);
                        $priceLabel.html(r.price_wot);
                        var $taxLabel = $('.tax', $table);
                        $taxLabel.html(r.tax);

                        var $totalLabel = $('.total', $table);
                        $totalLabel.html(r.total);

                        $('.calculate_totals').removeClass('spinning');

                    },
                    error: function(r) {
                        console.warn($(r.responseText).text() || r.responseText);
                    }
                });

                return false;

            });


            $table.on('change', '.dropdown', function(event) {
                var $input = $(this);
                var $td = $input.closest('.quantity-label');
                var $sum = $td.find('.sum-label');
                var qty = parseInt($input.val()) || 0;
                var maxQty = parseInt($input.attr('max'));
                var minQty = parseInt($input.attr('min'));
                var multiplyByMinQty = (1 === $input.data('multiply-by-min-qty'));
                var getPriceUrl = $input.data('get-price-url');

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

                $input.prop('value', qty);

                $total_qty = 0;

                $('.configure-area .input-quantity-wanted').each(function() {

                    var qty2 = $(this).val();
                    $total_qty = $total_qty + parseInt(qty2);

                });

                $('.product-quantity .total_qty').text($total_qty);

                var $price_per_item = getPricePerItemForBlank($total_qty);
                //alert($price_per_item);
                var totalResult = parseFloat($price_per_item) * $total_qty;
                if ($total_qty > 1) {
                    var $priceLabel = $('.current-price span', $table);
                    $priceLabel.html(prestashop.currency.sign + $price_per_item);
                }

                totalResult = round(totalResult, 2);

                var $totalLabel = $('.total_amount .total', $table);
                $totalLabel.html(prestashop.currency.sign + totalResult);

            });

            $table.on('click', '.quantity-label .qty-up, .quantity-label .qty-down', function(event) {
                var $btn = $(event.target);
                var $td = $btn.closest('.quantity-label');
                var $input = $td.find('.input-quantity-wanted');
                var qty = parseInt($input.val()) || 0;
                var maxQty = parseInt($input.attr('max'));
                var minQty = parseInt($input.attr('min'));
                var multiplyByMinQty = (1 === $input.data('multiply-by-min-qty'));
                var up = $btn.hasClass('qty-up') || $btn.parent().hasClass('qty-up');

                if (up) {
                    if (qty < 0) {
                        qty = minQty;
                        $total_qty = $total_qty;
                    } else if (qty > maxQty) {
                        qty = maxQty;
                    } else if (qty < maxQty) {
                        if (multiplyByMinQty && minQty > 1) {
                            if (qty + minQty <= maxQty) {
                                qty += minQty;
                            }
                        } else {
                            qty++;
                        }
                        $total_qty = $total_qty + 1;
                    }

                } else {
                    if (qty < 0) {
                        qty = minQty;
                        $total_qty = $total_qty;
                    } else if (qty > maxQty) {
                        qty = maxQty;
                    } else if (qty > 0) {
                        if (multiplyByMinQty && minQty > 1) {
                            if (qty - minQty >= 0) {
                                qty -= minQty;
                            }
                        } else {
                            qty--;
                        }
                        $total_qty = $total_qty - 1;
                    }


                }

                if ($total_qty < 0) {
                    $total_qty = 0;
                }

                $('.product-quantity .total_qty').text($total_qty);


                $input.prop('value', qty);
                $input.change();
            });

        });
    }

    init();
})(jQuery);


$('.calculate_totals').on('click', function(e) {
    e.preventDefault();
    var size = $('.btn-select-value').html();

    if (size == 'Please select') {
        $('.calculate-error').append("<div class='left-frm-section pull-left required-error' style='font-weight: normal;color: red;width: 50%;'><span>Please Select all required field</span></div>");
    } else {
        $('required-error').hide();
    }
});