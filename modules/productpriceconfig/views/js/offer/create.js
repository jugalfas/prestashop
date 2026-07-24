/**
 * Offer Create / List / Detail - Front-end JavaScript
 *
 * Handles product search, configurator loading, quote cart management,
 * file uploads, and all AJAX interactions for the Offer feature.
 */

(function ($) {
    'use strict';

    var OfferApp = {
        ajaxUrl: '',
        myOffersUrl: '',
        isLogged: false,
        currentIdOffer: 0,
        currentIdProduct: 0,
        searchDebounceTimer: null,
        searchSelectedIndex: -1,
        searchResults: [],

        init: function () {
            this.ajaxUrl = $('#ajax-url').val();
            this.myOffersUrl = $('#my-offers-url').val();
            this.isLogged = $('#is-logged').val() === '1';
            this.currentIdOffer = parseInt($('#current-id-offer').val()) || 0;

            this.bindSearchEvents();
            this.bindProductSelectionEvents();
            this.bindConfiguratorEvents();
            this.bindQuoteCartEvents();
            this.bindOfferSubmissionEvents();
            this.bindDetailPageEvents();
            this.bindListPageEvents();
        },

        // ── Product Search ──

        bindSearchEvents: function () {
            var self = this;

            $('#product-search-input').on('input', function () {
                var query = $(this).val().trim();
                clearTimeout(self.searchDebounceTimer);

                if (query.length === 0) {
                    self.showDefaultProducts();
                    return;
                }

                self.searchDebounceTimer = setTimeout(function () {
                    self.searchProducts(query);
                }, 300);
            });

            $('#product-search-input').on('focus', function () {
                if ($(this).val().trim().length === 0) {
                    self.showDefaultProducts();
                }
            });

            $('#product-search-input').on('keydown', function (e) {
                self.handleSearchKeyboard(e);
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('.search-box-wrapper').length) {
                    $('#search-results-dropdown').hide();
                }
            });

            $('#search-clear-btn').on('click', function () {
                $('#product-search-input').val('').focus();
                $('#search-results-dropdown').hide();
                $('#search-clear-btn').hide();
            });
        },

        showDefaultProducts: function () {
            var self = this;
            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: { action: 'search-products', q: '', limit: 20 },
                success: function (resp) {
                    if (resp.success) {
                        self.renderSearchResults(resp.products);
                    }
                }
            });
        },

        searchProducts: function (query) {
            var self = this;
            $('#search-clear-btn').show();

            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: { action: 'search-products', q: query, limit: 20 },
                success: function (resp) {
                    if (resp.success) {
                        self.renderSearchResults(resp.products);
                    }
                },
                error: function () {
                    self.notify('error', 'Search failed. Please try again.');
                }
            });
        },

        renderSearchResults: function (products) {
            var self = this;
            self.searchResults = products;
            self.searchSelectedIndex = -1;

            if (!products.length) {
                $('#search-results-dropdown').html(
                    '<div class="search-no-results">No products found.</div>'
                ).show();
                return;
            }

            var html = '';
            $.each(products, function (i, p) {
                html += '<div class="search-result-item" data-index="' + i + '" data-id-product="' + p.id_product + '">';
                html += '<div class="search-result-image">';
                if (p.image) {
                    html += '<img src="' + p.image + '" alt="" />';
                } else {
                    html += '<i class="material-icons">image</i>';
                }
                html += '</div>';
                html += '<div class="search-result-info">';
                html += '<div class="search-result-name">' + p.name + '</div>';
                html += '<div class="search-result-ref">' + (p.reference || '') + '</div>';
                html += '<div class="search-result-desc">' + (p.description_short || '') + '</div>';
                html += '<div class="search-result-meta">';
                if (p.category) html += '<span class="badge badge-info">' + p.category + '</span> ';
                if (p.active) html += '<span class="badge badge-success">Active</span>';
                html += '</div>';
                html += '</div>';
                html += '<div class="search-result-arrow"><i class="material-icons">chevron_right</i></div>';
                html += '</div>';
            });

            $('#search-results-dropdown').html(html).show();

            $('.search-result-item').on('click', function () {
                var index = parseInt($(this).data('index'));
                self.selectProduct(self.searchResults[index]);
            });
        },

        handleSearchKeyboard: function (e) {
            var self = this;
            var $dropdown = $('#search-results-dropdown');
            var $items = $dropdown.find('.search-result-item');

            if (!$items.length) return;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    self.searchSelectedIndex = Math.min(self.searchSelectedIndex + 1, $items.length - 1);
                    self.highlightSearchItem($items, self.searchSelectedIndex);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    self.searchSelectedIndex = Math.max(self.searchSelectedIndex - 1, 0);
                    self.highlightSearchItem($items, self.searchSelectedIndex);
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (self.searchSelectedIndex >= 0 && self.searchResults[self.searchSelectedIndex]) {
                        self.selectProduct(self.searchResults[self.searchSelectedIndex]);
                    }
                    break;
                case 'Escape':
                    $dropdown.hide();
                    break;
            }
        },

        highlightSearchItem: function ($items, index) {
            $items.removeClass('active');
            $items.eq(index).addClass('active');
        },

        // ── Product Selection ──

        selectProduct: function (product) {
            var self = this;
            self.currentIdProduct = product.id_product;

            $('#search-results-dropdown').hide();
            $('#product-search-input').hide();
            $('#search-clear-btn').hide();

            $('#selected-product-name').text(product.name);
            $('#selected-product-reference').text(product.reference || '');
            $('#selected-product-description').text(product.description_short || '');
            $('#selected-product-category').text(product.category || '');
            if (product.image) {
                $('#selected-product-image').attr('src', product.image).show();
            } else {
                $('#selected-product-image').hide();
            }
            $('#selected-product-card').show();

            self.loadConfigurator(product.id_product);
        },

        bindProductSelectionEvents: function () {
            var self = this;

            $('#btn-remove-product').on('click', function () {
                self.clearSelectedProduct();
            });

            $('#btn-change-product').on('click', function () {
                self.clearSelectedProduct();
            });
        },

        clearSelectedProduct: function () {
            this.currentIdProduct = 0;
            $('#selected-product-card').hide();
            $('#configurator-content').html('').hide();
            $('#configurator-placeholder').show();
            $('#product-search-input').val('').show().focus();
        },

        // ── Configurator ──

        loadConfigurator: function (id_product) {
            var self = this;
            $('#configurator-placeholder').hide();
            $('#configurator-content').html(
                '<div class="text-center" style="padding: 40px;"><i class="fa fa-spinner fa-spin" style="font-size: 32px;"></i><p>Loading configurator...</p></div>'
            ).show();

            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: { action: 'get-configurator', id_product: id_product },
                success: function (resp) {
                    if (resp.success) {
                        $('#configurator-content').html(resp.html);
                        self.bindAddToQuoteButton();
                    } else {
                        $('#configurator-content').html(
                            '<div class="alert alert-warning">' + (resp.error || 'No configuration available.') + '</div>'
                        );
                    }
                },
                error: function () {
                    $('#configurator-content').html(
                        '<div class="alert alert-danger">Failed to load configurator.</div>'
                    );
                }
            });
        },

        bindConfiguratorEvents: function () {
            // Configurator events are bound after loading content
        },

        bindAddToQuoteButton: function () {
            var self = this;

            // Hook into the existing add-to-cart button in the configurator
            // The existing front.js handles price calculation and banned combinations.
            // We intercept the add-to-cart to redirect to our quote cart instead.
            $('#add-to-cart, .add-to-cart, button[name="Submit"]').off('click').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                self.collectConfigAndAddToQuote();
            });
        },

        collectConfigAndAddToQuote: function () {
            var self = this;

            if (!self.currentIdProduct) {
                self.notify('error', 'Please select a product first.');
                return;
            }

            if (!self.currentIdOffer) {
                self.createDraftOffer(function () {
                    self.addProductToQuote();
                });
            } else {
                self.addProductToQuote();
            }
        },

        createDraftOffer: function (callback) {
            var self = this;

            var guestEmail = '';
            var guestName = '';
            if (!self.isLogged) {
                guestEmail = $('#guest_email').val();
                guestName = $('#guest_name').val();

                if (!guestEmail || !self.validateEmail(guestEmail)) {
                    self.notify('error', 'Please enter a valid email address.');
                    return;
                }
                if (!guestName) {
                    self.notify('error', 'Please enter your name.');
                    return;
                }
            }

            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'create-draft',
                    title: $('#quote-title').val() || '',
                    guest_email: guestEmail,
                    guest_name: guestName,
                    customer_note: $('#quote-customer-note').val() || ''
                },
                success: function (resp) {
                    if (resp.success) {
                        self.currentIdOffer = resp.id_offer;
                        $('#current-id-offer').val(resp.id_offer);
                        callback();
                    } else {
                        self.notify('error', resp.error || 'Failed to create quotation.');
                    }
                },
                error: function () {
                    self.notify('error', 'Failed to create quotation.');
                }
            });
        },

        addProductToQuote: function () {
            var self = this;

            var config = {};
            $('select[data-variable_id], input[data-variable_id]').each(function () {
                var $el = $(this);
                var varId = $el.data('variable_id');
                var val;
                if ($el.is('select')) {
                    val = $el.find('option:selected').data('option_id') || $el.val();
                } else {
                    val = $el.val();
                }
                config['variable_' + varId] = val;
            });

            var quantity = $('input[data-variable_id][type="number"]').val() || 1;
            var customerNote = '';

            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'add-product',
                    id_offer: self.currentIdOffer,
                    id_product: self.currentIdProduct,
                    configuration_json: JSON.stringify(config),
                    quantity: quantity,
                    customer_note: customerNote
                },
                success: function (resp) {
                    if (resp.success) {
                        self.notify('success', 'Product added to quotation.');
                        self.refreshQuoteCart();
                        $('#btn-submit-offer').prop('disabled', false);
                    } else {
                        self.notify('error', resp.error || 'Failed to add product.');
                    }
                },
                error: function () {
                    self.notify('error', 'Failed to add product to quotation.');
                }
            });
        },

        // ── Quote Cart ──

        bindQuoteCartEvents: function () {
            var self = this;
            $(document).on('click', '.btn-remove-quote-product', function () {
                var idOfferProduct = $(this).data('id-offer-product');
                self.removeQuoteProduct(idOfferProduct);
            });
        },

        refreshQuoteCart: function () {
            var self = this;
            if (!self.currentIdOffer) return;

            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: { action: 'get-quote-cart', id_offer: self.currentIdOffer },
                success: function (resp) {
                    if (resp.success) {
                        self.renderQuoteCart(resp.products);
                    }
                }
            });
        },

        renderQuoteCart: function (products) {
            $('#quote-cart-count').text(products.length);

            if (!products.length) {
                $('#quote-cart-empty').show();
                $('#quote-cart-items').html('');
                $('#btn-submit-offer').prop('disabled', true);
                return;
            }

            $('#quote-cart-empty').hide();
            var html = '';
            $.each(products, function (i, p) {
                html += '<div class="quote-cart-item" data-id-offer-product="' + p.id_offer_product + '">';
                html += '<div class="quote-cart-item-image">';
                if (p.image) {
                    html += '<img src="' + p.image + '" alt="" />';
                } else {
                    html += '<i class="material-icons">image</i>';
                }
                html += '</div>';
                html += '<div class="quote-cart-item-info">';
                html += '<div class="quote-cart-item-name">' + p.product_name + '</div>';
                html += '<div class="quote-cart-item-config small text-muted">' + (p.configuration_summary || '') + '</div>';
                html += '<div class="quote-cart-item-qty small">Qty: ' + p.quantity + '</div>';
                if (p.offered_price) {
                    html += '<div class="quote-cart-item-price text-success small">' + p.offered_price + '</div>';
                }
                html += '</div>';
                html += '<div class="quote-cart-item-actions">';
                html += '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-quote-product" data-id-offer-product="' + p.id_offer_product + '">';
                html += '<i class="material-icons">delete</i>';
                html += '</button>';
                html += '</div>';
                html += '</div>';
            });

            $('#quote-cart-items').html(html);
        },

        removeQuoteProduct: function (idOfferProduct) {
            var self = this;
            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'remove-product',
                    id_offer: self.currentIdOffer,
                    id_offer_product: idOfferProduct
                },
                success: function (resp) {
                    if (resp.success) {
                        self.notify('success', 'Product removed.');
                        self.refreshQuoteCart();
                    } else {
                        self.notify('error', resp.error || 'Failed to remove product.');
                    }
                }
            });
        },

        // ── Offer Submission ──

        bindOfferSubmissionEvents: function () {
            var self = this;
            $('#btn-submit-offer').on('click', function () {
                self.submitOffer();
            });
        },

        submitOffer: function () {
            var self = this;
            if (!self.currentIdOffer) {
                self.notify('error', 'No quotation to submit.');
                return;
            }

            var title = $('#quote-title').val() || '';
            var customerNote = $('#quote-customer-note').val() || '';

            if (!title) {
                self.notify('error', 'Please enter a quotation title.');
                return;
            }

            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'submit-offer',
                    id_offer: self.currentIdOffer
                },
                success: function (resp) {
                    if (resp.success) {
                        self.notify('success', 'Quotation submitted! We will review and respond soon.');
                        setTimeout(function () {
                            window.location.href = self.myOffersUrl;
                        }, 2000);
                    } else {
                        self.notify('error', resp.error || 'Failed to submit quotation.');
                    }
                },
                error: function () {
                    self.notify('error', 'Failed to submit quotation.');
                }
            });
        },

        // ── Detail Page Events ──

        bindDetailPageEvents: function () {
            var self = this;

            $('.btn-accept-product').on('click', function () {
                var idOffer = $(this).data('id-offer');
                var idOfferProduct = $(this).data('id-offer-product');
                self.acceptProduct(idOffer, idOfferProduct);
            });

            $('.btn-reject-product').on('click', function () {
                var idOffer = $(this).data('id-offer');
                var idOfferProduct = $(this).data('id-offer-product');
                var reason = prompt('Reason for rejection (optional):') || '';
                self.rejectProduct(idOffer, idOfferProduct, reason);
            });

            $('#btn-add-to-cart').on('click', function () {
                var idOffer = $(this).data('id-offer');
                self.addToCart(idOffer);
            });
        },

        acceptProduct: function (idOffer, idOfferProduct) {
            var self = this;
            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'accept-product',
                    id_offer: idOffer,
                    id_offer_product: idOfferProduct
                },
                success: function (resp) {
                    if (resp.success) {
                        self.notify('success', 'Product accepted!');
                        setTimeout(function () { location.reload(); }, 1500);
                    } else {
                        self.notify('error', resp.error || 'Failed to accept.');
                    }
                }
            });
        },

        rejectProduct: function (idOffer, idOfferProduct, reason) {
            var self = this;
            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'reject-product',
                    id_offer: idOffer,
                    id_offer_product: idOfferProduct,
                    reason: reason
                },
                success: function (resp) {
                    if (resp.success) {
                        self.notify('success', 'Product rejected.');
                        setTimeout(function () { location.reload(); }, 1500);
                    } else {
                        self.notify('error', resp.error || 'Failed to reject.');
                    }
                }
            });
        },

        addToCart: function (idOffer) {
            var self = this;
            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'add-to-cart',
                    id_offer: idOffer
                },
                success: function (resp) {
                    if (resp.success) {
                        self.notify('success', 'Products added to cart!');
                        setTimeout(function () {
                            window.location.href = $('.cart-link').attr('href') || '/cart';
                        }, 1500);
                    } else {
                        if (resp.errors && resp.errors.length) {
                            self.notify('error', resp.errors.join(' | '));
                        } else {
                            self.notify('error', 'Failed to add to cart.');
                        }
                    }
                }
            });
        },

        // ── List Page Events ──

        bindListPageEvents: function () {
            var self = this;
            $('.btn-duplicate-offer').on('click', function () {
                var idOffer = $(this).data('id-offer');
                self.duplicateOffer(idOffer);
            });
        },

        duplicateOffer: function (idOffer) {
            var self = this;
            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'duplicate-offer',
                    id_offer: idOffer
                },
                success: function (resp) {
                    if (resp.success) {
                        self.notify('success', 'Quotation duplicated!');
                        setTimeout(function () { location.reload(); }, 1500);
                    } else {
                        self.notify('error', resp.error || 'Failed to duplicate.');
                    }
                }
            });
        },

        // ── Utilities ──

        validateEmail: function (email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        notify: function (type, message) {
            var $area = $('#offer-notification-area');
            if (!$area.length) {
                alert(message);
                return;
            }

            var cls = type === 'success' ? 'alert-success' : 'alert-danger';
            var icon = type === 'success' ? 'check_circle' : 'error';

            $area.removeClass('alert-success alert-danger').addClass('alert ' + cls);
            $area.html('<i class="material-icons">' + icon + '</i> ' + message);
            $area.slideDown(200);

            clearTimeout($area.data('timeout'));
            $area.data('timeout', setTimeout(function () {
                $area.slideUp(200);
            }, 4000));
        }
    };

    $(function () {
        OfferApp.init();
    });

})(jQuery);
