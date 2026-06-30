/**
 * reconfigure.js - jQuery interactions for Reconfigure & Reorder page.
 *
 * Each product block manages its own state independently.
 * No modals, no popups, no page refreshes - all inline.
 */
(function ($) {
    'use strict';

    var ReconfigureApp = {

        ajax_url: null,
        cart_url: null,
        recalculate_timers: {},

        init: function () {
            this.ajax_url = window.reconfigure_ajax_url;
            this.cart_url = window.reconfigure_cart_url;

            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            // Quantity edit button: show editor
            $(document).on('click', '.qty-edit-btn', function () {
                var id = $(this).data('target') || $(this).closest('.config-qty').find('.qty-input').attr('id').replace('qty-input-', '');
                self.showQuantityEditor(id);
            });

            // Quantity increment
            $(document).on('click', '.qty-inc', function () {
                var id = $(this).data('target');
                self.changeQuantity(id, 1);
            });

            // Quantity decrement
            $(document).on('click', '.qty-dec', function () {
                var id = $(this).data('target');
                self.changeQuantity(id, -1);
            });

            // Quantity manual input change
            $(document).on('change', '.qty-input', function () {
                var id = this.id.replace('qty-input-', '');
                self.onQuantityChange(id, $(this).val());
            });

            // Add all valid products to cart
            $(document).on('click', '#btn-add-to-cart', function () {
                self.addAllToCart();
            });
        },

        // ──────────────────────────────────────
        //  QUANTITY EDITING
        // ──────────────────────────────────────

        showQuantityEditor: function (id) {
            $('#qty-display-' + id).hide();
            $('#qty-editor-' + id).show();
            $('#qty-range-' + id).show();
            $('#qty-edit-btn-' + id).hide();
        },

        changeQuantity: function (id, direction) {
            var $input = $('#qty-input-' + id);
            var step = parseFloat($input.attr('step')) || 1;
            var min = parseFloat($input.attr('min')) || 1;
            var max = parseFloat($input.attr('max')) || 999999;
            var current = parseInt($input.val()) || min;
            var id_pv = $input.data('id-pv');

            var newQty = current + (direction * step);
            newQty = Math.max(min, Math.min(max, newQty));

            $input.val(newQty);
            this.onQuantityChange(id, newQty, id_pv);
        },

        onQuantityChange: function (id, newQty, id_pv) {
            var self = this;
            var $block = $('#qty-input-' + id).closest('.product-block');
            var id_product = $block.data('id-product');
            var id_order_detail = $block.data('id-order-detail');

            // Update the displayed value
            $('#qty-value-' + id).text(newQty);

            // Validate quantity via AJAX
            $.ajax({
                url: this.ajax_url,
                method: 'POST',
                data: {
                    action: 'validate-qty',
                    id_product: id_product,
                    id_order_detail: id_order_detail,
                    quantity: newQty
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        var $valArea = $('#validation-' + id_order_detail);

                        if (!response.valid) {
                            self.setInlineMessage($valArea, 'error', response.message);
                        }

                        // Update the hidden params with new quantity
                        var params = self.getBlockParams(id_order_detail);
                        params['variable_' + id_pv] = newQty;
                        self.setBlockParams(id_order_detail, params);

                        // Debounced price recalculation
                        clearTimeout(self.recalculate_timers[id_order_detail]);
                        self.recalculate_timers[id_order_detail] = setTimeout(function () {
                            self.recalculate(id_order_detail, params);
                        }, 400);
                    }
                }
            });
        },

        // ──────────────────────────────────────
        //  PRICE RECALCULATION (inline)
        // ──────────────────────────────────────

        recalculate: function (id_order_detail, params) {
            var self = this;
            var $block = $('[data-id-order-detail="' + id_order_detail + '"]');
            var $priceEl = $('#price-current-' + id_order_detail);
            var $valArea = $('#validation-' + id_order_detail);

            $.ajax({
                url: this.ajax_url,
                method: 'POST',
                data: $.extend({ action: 'recalculate' }, params),
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.price_data) {
                        // Update price inline
                        $priceEl.text(response.price_data.total);

                        if (response.price_data.qty) {
                            // Sync quantity display if it changed
                            var $qtyVar = $block.find('.qty-input');
                            if ($qtyVar.length) {
                                var qtyId = $qtyVar.attr('id').replace('qty-input-', '');
                                $('#qty-value-' + qtyId).text(response.price_data.qty);
                            }
                        }

                        if (!response.rules_valid) {
                            self.setInlineMessage($valArea, 'error', 'Banned combination detected');
                            $block.removeClass('block-valid').addClass('block-invalid');
                        } else if (response.price_error) {
                            self.setInlineMessage($valArea, 'error', 'Price calculation error');
                            $block.removeClass('block-valid').addClass('block-invalid');
                        } else {
                            self.setInlineMessage($valArea, 'success', 'Price updated successfully');
                            $block.removeClass('block-invalid').addClass('block-valid');
                        }
                    } else if (response.price_error) {
                        self.setInlineMessage($valArea, 'error', 'Price calculation error');
                        $block.removeClass('block-valid').addClass('block-invalid');
                    }
                }
            });
        },

        // ──────────────────────────────────────
        //  ADD ALL TO CART
        // ──────────────────────────────────────

        addAllToCart: function () {
            var self = this;
            var $btn = $('#btn-add-to-cart');
            var products = [];

            // Collect all valid product blocks
            $('.product-block.block-valid').each(function () {
                var $block = $(this);
                var id_order_detail = $block.data('id-order-detail');
                var params = self.getBlockParams(id_order_detail);

                var product = {
                    id_product: $block.data('id-product'),
                    id_product_setting: $block.data('id-product-setting'),
                    id_product_attribute: $block.data('id-product-attribute') || 0
                };

                // Add all variable_X values from params
                $.each(params, function (key, value) {
                    if (key.indexOf('variable_') === 0) {
                        product[key] = value;
                    }
                });

                products.push(product);
            });

            if (products.length === 0) {
                return;
            }

            // Show spinner in button
            $btn.find('.btn-text').text('Adding to cart...');
            $btn.find('.btn-spinner').show();
            $btn.prop('disabled', true);

            $.ajax({
                url: this.ajax_url,
                method: 'POST',
                data: {
                    action: 'add-to-cart',
                    products: JSON.stringify(products)
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // Mark skipped products
                        if (response.skipped && response.skipped.length > 0) {
                            $.each(response.skipped, function (i, skipped) {
                                var $block = $('[data-id-product="' + skipped.id_product + '"]');
                                $block.removeClass('block-valid').addClass('block-invalid');
                                self.setInlineMessage(
                                    $block.find('.block-validation'),
                                    'error',
                                    skipped.reason
                                );
                            });
                        }

                        // If at least one product was added, redirect to cart
                        if (response.added_count > 0) {
                            window.location.href = self.cart_url;
                        } else {
                            // None added - re-enable button
                            $btn.find('.btn-text').text('Add Valid Products To Cart');
                            $btn.find('.btn-spinner').hide();
                            $btn.prop('disabled', false);
                        }
                    } else {
                        $btn.find('.btn-text').text('Add Valid Products To Cart');
                        $btn.find('.btn-spinner').hide();
                        $btn.prop('disabled', false);
                    }
                },
                error: function () {
                    $btn.find('.btn-text').text('Add Valid Products To Cart');
                    $btn.find('.btn-spinner').hide();
                    $btn.prop('disabled', false);
                }
            });
        },

        // ──────────────────────────────────────
        //  HELPERS
        // ──────────────────────────────────────

        getBlockParams: function (id_order_detail) {
            var $el = $('#block-params-' + id_order_detail);
            var raw = $el.val();
            try {
                return JSON.parse(raw);
            } catch (e) {
                return {};
            }
        },

        setBlockParams: function (id_order_detail, params) {
            $('#block-params-' + id_order_detail).val(JSON.stringify(params));
        },

        setInlineMessage: function ($valArea, type, message) {
            // Remove any existing dynamic message
            $valArea.find('.val-dynamic').remove();

            var icon = type === 'success' ? '&#10003;' : (type === 'warning' ? '&#9888;' : '&#10007;');
            var cssClass = 'val-detail val-dynamic severity-' + type;

            $valArea.append(
                '<div class="' + cssClass + '">' + icon + ' ' + message + '</div>'
            );

            // Auto-dismiss success messages after 3 seconds
            if (type === 'success') {
                setTimeout(function () {
                    $valArea.find('.val-dynamic.severity-success').fadeOut(300, function () {
                        $(this).remove();
                    });
                }, 3000);
            }
        }
    };

    $(function () {
        ReconfigureApp.init();
    });

})(jQuery);
