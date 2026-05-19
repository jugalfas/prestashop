$(document).ready(function () {
  setTimeout(function () {
    rulesForbannedComb();
    // alert_messages_combination();
    add_color_when_not_selected();
    validateAllQuantityInputs();
    validateThicknessInput();
  }, 100);
  $(".btn-select").each(function (e) {
    var value = $(this).val();
    var value_input = $(this).find(":selected").attr("data-id_option");

    if (value != 0) {
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

function validateThicknessInput() {
  var $input = $("#thickness_input");
  var value = parseFloat($input.val());
  // Remove duplicate error divs after #thickness_input
  var $errorDivs = $input.parent().find('.custom_input_error');
  if ($errorDivs.length > 1) {
    $errorDivs.slice(1).remove();
  }
  var $errorDiv = $errorDivs.length ? $errorDivs.first() : $("<div class='custom_input_error'></div>").insertAfter($input);
  if (value === 0) {
    $errorDiv.html("<span class='text-danger'>" + (typeof errorSpineThicknessZero !== 'undefined' ? errorSpineThicknessZero : 'Spine thickness cannot be 0') + "</span>");
    $(".btn-primary").addClass("disabled").attr("disabled", "disabled");
    $(".main-add-to-cart").addClass("disable_add_to_cart");
    $(".calculate-price-green").addClass("cursor_not_allowed");
  } else {
    $errorDiv.html("");
    $(".configure-area .btn-primary").removeClass("disabled").removeAttr("disabled");
    $(".main-add-to-cart").removeClass("disable_add_to_cart");
    $(".calculate-price-green").removeClass("cursor_not_allowed");
  }
}

function validateAllQuantityInputs() {
  $(".quantity-label .input-quantity-wanted").each(function () {
    var $input = $(this);
    var value = parseInt($input.val());
    var min = parseInt($input.attr("min"));
    var max = parseInt($input.attr("max"));
    var step = parseInt($input.attr("step")) || 1;
    var errorMsg = "";
    
    if (isNaN(value)) {
      errorMsg = typeof errorValueNotValid !== 'undefined' ? errorValueNotValid : 'Value is not valid.';
      $input.val(min);
    } else if (value < min) {
      errorMsg = typeof errorMinNumber !== 'undefined' ? errorMinNumber.replace('%min%', min) : 'Minimum number is ' + min;
      $input.val(min);
    } else if (value > max) {
      errorMsg = typeof errorMaxNumber !== 'undefined' ? errorMaxNumber.replace('%max%', max) : 'Maximum number is ' + max;
      $input.val(max);
    } else if ((value - min) % step !== 0) {
      errorMsg = typeof errorAmountMultiple !== 'undefined' ? errorAmountMultiple.replace('%step%', step) : 'Amount must be a multiple of ' + step;
      // Adjust to nearest valid value
      var validValue = value - ((value - min) % step);
      $input.val(validValue);
    }

    var $errorDiv = $input.closest(".quantity-label").next(".custom_input_error");
    if (errorMsg) {
      $errorDiv.html("<span class='text-danger'>" + errorMsg + "</span>");
    } else {
      $errorDiv.html("");
    }
  });
}

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
    success: function (r) {
      var $priceLabel = $(".price_wot", $table);

      var $thickness = $("#thickness_input");
      $thickness.val(r.total_thickness);

      $priceLabel.html(r.price_wot);
      var $taxLabel = $(".tax", $table);
      $taxLabel.html(r.tax);

      var $totalLabel = $(".total", $table);
      $totalLabel.html(r.total);

      var $package_count = $(".package_count", $table);
      $package_count.html(r.total_package);
      var quantity = $("#qty_input").val();
      $(".single_unit_price span.single_unit_price_span").text(
        `: ${r.total} / ${quantity}`
      );
      $(".single_unit_price_right").text(r.price_per_qty);

      $(".calculate_totals").removeClass("spinning");
      show_unit_price();
      validateThicknessInput();
      //single_unit_price();
    },
    error: function (r) {
      console.warn($(r.responseText).text() || r.responseText);
    },
  });
}

$(document).on("click", ".select_box_for_url li", function (e) {
  // $('.select_box_for_url li').on('mousedown', function(event) {
  var $table = $(".configure-area");
  var flag = false;
  $(e.target)
    .parent()
    .find("li")
    .each(function (index) {
      if (this.classList.contains("disabled")) {
        flag = true;
      }
    });
  if (!flag) {
    setTimeout(function () {
      rulesForbannedComb();
      alert_messages_combination(e.target);
      calculate_totals($table);
    }, 100);
  }
  add_color_when_not_selected();
});
$(document).on("change", ".select_box_for_url", function (e) {
  var $table = $(".configure-area");
  var flag = false;
  // $(e.target)
  //     .parent()
  //     .find("li")
  //     .each(function(index) {
  //         if (this.classList.contains("disabled")) {
  //             flag = true;
  //         }
  //     });
  if (!flag) {
    setTimeout(function () {
      rulesForbannedComb();
      alert_messages_combination(e.target);
      calculate_totals($table);
    }, 100);
  }
  add_color_when_not_selected();
});

$(document).on("change", ".btn-select", function (e) {
  e.preventDefault();
  var value = $(this).val();
  var value_input = $(this).find(":selected").attr("data-id_option");

  $(this).parent().find(".btn-select-input").val(value_input);
  prestashop.emit("ppc-select-updated", {
    value,
    value_input,
    name: $(this).find(".btn-select-input").attr("data-formula-name"),
  });
  // var ul = $(this).find("ul");
  // if ($(this).hasClass("active")) {
  //     if (ul.find("li").is(e.target)) {
  //         var target = $(e.target);
  //         target.addClass("selected").siblings().removeClass("selected");
  //         var value = target.html();
  //         var value_input = target.data("id_option");

  //         $(this).find(".btn-select-input").val(value_input);
  //         $(this).find(".btn-select-value").html(value);
  //         ul.hide();
  //         $(this).removeClass("active");

  //         prestashop.emit("ppc-select-updated", {
  //             value,
  //             value_input,
  //             name: $(this).find(".btn-select-input").attr("data-formula-name"),
  //         });
  //         // conditionsForBanedComb();
  //     }
  // } else {
  //     $(".btn-select")
  //         .not(this)
  //         .each(function() {
  //             $(this).removeClass("active").find("ul").hide();
  //         });
  //     ul.slideDown(300);
  //     $(this).addClass("active");
  // }
  var $table = $(".configure-area");
  calculate_totals($table);
  show_unit_price();
  //single_unit_price();
});

$(document).on("click", function (e) {
  var target = $(e.target).closest(".btn-select");
  if (!target.length) {
    // $(".btn-select").removeClass("active").find("ul").hide();
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
    success: function (r) {
      var $priceLabel = $(".price_wot", $table);
      $priceLabel.html(r.price_wot);
      var $taxLabel = $(".tax", $table);
      $taxLabel.html(r.tax);

      var $totalLabel = $(".total", $table);
      $totalLabel.html(r.total);

      var $package_count = $(".package_count", $table);
      $package_count.html(r.total_package);

      var $thickness = $("#thickness_input");
      $thickness.val(r.total_thickness);

      var quantity = $("#qty_input").val();
      $(".single_unit_price span.single_unit_price_span").text(
        `: ${r.total} / ${quantity}`
      );
      $(".single_unit_price_right").text(r.price_per_qty);

      $(".calculate_totals").removeClass("spinning");
      validateThicknessInput();
      //single_unit_price(r.total, r.price_per_qty);
    },
    error: function (r) {
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

function delay(callback, ms) {
  var timer = 0;
  return function () {
    var context = this,
      args = arguments;
    clearTimeout(timer);
    timer = setTimeout(function () {
      callback.apply(context, args);
    }, ms || 0);
  };
}

(function ($) {
  function init() {
    var $table;
    var $total_qty;
    var $total_amount;
    var $total_time;
    var $timer;

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

    $(document).ready(function () {
      $table = $(".configure-area");
      calculate_totals($table);
      disable_add_to_cart();
      show_button_based_on_value();
      $total_qty = parseInt($(".product-quantity .total_qty").text());
      if (0 === $table.length) {
        return;
      }
      $table.on("click", ".main-add-to-cart", function (event) {
        $(".text-danger.alert_message_text").html("");

        rulesForbannedComb();

        add_color_when_not_selected();

        var error_found = false;

        $(".btn-select-value").each(function () {
          // console.log($(this).val());

          if ($(this).val() == 0) {
            error_found = true;
          }
        });

        if (error_found) {
          $(".text-danger.alert_message_text").html(
            '<div class="alert alert-danger ajax-error" role="alert">' +
              "Selecteer alle opties" +
              "</div>"
          );
          return false;
        }

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

        $(this).text(adding_to_cart_text);
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

        setTimeout(function () {
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
            success: function (r) {
              $(".main-add-to-cart").removeClass("spinning");
              if (!r.error) {
                id_customization = r.id_customization;
                prestashop.blockcart = prestashop.blockcart || {};

                var showModal =
                  prestashop.blockcart.showModal ||
                  function (modal) {
                    var $body = $("body");
                    $body.append(modal);
                    $body.one("click", "#blockcart-modal", function (event) {
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
                if (customizationInput)
                  id_customization = customizationInput.value;

                requestData = {
                  id_product_attribute: parseInt(id_product_attribute),
                  id_product: $("#kd_id_product").val(),
                  action: "add-to-cart",
                  id_customization: id_customization,
                  // price: $('.right-frm-section-price.total').text()
                };

                refreshURL = "https:" + refreshURL;
                $.post(refreshURL, requestData)
                  .then(function (resp) {
                    $(".blockcart").replaceWith(
                      $(resp.preview).find(".blockcart")
                    );
                    if (resp.modal) {
                      showModal(resp.modal);
                      $(this).addClass("disable");

                      setTimeout(function () {
                        getCartUrl = getCartUrl + "?action=show";
                        //window.location = getCartUrl;
                      }, 1000);
                    }
                  })
                  .fail(function (resp) {
                    prestashop.emit("handleError", {
                      eventType: "updateShoppingCart",
                      resp: resp,
                    });
                  });
              } else {
                $(".text-danger.alert_message_text").html(
                  '<div class="alert alert-danger ajax-error" role="alert">' +
                    r.error +
                    "</div>"
                );
                // alert(r.error);
              }

              // window.location.href = getCartUrl;
            },
            error: function (r) {
              console.warn($(r.responseText).text() || r.responseText);
            },
          });
        }, 500);

        return false;
      });

      $table.on("click", ".calculate_totals", function (event) {
        $(this).addClass("spinning");

        var params = $("#kd_form").serialize();

        console.log($("#kd_form").serializeArray());
        $.ajax({
          type: "POST",
          url: kd_ajax_path,
          dataType: "json",
          data: {
            params: params,
            action: "calculate",
            current_url: window.location.href,
          },
          success: function (r) {
            var $priceLabel = $(".price_wot", $table);
            $priceLabel.html(r.price_wot);
            var $taxLabel = $(".tax", $table);
            $taxLabel.html(r.tax);

            var $totalLabel = $(".total", $table);
            $totalLabel.html(r.total);

            var $package_count = $(".package_count", $table);
            $package_count.html(r.total_package);

            var $thickness = $("#thickness_input", $table);
            $thickness.val(r.total_thickness);
            var quantity = $("#qty_input").val();
            $(".single_unit_price span.single_unit_price_span").text(
              `: ${r.total} / ${quantity}`
            );
            $(".single_unit_price_right").text(r.price_per_qty);

            $(".calculate_totals").removeClass("spinning");
            var error = false;
            if (r.total == "€ 0,00") {
              error = true;
            }

            if (error) {
              $(".main-add-to-cart").addClass("disable_add_to_cart");
              $(".calculate-price-green").addClass("cursor_not_allowed");
            } else {
              $(".main-add-to-cart").removeClass("disable_add_to_cart");
              $(".calculate-price-green").removeClass("cursor_not_allowed");
            }
            //single_unit_price();
          },
          error: function (r) {
            console.warn($(r.responseText).text() || r.responseText);
          },
        });
        add_color_when_not_selected();
        disable_add_to_cart();
        show_button_based_on_value();
        // show_unit_price();
        //single_unit_price();

        return false;
      });

      $table.on("change", ".dropdown", function (event) {
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

        $(".configure-area .input-quantity-wanted").each(function () {
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
        //single_unit_price();
      });

      $table.on(
        "click",
        ".quantity-label .qty-up, .quantity-label .qty-down",
        function (event) {
          var $btn = $(event.target);
          var $td = $btn.closest(".quantity-label");
          var $input = $td.find(".input-quantity-wanted");

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

          if ($total_qty < 0) {
            $total_qty = 0;
          }

          $(".product-quantity .total_qty").text($total_qty);

          $input.prop("value", qty);
          $input.change();
          conditionsForBanedComb();
          disable_add_to_cart();
          show_button_based_on_value();
          show_unit_price();
          //single_unit_price();
        }
      );

      $table.on("blur", ".input-quantity-wanted", function (event) {
        $timer && clearTimeout($timer);
        $t = $(this);
        $timer = setTimeout(function () {
          console.log($t.val());
          var inputValue = parseInt($t.val());
          var minValue = parseInt($t.attr("min"));
          var maxValue = parseInt($t.attr("max"));
          var multiplier = parseInt($t.attr("step"));
          var remains = 0;
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
            $t.val(inputValue);
            $t.parent()
              .next(".custom_input_error")
              .html(
                "<span class='text-danger'> " + errorAmountMultiple.replace('%step%', multiplier) +
                  "</span>"
              );

            $(".main-add-to-cart").addClass("disable_add_to_cart");
            $(".calculate-price-green").addClass("cursor_not_allowed");
            return false;
          } else {
            $t.parent().next(".custom_input_error").html("");
            $(".main-add-to-cart").removeClass("disable_add_to_cart");
            $(".calculate-price-green").removeClass("cursor_not_allowed");
          }

          if (isNaN(inputValue)) {
            $t.val(minValue);
          } else if (inputValue < minValue) {
            $t.val(minValue);
            $t.parent()
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
            $t.val(maxValue);
            $t.parent()
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
            $t.parent().next(".custom_input_error").html("");
            $(".main-add-to-cart").removeClass("disable_add_to_cart");
            $(".calculate-price-green").removeClass("cursor_not_allowed");
          }
          conditionsForBanedComb();
          disable_add_to_cart();
          show_button_based_on_value();
          // show_unit_price();
        }, 0);
      });

      show_unit_price();
      //single_unit_price();
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
  $(".btn-select-value").each(function () {
    var $parent = $(this).parent();

    // Check if the parent does not have the "disabled" class
    setTimeout(() => {
      if ($(this).val() == 0) {
        $(this).addClass("text-danger");
        $(".calculate-error").html(
          "<div class='left-frm-section pull-left required-error' style='font-weight: normal;color: red;width: 50%;'><span>Bitte wählen Sie alle erforderlichen Felder aus</span></div>"
        );
        errorfound = true;
      } else {
        $(this).removeClass("text-danger");
        $(".required-error").hide();
      }
      // if (!$parent.hasClass("disabled")) {
      //     if (
      //         $(this).text() == "Bitte auswählen" ||
      //         $(this).text() == "Kies een optie" ||
      //         $(this).text() == "Choose an option"
      //     ) {
      //         $(this).addClass("text-danger");
      //         $(".calculate-error").html(
      //             "<div class='left-frm-section pull-left required-error' style='font-weight: normal;color: red;width: 50%;'><span>Bitte wählen Sie alle erforderlichen Felder aus</span></div>"
      //         );
      //         errorfound = true;
      //     } else {
      //         $(this).removeClass("text-danger");
      //         $(".required-error").hide();
      //     }
      // }
      disable_add_to_cart();
      show_button_based_on_value();
      // show_unit_price();
    }, 100);
  });
  // Check for the presence of ".custom_input_error" elements
  $(".configure-area .custom_input_error").each(function () {
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

$(document).ready(function () {
  if ($("body").attr("id") === "product") {
    var currentParams = new URLSearchParams(window.location.search);

    var variable_form = $("#kd_form");
    var inputs = variable_form.find(".input_for_url");
    $("#loader").show();

    setTimeout(function () {
      currentParams.forEach(function (value, key) {
        $(inputs).each(function (index) {
          if (
            $(this).data("formula-name").replace(/\s/g, "_").toLowerCase() ==
            key.toLowerCase()
          ) {
            if ($(this).data("type") == 1) {
              $(this).val(value);
            } else if ($(this).data("type") == 2) {
              $(this).val(value);

              console.log(key);
              $(this)
                .parents()
                .find("select.btn-select-value")
                .filter(function () {
                  return (
                    ($(this).data("formula-name") + "")
                      .replace(/\s|_/g, "")
                      .toLowerCase() ===
                    (key + "").replace(/\s|_/g, "").toLowerCase()
                  );
                })
                .val(value)
                .trigger("change");
            } else if ($(this).data("type") == 3) {
            } else if ($(this).data("type") == 4) {
              $(this).val(value);
            } else if ($(this).data("type") == 5) {
              $(this).val(value);
            }
          }
        });
      });

      $(inputs).each(function (index) {
        if ($(this).data("type") == 1) {
          $(this).on("input", function () {
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
          $(document).on("click", "#plus-btn,#minus-btn", function () {
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
            .parent()
            .find("select.btn-select-value")
            .on("change", function (e) {
              var param_value = $(this).val();
              var param_name = $(this)
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
            });
        } else if ($(this).data("type") == 3) {
        } else if ($(this).data("type") == 4) {
          $(this).on("input", function () {
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
          $(document).on("click", "#plus-btn,#minus-btn", function () {
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
          $(this).on("input", function () {
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
      disable_add_to_cart();
      show_button_based_on_value();
      // show_unit_price();
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
        success: function (r) {
          var $priceLabel = $(".price_wot", $table);
          $priceLabel.html(r.price_wot);
          var $taxLabel = $(".tax", $table);
          $taxLabel.html(r.tax);

          var $totalLabel = $(".total", $table);
          $totalLabel.html(r.total);

          var $package_count = $(".package_count", $table);
          $package_count.html(r.total_package);

          var $thickness = $("#thickness_input", $table);
          $thickness.val(r.total_thickness);

          var quantity = $("#qty_input").val();
          $(".single_unit_price span.single_unit_price_span").text(
            `: ${r.total} / ${quantity}`
          );
          $(".single_unit_price_right").text(r.price_per_qty);

          //single_unit_price();
          $(".calculate_totals").removeClass("spinning");
          $("#loader").hide();
          validateAllQuantityInputs();
          validateThicknessInput();
        },
        error: function (r) {
          console.warn($(r.responseText).text() || r.responseText);
        },
      });
    }, 1000);
  }
});

$(document).on("change", 'input.btn-select-input[type="hidden"]', function () {
  $table = $(".configure-area");
  var params = $("#kd_form").serialize();

  // $.ajax({
  //     type: "POST",
  //     url: kd_ajax_path,
  //     dataType: "json",
  //     data: {
  //         params: params,
  //         action: "calculate",
  //         current_url: window.location.href,
  //     },
  //     success: function(r) {
  //         var variable_form = $("#kd_form");
  //         var inputs = variable_form.find('input.btn-select-input[type="hidden"]');
  //         $(inputs).each(function(index) {
  //             if ($(this).data("type") == 2) {
  //                 var hidden_value = $(this).val();
  //                 console.log(hidden_value);
  //                 console.log($(this).parent().find('select.btn-select-value'));
  //                 $(this).parent().find('select.btn-select-value').val(hidden_value).trigger('change');
  //                 // $(this)
  //                 //     .parents(".btn-select.dropdown")
  //                 //     .find("ul.select_box_for_url li")
  //                 //     .removeClass("selected");
  //                 // var new_hidden_value_text = $(this)
  //                 //     .parents(".btn-select.dropdown")
  //                 //     .find(
  //                 //         'ul.select_box_for_url li[data-id_option="' + hidden_value + '"]'
  //                 //     )
  //                 //     .text();
  //                 // $(this)
  //                 //     .parents(".btn-select.dropdown")
  //                 //     .find("span.btn-select-value")
  //                 //     .text(new_hidden_value_text);
  //                 // $(this)
  //                 //     .parents(".btn-select.dropdown")
  //                 //     .find(
  //                 //         'ul.select_box_for_url li[data-id_option="' + hidden_value + '"]'
  //                 //     )
  //                 //     .addClass("selected");
  //             }
  //         });

  //         var $priceLabel = $(".price_wot", $table);
  //         $priceLabel.html(r.price_wot);
  //         var $taxLabel = $(".tax", $table);
  //         $taxLabel.html(r.tax);

  //         var $totalLabel = $(".total", $table);
  //         $totalLabel.html(r.total);

  //         var $thickness = $("#thickness_input", $table);
  //         $thickness.val(r.total_thickness);

  //         $(".calculate_totals").removeClass("spinning");
  //         $("#loader").hide();
  //     },
  //     error: function(r) {
  //         console.warn($(r.responseText).text() || r.responseText);
  //     },
  // });
});

function rulesForbannedComb3() {
  $("select[data-variable-name] option, input[data-variable-name]").each(
    function () {
      $(this).removeClass("disabled");
      $(this).removeAttr("disabled");
    }
  );
  if ($("input[data-variable_id='3']").val() == 1) {
    $("input:hidden.btn-select-input[data-variable_id='6']").val("");
    $("select[data-variable_id='6'] option").each(function () {
      if ($(this).text() === "Dubbelzijdig") {
        $("input:hidden.btn-select-input[data-variable_id='6']").val("");
        if ($(this).hasClass("selected") || $(this).is(":selected")) {
          $(".btn-select-value[data-variable_id='6']").val(0);
          $(this).parent().prop("selectedIndex", 0).val();
        }
        $("input:hidden.btn-select-input[data-variable_id='6']").val(
          $(this).parent().find(".selected").attr("data-id_option")
        );
        $(this).addClass("disabled");
        $(this).attr("disabled", "disabled");
      }
    });
    var ul = $("select[data-variable_id='6']");
    var span = $(".btn-select-value[data-variable_id='6']");
    ul.on("click", "option", function () {});
  }
}

// function alert_messages_combination(target) {}

function disable_add_to_cart() {
  var select_elements = $("select.select_box_for_url");
  var error = false;
  select_elements.each(function (index, element) {
    if ($(element).hasClass("text-danger")) {
      error = true;
    }
  });

  if (error) {
    $(".main-add-to-cart").addClass("disable_add_to_cart");
    $(".calculate-price-green").addClass("cursor_not_allowed");
  } else {
    $(".main-add-to-cart").removeClass("disable_add_to_cart");
    $(".calculate-price-green").removeClass("cursor_not_allowed");
  }
}

function show_unit_price() {
  var select_elements = $("select.select_box_for_url");
  var html = ""; // Initialize an empty string to store the HTML

  select_elements.each(function (index, element) {
    var selectedOption = $(element).find("option:selected");
    var label = $(element).attr("data-label-text");
    var dataPrice = parseFloat(selectedOption.data("price")) || 0; // Get the data-price or use 0 if undefined

    dataPrice = dataPrice.toFixed(2);
    // Append the HTML for each unit price block
    html += `
    <div class="unit_price">
        <div class="left-frm-section unit_price_label">${label}: <span class="unit_price_value"> ${selectedOption.text()}</span></div>
        <div class="right-frm-section-price unit_price_right"> € ${dataPrice}</div>
    </div>`;
  });

  $(".unit_prices").html(html);

  // ajax call for get unit price from database
  // $.ajax({
  //   type: "POST",
  //   url: kd_ajax_path,
  //   dataType: "json",
  //   data: {
  //     params: $("#kd_form").serialize(),
  //     action: "get_unit_price",
  //   },
  //   success: function (r) {
  //     if (r.error) {
  //       console.error(r.error);
  //       return;
  //     }
  //   },
  //   error: function (r) {
  //     console.log(r);
  //   },
  // });
}

function single_unit_price() {
  var total_price = $(".right-frm-section-price.total").text().trim();

  total_price = total_price
    .replace(/[^\d,.-]/g, "")
    .replace(/\./g, "")
    .replace(/,/g, ".");

  // Convert the string to a float number
  var total_price_number = parseFloat(total_price);
  var quantity = $("#qty_input").val();
  // Calculate the single price
  var single_price = total_price_number / quantity;

  // Format the single price to have the desired format
  single_price = single_price.toLocaleString("de-DE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  var total_price_string = total_price_number.toLocaleString("de-DE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  $(".single_unit_price span.single_unit_price_span").text(
    `: ${total_price_string}  € / ${quantity}`
  );
  $(".single_unit_price_right").text(`${single_price}   €`);
}

function show_button_based_on_value() {
  // var upload_method = $("[data-url_variable='upload_method']").val();
  // if (upload_method == 394) {
  //   $("button.customize_design").show();
  //   $("a.main-add-to-cart").hide();
  // } else if (upload_method == 393) {
  //   $("button.customize_design").hide();
  //   $("a.main-add-to-cart").show();
  // }
}
