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
        uploadedFiles: [],
        pendingFileIds: [],

        init: function () {
            this.ajaxUrl = $('#ajax-url').val();
            this.myOffersUrl = $('#my-offers-url').val();
            this.isLogged = $('#is-logged').val() === '1';
            this.currentIdOffer = parseInt($('#current-id-offer').val()) || 0;

            this.bindSearchEvents();
            this.bindProductSelectionEvents();
            this.bindQuoteCartEvents();
            this.bindOfferSubmissionEvents();
            this.bindDetailPageEvents();
            this.bindListPageEvents();
            this.bindUploadEvents();
            this.bindAddToQuoteEvents();
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

            var html = '<div class="search-results-grid">';
            $.each(products, function (i, p) {
                html += '<div class="search-result-card" data-index="' + i + '" data-id-product="' + p.id_product + '">';
                html += '<div class="src-image">';
                if (p.image) {
                    html += '<img src="' + p.image + '" alt="" />';
                } else {
                    html += '<i class="material-icons">image</i>';
                }
                html += '</div>';
                html += '<div class="src-info">';
                html += '<div class="src-name">' + self.escapeHtml(p.name) + '</div>';
                html += '<div class="src-ref">' + self.escapeHtml(p.reference || '') + '</div>';
                html += '<div class="src-desc">' + self.escapeHtml(p.description_short || '') + '</div>';
                html += '</div>';
                html += '<div class="src-select-btn">';
                html += '<i class="material-icons">chevron_right</i>';
                html += '</div>';
                html += '</div>';
            });
            html += '</div>';

            $('#search-results-dropdown').html(html).show();

            $('.search-result-card').on('click', function () {
                var index = parseInt($(this).data('index'));
                self.selectProduct(self.searchResults[index]);
            });
        },

        handleSearchKeyboard: function (e) {
            var self = this;
            var $dropdown = $('#search-results-dropdown');
            var $items = $dropdown.find('.search-result-card');

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
            var $dropdown = $('#search-results-dropdown');
            var $item = $items.eq(index);
            if ($item.length) {
                var dropdownTop = $dropdown.scrollTop();
                var itemTop = $item.position().top + dropdownTop;
                var itemBottom = itemTop + $item.outerHeight();
                if (itemBottom > $dropdown.scrollTop() + $dropdown.height()) {
                    $dropdown.scrollTop(itemBottom - $dropdown.height());
                } else if (itemTop < $dropdown.scrollTop()) {
                    $dropdown.scrollTop(itemTop);
                }
            }
        },

        // ── Product Selection ──

        selectProduct: function (product) {
            var self = this;
            self.currentIdProduct = product.id_product;
            self.uploadedFiles = [];
            self.pendingFileIds = [];

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
            this.uploadedFiles = [];
            this.pendingFileIds = [];
            $('#selected-product-card').hide();
            $('#configurator-content').html('').hide();
            $('#offer-upload-section').hide();
            $('#offer-product-note-section').hide();
            $('#offer-add-to-quote-section').hide();
            $('#configurator-placeholder').show();
            $('#product-search-input').val('').show().focus();
        },

        // ── Configurator ──

        loadConfigurator: function (id_product) {
            var self = this;

            // Clear config section immediately and show centered loader
            $('#configurator-placeholder').hide();
            $('#configurator-content').html(
                '<div class="configurator-loader">' +
                '<i class="fa fa-spinner fa-spin"></i>' +
                '<p>Loading configurator...</p>' +
                '</div>'
            ).show();

            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: { action: 'get-configurator', id_product: id_product },
                success: function (resp) {
                    if (resp.success) {
                        // Strip price/purchase elements from the configurator HTML
                        var $html = $('<div>').html(resp.html);
                        self.stripPurchaseElements($html);
                        $('#configurator-content').html($html.html());

                        // Show upload, note, and add-to-quote sections
                        $('#offer-upload-section').show();
                        $('#offer-product-note-section').show();
                        $('#offer-add-to-quote-section').show();
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

        /**
         * Remove all price/purchase-related elements from the configurator HTML
         * so the Offer page only shows configuration controls.
         */
        stripPurchaseElements: function ($html) {
            // Remove price section
            $html.find('.ppc-price-section').remove();
            // Remove add-to-cart button
            $html.find('.ppc-cta-main, .main-add-to-cart, .single_add_to_cart_button').remove();
            // Remove price-related rows
            $html.find('.single_unit_prices, .price_wot, .tax, .total, .package_count').closest('.ppc-price-row').remove();
            $html.find('.single_unit_prices').remove();
            // Remove calculate price trigger
            $html.find('.calculate-price').remove();
            // Remove alert message text (price-related)
            $html.find('.alert_message_text').remove();
            // Remove the step 3 "Oplage & prijs" tab button text change to just "Oplage"
            $html.find('#ppcTab3 .ppc-step-num').next().each(function () {
                $(this).text($(this).text().replace('& prijs', ''));
            });
        },

        // ── File Upload ──

        bindUploadEvents: function () {
            var self = this;

            // Click to browse
            $('#offer-drop-zone').on('click', function () {
                $('#offer-file-input').click();
            });

            // File input change
            $('#offer-file-input').on('change', function () {
                self.handleFiles(this.files);
                $(this).val('');
            });

            // Drag and drop
            var $drop = $('#offer-drop-zone');
            $drop.on('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $drop.addClass('offer-drop-zone-active');
            });
            $drop.on('dragleave', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $drop.removeClass('offer-drop-zone-active');
            });
            $drop.on('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $drop.removeClass('offer-drop-zone-active');
                var files = e.originalEvent.dataTransfer.files;
                self.handleFiles(files);
            });

            // Remove file (delegated)
            $(document).on('click', '.offer-file-remove', function () {
                var fileId = $(this).data('file-id');
                self.removeFile(fileId);
            });
        },

        handleFiles: function (files) {
            var self = this;
            var allowedExt = ['pdf', 'ai', 'eps', 'svg', 'png', 'jpg', 'jpeg', 'tiff', 'tif'];
            var maxSize = 20 * 1024 * 1024; // 20MB
            var errorMsg = '';

            $.each(files, function (i, file) {
                var ext = (file.name.split('.').pop() || '').toLowerCase();

                if (allowedExt.indexOf(ext) === -1) {
                    errorMsg += file.name + ': unsupported file type. ';
                    return;
                }

                if (file.size > maxSize) {
                    errorMsg += file.name + ': file exceeds 20MB limit. ';
                    return;
                }

                self.uploadFile(file);
            });

            if (errorMsg) {
                self.showUploadError(errorMsg);
            } else {
                $('#offer-upload-error').hide();
            }
        },

        uploadFile: function (file) {
            var self = this;
            var fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            self.uploadedFiles.push({ id: fileId, name: file.name, size: file.size, status: 'uploading' });

            self.renderFileList();

            // If we don't have an offer yet, store the file to upload after offer creation
            if (!self.currentIdOffer) {
                // Store file for later upload after offer is created
                self.pendingFileIds.push({ id: fileId, file: file });
                self.updateFileStatus(fileId, 'pending', 'Will upload when quotation is created');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'upload-attachment');
            formData.append('id_offer', self.currentIdOffer);
            formData.append('id_offer_product', '0'); // Will be set after product is added
            formData.append('file', file);

            // Show progress
            self.updateFileStatus(fileId, 'uploading', 'Uploading...');

            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            var percent = Math.round((e.loaded / e.total) * 100);
                            self.updateFileProgress(fileId, percent);
                        }
                    }, false);
                    return xhr;
                },
                success: function (resp) {
                    if (resp.success) {
                        self.updateFileStatus(fileId, 'done', 'Uploaded');
                    } else {
                        self.updateFileStatus(fileId, 'error', resp.error || 'Upload failed');
                    }
                },
                error: function () {
                    self.updateFileStatus(fileId, 'error', 'Upload failed');
                }
            });
        },

        removeFile: function (fileId) {
            var self = this;
            // Remove from local list
            self.uploadedFiles = self.uploadedFiles.filter(function (f) { return f.id !== fileId; });
            self.pendingFileIds = self.pendingFileIds.filter(function (f) { return f.id !== fileId; });
            self.renderFileList();
        },

        renderFileList: function () {
            var self = this;
            var html = '';

            if (!self.uploadedFiles.length) {
                $('#offer-file-list').html('');
                return;
            }

            $.each(self.uploadedFiles, function (i, f) {
                var sizeStr = self.formatFileSize(f.size);
                var statusBadge = '';
                var progressBar = '';

                if (f.status === 'uploading') {
                    statusBadge = '<span class="offer-file-status uploading">' + (f.statusText || 'Uploading...') + '</span>';
                    if (f.progress !== undefined) {
                        progressBar = '<div class="offer-file-progress"><div class="offer-file-progress-bar" style="width:' + f.progress + '%"></div></div>';
                    }
                } else if (f.status === 'done') {
                    statusBadge = '<span class="offer-file-status done"><i class="material-icons">check_circle</i></span>';
                } else if (f.status === 'error') {
                    statusBadge = '<span class="offer-file-status error"><i class="material-icons">error</i> ' + (f.statusText || '') + '</span>';
                } else if (f.status === 'pending') {
                    statusBadge = '<span class="offer-file-status pending">' + (f.statusText || 'Pending') + '</span>';
                }

                html += '<div class="offer-file-item" data-file-id="' + f.id + '">';
                html += '<div class="offer-file-icon"><i class="material-icons">insert_drive_file</i></div>';
                html += '<div class="offer-file-details">';
                html += '<div class="offer-file-name">' + self.escapeHtml(f.name) + ' <span class="offer-file-size">(' + sizeStr + ')</span></div>';
                html += progressBar;
                html += statusBadge;
                html += '</div>';
                html += '<button type="button" class="btn btn-sm btn-outline-danger offer-file-remove" data-file-id="' + f.id + '"><i class="material-icons">delete</i></button>';
                html += '</div>';
            });

            $('#offer-file-list').html(html);
        },

        updateFileStatus: function (fileId, status, text) {
            var self = this;
            $.each(self.uploadedFiles, function (i, f) {
                if (f.id === fileId) {
                    f.status = status;
                    f.statusText = text;
                }
            });
            self.renderFileList();
        },

        updateFileProgress: function (fileId, percent) {
            var self = this;
            $.each(self.uploadedFiles, function (i, f) {
                if (f.id === fileId) {
                    f.progress = percent;
                }
            });
            self.renderFileList();
        },

        showUploadError: function (msg) {
            $('#offer-upload-error').text(msg).show();
        },

        formatFileSize: function (bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },

        // ── Add To Quote Cart ──

        bindAddToQuoteEvents: function () {
            var self = this;
            $('#btn-add-to-quote').on('click', function () {
                self.collectConfigAndAddToQuote();
            });
        },

        collectConfigAndAddToQuote: function () {
            var self = this;

            if (!self.currentIdProduct) {
                self.notify('error', 'Please select a product first.');
                return;
            }

            // Collect configuration from the configurator form
            var config = {};
            var quantity = 1;

            // Collect hidden inputs with variable data
            $('#configurator-content').find('input[name^="variable_"]').each(function () {
                var $el = $(this);
                var name = $el.attr('name');
                var val = $el.val();
                config[name] = val;

                // Check if this is a quantity variable (type 1 or 4 with number input)
                if ($el.is('input[type="number"]')) {
                    var formulaName = $el.data('formula-name') || '';
                    if (formulaName === 'oplage' || formulaName === 'quantity' || $el.attr('id') === 'qty_input') {
                        quantity = parseInt(val) || 1;
                    }
                }
            });

            // Also collect from visible selects
            $('#configurator-content').find('select[name^="variable_"]').each(function () {
                var $el = $(this);
                var name = $el.attr('name');
                config[name] = $el.val();
            });

            // Collect from text inputs
            $('#configurator-content').find('input[type="text"][name^="variable_"]').each(function () {
                var $el = $(this);
                config[$el.attr('name')] = $el.val();
            });

            var customerNote = $('#offer-product-note').val() || '';

            // Validate via banned combination check first
            $('#offer-config-error').hide();

            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: $.extend({ action: 'validate-configuration', id_product: self.currentIdProduct }, config),
                success: function (resp) {
                    if (resp.success && resp.validation && resp.validation.banned) {
                        $('#offer-config-error').text(resp.validation.reason || 'This combination is not allowed.').show();
                        return;
                    }

                    // Validation passed, proceed to add product
                    if (!self.currentIdOffer) {
                        self.createDraftOffer(function () {
                            self.addProductToQuote(config, quantity, customerNote);
                        });
                    } else {
                        self.addProductToQuote(config, quantity, customerNote);
                    }
                },
                error: function () {
                    // If validation endpoint fails, proceed anyway (server-side will catch it)
                    if (!self.currentIdOffer) {
                        self.createDraftOffer(function () {
                            self.addProductToQuote(config, quantity, customerNote);
                        });
                    } else {
                        self.addProductToQuote(config, quantity, customerNote);
                    }
                }
            });
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

        addProductToQuote: function (config, quantity, customerNote) {
            var self = this;

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

                        // Upload any pending files now that we have an offer product
                        if (resp.id_offer_product && self.pendingFileIds.length) {
                            self.uploadPendingFiles(resp.id_offer_product);
                        }

                        // Reset the product selection for the next product
                        self.clearSelectedProduct();
                    } else {
                        self.notify('error', resp.error || 'Failed to add product.');
                    }
                },
                error: function () {
                    self.notify('error', 'Failed to add product to quotation.');
                }
            });
        },

        uploadPendingFiles: function (idOfferProduct) {
            var self = this;
            var pending = self.pendingFileIds.slice();
            self.pendingFileIds = [];

            $.each(pending, function (i, item) {
                var formData = new FormData();
                formData.append('action', 'upload-attachment');
                formData.append('id_offer', self.currentIdOffer);
                formData.append('id_offer_product', idOfferProduct);
                formData.append('file', item.file);

                self.updateFileStatus(item.id, 'uploading', 'Uploading...');

                $.ajax({
                    url: self.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (resp) {
                        if (resp.success) {
                            self.updateFileStatus(item.id, 'done', 'Uploaded');
                        } else {
                            self.updateFileStatus(item.id, 'error', resp.error || 'Upload failed');
                        }
                    },
                    error: function () {
                        self.updateFileStatus(item.id, 'error', 'Upload failed');
                    }
                });
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
            var self = this;
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
                html += '<div class="quote-cart-item-name">' + self.escapeHtml(p.product_name) + '</div>';
                html += '<div class="quote-cart-item-config small text-muted">' + self.escapeHtml(p.configuration_summary || '') + '</div>';
                html += '<div class="quote-cart-item-qty small">Qty: ' + p.quantity + '</div>';
                if (p.attachment_count > 0) {
                    html += '<div class="quote-cart-item-attachments small"><i class="material-icons">attach_file</i> ' + p.attachment_count + ' file(s)</div>';
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

        escapeHtml: function (text) {
            if (!text) return '';
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, function (m) { return map[m]; });
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
