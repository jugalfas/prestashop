/**
 * Offer Admin - JavaScript for the admin offer management interface.
 *
 * Handles price offering, cancellation, expiry extension,
 * order conversion, and attachment management.
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
        },

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
                        }
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

        // ── Utility ──

        showAlert: function (type, message) {
            var cls = type === 'success' ? 'alert-success' : 'alert-danger';
            var alert = '<div class="alert ' + cls + '" style="margin-top: 10px;">' + message + '</div>';
            $('.offer-admin-detail').prepend(alert);
        }
    };

    $(function () {
        if ($('#ajax-url').length) {
            OfferAdmin.init();
        }
    });

})(jQuery);
