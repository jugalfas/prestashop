/**
 * Offer Admin - JavaScript for the admin offer management interface.
 *
 * Handles price offering, cancellation, expiry extension, order conversion,
 * attachment management, date picker filtering, and PDF printing.
 */

(function ($) {
    'use strict';

    var OfferAdmin = {
        ajaxUrl: '',

        init: function () {
            this.ajaxUrl = $('#ajax-url').val();

            this.bindPriceOfferEvents();
            this.bindCancelEvents();
            this.bindExtendExpiryEvents();
            this.bindConvertOrderEvents();
            this.bindDeleteEvents();
            this.bindAttachmentEvents();
            this.bindFilterEvents();
            this.bindDatePickerEvents();
            this.bindPrintPdfEvents();
            this.bindAttachmentDownloadEvents();
            this.bindRecalculatePriceEvents();
            this.bindDuplicateOfferEvents();
            // Show More/Less disabled in Admin — CSS/JS conflicts in admin theme.
            // Full summary is shown by default (no truncation) in admin.
            // this.bindConfigSummaryToggle();
        },

        // ── Configuration Summary: Show More / Show Less ──
        // DISABLED in admin — re-enable after admin CSS/JS issues resolved.
        // bindConfigSummaryToggle: function () {
        //     $(document).on('click', '.ppc-cs-toggle', function (e) {
        //         e.preventDefault();
        //         var $summary = $(this).closest('.ppc-config-summary');
        //         var expanded = $(this).data('expanded') === 1;
        //         if (expanded) {
        //             $summary.removeClass('ppc-cs-expanded');
        //             $(this).data('expanded', 0);
        //         } else {
        //             $summary.addClass('ppc-cs-expanded');
        //             $(this).data('expanded', 1);
        //         }
        //     });
        // },

        // ── Price Offer ──

        bindPriceOfferEvents: function () {
            var self = this;

            $('.btn-save-price').on('click', function () {
                var $card = $(this).closest('.price-offer-form');
                var idOffer = $(this).data('id-offer');
                var idOfferProduct = $(this).data('id-offer-product');

                var data = {
                    action: 'ajax',
                    sub_action: 'offer-price',
                    id_offer: idOffer,
                    id_offer_product: idOfferProduct,
                    offered_price: $card.find('.offered-price-input').val(),
                    estimated_price: $card.find('.estimated-price-input').val(),
                    weight: $card.find('.weight-input').val(),
                    production_time: $card.find('.production-time-input').val(),
                    admin_note: $card.find('.admin-note-input').val(),
                    expire_days: $card.find('.expire-days-input').val()
                };

                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="icon icon-spinner icon-spin"></i> Saving...');

                $.ajax({
                    url: self.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function (resp) {
                        if (resp.success) {
                            self.showAlert('success', 'Price offered and email sent to customer.');
                            setTimeout(function () { location.reload(); }, 2000);
                        } else {
                            self.showAlert('error', resp.error || 'Failed to save price.');
                            $btn.prop('disabled', false).html('<i class="icon icon-save"></i> Save & Send');
                        }
                    },
                    error: function () {
                        self.showAlert('error', 'AJAX error.');
                        $btn.prop('disabled', false).html('<i class="icon icon-save"></i> Save & Send');
                    }
                });
            });
        },

        // ── Cancel ──

        bindCancelEvents: function () {
            var self = this;

            $('#btn-cancel-offer').on('click', function () {
                if (!confirm('Are you sure you want to cancel this offer?')) return;

                var idOffer = $(this).data('id-offer');
                var $btn = $(this);
                var originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="icon icon-spinner icon-spin"></i> Cancelling...');

                $.ajax({
                    url: self.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'ajax', sub_action: 'cancel', id_offer: idOffer },
                    success: function (resp) {
                        if (resp.success) {
                            self.showAlert('success', 'Offer cancelled.');
                            setTimeout(function () { location.reload(); }, 1500);
                        } else {
                            self.showAlert('error', resp.error || 'Failed to cancel.');
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function () {
                        self.showAlert('error', 'AJAX error.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        },

        // ── Extend Expiry ──

        bindExtendExpiryEvents: function () {
            var self = this;

            $('#btn-extend-expiry').on('click', function () {
                var days = prompt('Extend expiry by how many days?', '7');
                if (!days || isNaN(days)) return;

                var idOffer = $(this).data('id-offer');

                $.ajax({
                    url: self.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'ajax', sub_action: 'extend-expiry', id_offer: idOffer, days: days },
                    success: function (resp) {
                        if (resp.success) {
                            self.showAlert('success', 'Expiry extended by ' + days + ' days.');
                            setTimeout(function () { location.reload(); }, 1500);
                        } else {
                            self.showAlert('error', resp.error || 'Failed to extend expiry.');
                        }
                    }
                });
            });
        },

        // ── Convert To Order ──

        bindConvertOrderEvents: function () {
            var self = this;

            $('#btn-convert-order').on('click', function () {
                $('#convert-order-modal').modal('show');
            });

            $('#btn-confirm-convert').on('click', function () {
                var idOffer = $(this).data('id-offer');

                var data = {
                    action: 'ajax',
                    sub_action: 'convert-to-order',
                    id_offer: idOffer,
                    id_address_delivery: $('#convert-address-delivery').val(),
                    id_address_invoice: $('#convert-address-invoice').val(),
                    id_carrier: $('#convert-carrier').val(),
                    payment_module_name: $('#convert-payment').val()
                };

                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="icon icon-spinner icon-spin"></i> Creating...');

                $.ajax({
                    url: self.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function (resp) {
                        if (resp.success) {
                            self.showAlert('success', 'Order #' + resp.id_order + ' created.');
                            setTimeout(function () {
                                location.href = $('#list-url').val() + '&id_order=' + resp.id_order;
                            }, 2000);
                        } else {
                            self.showAlert('error', resp.error || 'Failed to create order.');
                            $btn.prop('disabled', false).html('<i class="icon icon-check"></i> Create Order');
                        }
                    },
                    error: function () {
                        self.showAlert('error', 'AJAX error.');
                        $btn.prop('disabled', false).html('<i class="icon icon-check"></i> Create Order');
                    }
                });
            });
        },

        // ── Delete ──

        bindDeleteEvents: function () {
            var self = this;

            $('.btn-delete-offer').on('click', function () {
                if (!confirm('Delete this offer permanently? This cannot be undone.')) return;
                var idOffer = $(this).data('id-offer');

                $.ajax({
                    url: self.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'ajax', sub_action: 'delete-offer', id_offer: idOffer },
                    success: function (resp) {
                        if (resp.success) {
                            location.reload();
                        }
                    }
                });
            });

            $('.btn-delete-product').on('click', function () {
                if (!confirm('Remove this product from the offer?')) return;
                var idOffer = $(this).data('id-offer');
                var idOfferProduct = $(this).data('id-offer-product');

                $.ajax({
                    url: self.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'ajax', sub_action: 'delete-product', id_offer: idOffer, id_offer_product: idOfferProduct },
                    success: function (resp) {
                        if (resp.success) {
                            location.reload();
                        }
                    }
                });
            });
        },

        // ── Attachments ──

        bindAttachmentEvents: function () {
            var self = this;

            $('.btn-upload-attachment').on('click', function () {
                var idOffer = $(this).data('id-offer');
                var idOfferProduct = $(this).data('id-offer-product');
                $('#upload-offer').val(idOffer);
                $('#upload-offer-product').val(idOfferProduct);
                $('#upload-attachment-input').click();
            });

            $('#upload-attachment-input').on('change', function () {
                if (!this.files.length) return;

                var formData = new FormData();
                formData.append('action', 'ajax');
                formData.append('sub_action', 'upload-attachment');
                formData.append('id_offer', $('#upload-offer').val());
                formData.append('id_offer_product', $('#upload-offer-product').val());
                formData.append('file', this.files[0]);

                $.ajax({
                    url: self.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (resp) {
                        if (resp.success) {
                            self.showAlert('success', 'File uploaded.');
                            setTimeout(function () { location.reload(); }, 1500);
                        } else {
                            self.showAlert('error', resp.error || 'Upload failed.');
                        }
                    }
                });
            });

            $('.btn-remove-attachment').on('click', function () {
                var idOffer = $(this).data('id-offer');
                var idAttachment = $(this).data('id-attachment');

                $.ajax({
                    url: self.ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'ajax', sub_action: 'remove-attachment', id_offer: idOffer, id_attachment: idAttachment },
                    success: function (resp) {
                        if (resp.success) {
                            location.reload();
                        }
                    }
                });
            });
        },

        // ── Filters ──

        bindFilterEvents: function () {
            var self = this;

            $('#btn-filter-offers').on('click', function () {
                var search = $('#offer-search').val();
                var status = $('#offer-status-filter').val();
                var dateFrom = $('#offer-date-from').val();
                var dateTo = $('#offer-date-to').val();

                var url = $('#list-url').val() + '&page=1';
                if (search) url += '&search=' + encodeURIComponent(search);
                if (status) url += '&status=' + encodeURIComponent(status);
                if (dateFrom) url += '&date_from=' + encodeURIComponent(dateFrom);
                if (dateTo) url += '&date_to=' + encodeURIComponent(dateTo);

                location.href = url;
            });
        },

        // ── Date Picker ──

        bindDatePickerEvents: function () {
            var self = this;

            $('.offer-datepicker').each(function () {
                self.initDatePicker(this);
            });

            $('.offer-date-clear').on('click', function () {
                var target = $(this).data('target');
                $('#' + target).val('');
                $(this).hide();
            });
        },

        initDatePicker: function (input) {
            var self = this;
            var $input = $(input);

            $input.on('focus click', function (e) {
                e.preventDefault();
                self.showDatePicker($input);
            });
        },

        showDatePicker: function ($input) {
            var self = this;
            $('.offer-datepicker-popup').remove();

            var initial = $input.val() ? new Date($input.val()) : new Date();
            if (isNaN(initial.getTime())) initial = new Date();

            var viewDate = new Date(initial.getFullYear(), initial.getMonth(), 1);
            var selectedDate = $input.val() ? new Date($input.val()) : null;

            var $popup = $('<div class="offer-datepicker-popup"></div>');
            $popup.css({
                top: $input.outerHeight() + 5,
                left: 0
            });

            function render() {
                var year = viewDate.getFullYear();
                var month = viewDate.getMonth();
                var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                var dayHeaders = ['Su','Mo','Tu','We','Th','Fr','Sa'];

                var html = '<div class="dp-header">';
                html += '<span class="dp-nav-btn dp-prev">&laquo;</span>';
                html += '<span class="dp-title">' + monthNames[month] + ' ' + year + '</span>';
                html += '<span class="dp-nav-btn dp-next">&raquo;</span>';
                html += '</div>';
                html += '<div class="dp-grid">';
                dayHeaders.forEach(function (d) {
                    html += '<div class="dp-day-header">' + d + '</div>';
                });

                var firstDay = new Date(year, month, 1).getDay();
                var daysInMonth = new Date(year, month + 1, 0).getDate();
                var prevMonthDays = new Date(year, month, 0).getDate();
                var today = new Date();
                today.setHours(0, 0, 0, 0);

                for (var i = firstDay - 1; i >= 0; i--) {
                    html += '<div class="dp-day dp-other-month">' + (prevMonthDays - i) + '</div>';
                }
                for (var d = 1; d <= daysInMonth; d++) {
                    var cellDate = new Date(year, month, d);
                    var classes = 'dp-day';
                    if (cellDate.getTime() === today.getTime()) classes += ' dp-today';
                    if (selectedDate && cellDate.getTime() === selectedDate.getTime()) classes += ' dp-selected';
                    html += '<div class="' + classes + '" data-day="' + d + '">' + d + '</div>';
                }
                var totalCells = firstDay + daysInMonth;
                var remaining = (7 - (totalCells % 7)) % 7;
                for (var j = 1; j <= remaining; j++) {
                    html += '<div class="dp-day dp-other-month">' + j + '</div>';
                }
                html += '</div>';
                $popup.html(html);

                $popup.find('.dp-prev').on('click', function () {
                    viewDate.setMonth(viewDate.getMonth() - 1);
                    render();
                });
                $popup.find('.dp-next').on('click', function () {
                    viewDate.setMonth(viewDate.getMonth() + 1);
                    render();
                });
                $popup.find('.dp-day:not(.dp-other-month)').on('click', function () {
                    var day = parseInt($(this).data('day'), 10);
                    var picked = new Date(year, month, day);
                    var formatted = picked.getFullYear() + '-' +
                        String(picked.getMonth() + 1).padStart(2, '0') + '-' +
                        String(picked.getDate()).padStart(2, '0');
                    $input.val(formatted);
                    $input.siblings('.offer-date-clear').show();
                    self.closeDatePicker();
                });
            }

            render();

            $input.parent().append($popup);

            $(document).one('click.dpClose', function (e) {
                if (!$(e.target).closest('.date-picker-group').length) {
                    self.closeDatePicker();
                }
            });
        },

        closeDatePicker: function () {
            $('.offer-datepicker-popup').remove();
            $(document).off('click.dpClose');
        },

        // ── Print PDF (Row + Detail) ──

        bindPrintPdfEvents: function () {
            var self = this;

            $('.btn-print-pdf-row').on('click', function () {
                var idOffer = $(this).data('id-offer');
                self.printPdf(idOffer, this);
            });

            $('.btn-print-pdf').on('click', function () {
                var idOffer = $(this).data('id-offer');
                self.printPdf(idOffer, this);
            });
        },

        printPdf: function (idOffer, btn) {
            var self = this;
            var $btn = $(btn);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="icon icon-refresh icon-spin"></i>');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', self.ajaxUrl, true);
            xhr.responseType = 'blob';
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function () {
                if (xhr.status === 200) {
                    var disposition = xhr.getResponseHeader('Content-Disposition') || '';
                    var matches = disposition.match(/filename="?([^"]+)"?/);
                    var filename = matches ? matches[1] : 'offer.pdf';

                    var blob = xhr.response;
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                    $btn.prop('disabled', false).html(originalHtml);
                } else {
                    self.showAlert('error', 'Failed to generate PDF.');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            };

            xhr.onerror = function () {
                self.showAlert('error', 'Failed to generate PDF.');
                $btn.prop('disabled', false).html(originalHtml);
            };

            xhr.send('action=ajax&sub_action=print-pdf&id_offer=' + idOffer);
        },

        // ── Download Attachment ──

        bindAttachmentDownloadEvents: function () {
            var self = this;

            $(document).on('click', '.btn-download-attachment', function () {
                var idAttachment = $(this).data('id-attachment');
                self.downloadAttachment(idAttachment, this);
            });
        },

        downloadAttachment: function (idAttachment, btn) {
            var self = this;
            var $btn = $(btn);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="icon icon-refresh icon-spin"></i>');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', self.ajaxUrl, true);
            xhr.responseType = 'blob';
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function () {
                if (xhr.status === 200) {
                    var ct = xhr.getResponseHeader('Content-Type') || '';
                    if (ct.indexOf('application/json') !== -1) {
                        self.showAlert('error', 'File not found on disk.');
                    } else {
                        var disposition = xhr.getResponseHeader('Content-Disposition') || '';
                        var matches = disposition.match(/filename="?([^"]+)"?/);
                        var filename = matches ? matches[1] : 'attachment';
                        var blob = xhr.response;
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    }
                } else {
                    self.showAlert('error', 'Download failed.');
                }
                $btn.prop('disabled', false).html(originalHtml);
            };

            xhr.onerror = function () {
                self.showAlert('error', 'Download failed.');
                $btn.prop('disabled', false).html(originalHtml);
            };

            xhr.send('action=ajax&sub_action=download-attachment&id_attachment=' + idAttachment);
        },

        // ── Recalculate Live Price & Weight ──

        bindRecalculatePriceEvents: function () {
            var self = this;

            $(document).on('click', '.btn-recalculate-price', function () {
                var idOffer = $(this).data('id-offer');
                var idOfferProduct = $(this).data('id-offer-product');
                var idProduct = $(this).data('id-product');
                self.recalculatePrice(idOffer, idOfferProduct, idProduct, this);
            });

            $(document).on('click', '.btn-apply-recalc', function () {
                var idOfferProduct = $(this).data('id-offer-product');
                var price = $(this).data('price');
                var weight = $(this).data('weight');
                self.applyRecalc(idOfferProduct, price, weight, this);
            });
        },

        recalculatePrice: function (idOffer, idOfferProduct, idProduct, btn) {
            var self = this;
            var $btn = $(btn);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="icon icon-refresh icon-spin"></i> ' + originalHtml);

            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'ajax',
                    sub_action: 'recalculate-price',
                    id_offer: idOffer,
                    id_offer_product: idOfferProduct,
                    id_product: idProduct
                },
                success: function (resp) {
                    if (resp.success) {
                        var $card = $btn.closest('.offer-product-card, .panel, [data-id-offer-product], .card');
                        if (!$card.length) $card = $btn.parent();
                        var priceStr = resp.estimated_price_formatted || '-';
                        var weightStr = resp.estimated_weight_formatted || '-';

                        var $info = $card.find('.recalc-info');
                        if (!$info.length) {
                            $info = $('<div class="recalc-info" style="margin-top:8px;padding:8px;border:1px solid #b8daff;border-radius:6px;background:#f0f8ff;"></div>');
                            $btn.after($info);
                        }
                        $info.html(
                            '<div class="small"><strong>Live Price:</strong> ' + priceStr + '</div>' +
                            '<div class="small"><strong>Live Weight:</strong> ' + weightStr + '</div>' +
                            '<button type="button" class="btn btn-success btn-xs btn-apply-recalc" data-id-offer-product="' + idOfferProduct + '" data-price="' + (resp.estimated_price || '') + '" data-weight="' + (resp.estimated_weight || '') + '" style="margin-top:6px;">' +
                            '<i class="icon icon-check"></i> Apply as Estimated</button>'
                        );
                    } else {
                        self.showAlert('error', resp.error || 'Recalculation failed.');
                    }
                    $btn.prop('disabled', false).html(originalHtml);
                },
                error: function () {
                    self.showAlert('error', 'Recalculation failed.');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        },

        applyRecalc: function (idOfferProduct, price, weight, btn) {
            var self = this;
            var $btn = $(btn);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="icon icon-refresh icon-spin"></i> Applying...');

            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'ajax',
                    sub_action: 'apply-recalc',
                    id_offer_product: idOfferProduct,
                    price: price,
                    weight: weight
                },
                success: function (resp) {
                    if (resp.success) {
                        var $info = $btn.closest('.recalc-info');
                        $info.html('<div class="alert alert-success" style="margin:0;padding:6px 10px;\"><i class="icon icon-check"></i> Updated — ' + resp.estimated_price_formatted + ' / ' + resp.estimated_weight_formatted + '</div>');
                        self.showAlert('success', 'Estimated price and weight updated.');
                    } else {
                        self.showAlert('error', resp.error || 'Failed to apply.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function () {
                    self.showAlert('error', 'Failed to apply.');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        },

        // ── Utility ──

        showAlert: function (type, message) {
            var cls = type === 'success' ? 'alert-success' : 'alert-danger';
            var alert = '<div class="alert ' + cls + '" style="margin-top: 10px;">' + message + '</div>';
            $('.offer-admin-detail').prepend(alert);
        },

        // ── Duplicate Offer ──

        bindDuplicateOfferEvents: function () {
            var self = this;

            $(document).on('click', '.btn-duplicate-offer-admin', function () {
                var idOffer = $(this).data('id-offer');
                self.duplicateOffer(idOffer, this);
            });
        },

        duplicateOffer: function (idOffer, btn) {
            var self = this;
            var $btn = $(btn);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="icon icon-refresh icon-spin"></i> Duplicating...');

            $.ajax({
                url: self.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'ajax',
                    sub_action: 'duplicate-offer',
                    id_offer: idOffer
                },
                success: function (resp) {
                    if (resp.success) {
                        self.showAlert('success', 'Offer duplicated successfully. Redirecting to new offer...');
                        setTimeout(function () {
                            window.location.href = resp.redirect_url;
                        }, 1500);
                    } else {
                        self.showAlert('error', resp.error || 'Failed to duplicate offer.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function () {
                    self.showAlert('error', 'Failed to duplicate offer.');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        }
    };

    $(function () {
        if ($('#ajax-url').length) {
            OfferAdmin.init();
        }
    });

})(jQuery);
