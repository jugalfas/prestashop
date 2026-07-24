/**
 * formula-conditions.js - AJAX CRUD for Formula Conditions admin tab.
 *
 * All operations (create, update, delete, toggle, load options) use AJAX.
 * No page refresh. All events are delegated so they survive table reloads.
 */
(function ($) {
    'use strict';

    // ── State ──
    var fcState = {
        ajaxUrl: window.location.href.split('#')[0] + '&ajax=1',
        idProduct: 0,
        busy: false
    };

    $(document).ready(function () {
        fcState.idProduct = $('.af').data('id_product');
        loadConditionsTable();

        // ── Clean up Bootstrap modal state after hide ──
        // Bootstrap 3 can leave a stale .modal-backdrop and body.modal-open
        // if the fade-out transition doesn't complete cleanly (common when
        // SweetAlert is on the same page).  This ensures the modal can be
        // re-opened reliably any number of times without a page refresh.
        $('#formula_condition_modal').on('hidden.bs.modal', function () {
            // Always force-clean any stale backdrop / body class left by Bootstrap 3
            // so the next modal('show') does not trigger the wrong modal.
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
            // Reset the modal's internal Bootstrap data flag
            $(this).removeData('bs.modal');
        });

        // ── Open "Add" modal ──
        // Use .off().on() to guarantee no duplicate or conflicting handlers
        // are attached to this selector.  stopImmediatePropagation prevents
        // any other delegated document-level handlers (e.g. Bootstrap's
        // data-toggle="modal" or Rules-feature handlers) from firing.
        $(document)
            .off('click', '#btn-add-formula-condition')
            .on('click', '#btn-add-formula-condition', function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                // Force-clean any stale modal state before showing
                //forceCloseAllModals();
                resetForm();
                $('#formula_condition_modal .modal-title').text('Add Formula Condition');
                $('#btn-save-condition').attr('data-mode', 'create');
                $('#formula_condition_modal').modal('show');
            });

        // ── Variable change → load options dynamically ──
        $(document).on('change', '#fc_id_variable', function () {
            var idVariable = $(this).val();
            var $valueWrap = $('#fc_value_wrap');
            var $spinner = $('#fc_variable_spinner');

            if (!idVariable) {
                $valueWrap.hide();
                $('#fc_id_option').html('<option value="0">--</option>');
                return;
            }

            $spinner.show();
            $.ajax({
                url: fcState.ajaxUrl + '&action=FcGetVariableInfo',
                type: 'POST',
                dataType: 'json',
                data: {
                    id_variable: idVariable,
                    id_product: fcState.idProduct
                }
            }).done(function (resp) {
                $spinner.hide();
                if (!resp.success) {
                    $valueWrap.hide();
                    notify('error', resp.message || 'Failed to load variable info');
                    return;
                }

                if (resp.type == 2) {
                    var $select = $('#fc_id_option');
                    $select.html('<option value="0">-- Select option --</option>');
                    $.each(resp.options, function (i, opt) {
                        $select.append('<option value="' + opt.id_option + '">' + opt.label + '</option>');
                    });
                    $('#fc_value_label').text('Variable Value');
                    $('#fc_value_select').show();
                    $('#fc_value_input').hide();
                    $valueWrap.show();
                    // Notify any waiting edit handler that options have loaded
                    $('#fc_id_variable').trigger('fc_options_loaded');
                } else {
                    $('#fc_value_label').text('Variable Value');
                    $('#fc_value_select').hide();
                    $('#fc_value_input').show();
                    $valueWrap.show();
                }
            }).fail(function () {
                $spinner.hide();
                notify('error', 'Failed to load variable options');
            });
        });

        // ── Save (create or update) ──
        $(document).on('click', '#btn-save-condition', function (e) {
            e.preventDefault();
            if (fcState.busy) return;

            var $btn = $(this);
            var mode = $btn.attr('data-mode');
            var idVariable = $('#fc_id_variable').val();
            var surchargeType = $('#fc_surcharge_type').val();
            var amount = $('#fc_amount').val();

            var idOption = 0;
            var customValue = '';
            var varType = $('#fc_value_select').is(':visible') ? 2 : 0;
            if (varType == 2) {
                idOption = $('#fc_id_option').val();
            } else {
                customValue = $('#fc_custom_value').val();
            }

            if (!idVariable || !surchargeType || !amount) {
                notify('error', 'Please fill in all required fields.');
                return;
            }

            var data = {
                id_product: fcState.idProduct,
                id_variable: idVariable,
                id_option: idOption,
                custom_value: customValue,
                surcharge_type: surchargeType,
                amount: amount,
                apply_type: $('#fc_apply_type').val(),
                notes: $('#fc_notes').val(),
                active: $('#fc_active').is(':checked') ? 1 : 0
            };

            if (mode === 'edit') {
                data.id_formula_condition = $('#fc_id_formula_condition').val();
            }

            fcState.busy = true;
            setBtnLoading($btn, true);

            $.ajax({
                url: fcState.ajaxUrl + '&action=' + (mode === 'edit' ? 'FcUpdate' : 'FcCreate'),
                type: 'POST',
                dataType: 'json',
                data: data
            }).done(function (resp) {
                if (resp.success) {
                    $('#formula_condition_modal').modal('hide');
                    notify('success', resp.message || 'Formula condition ' + (mode === 'edit' ? 'updated' : 'added') + ' successfully.');
                    loadConditionsTable();
                } else {
                    notify('error', resp.message || 'Failed to save formula condition.');
                }
            }).fail(function () {
                notify('error', 'Failed to save formula condition.');
            }).always(function () {
                fcState.busy = false;
                setBtnLoading($btn, false);
            });
        });

        // ── Edit ──
        // Same .off().on() + stopImmediatePropagation isolation as Add.
        $(document)
            .off('click', '.fc-edit')
            .on('click', '.fc-edit', function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                if (fcState.busy) return;
                var id = $(this).data('id');

                fcState.busy = true;
                var $link = $(this);
                var originalHtml = $link.html();
                $link.html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: fcState.ajaxUrl + '&action=FcGetCondition',
                    type: 'POST',
                    dataType: 'json',
                    data: { id_formula_condition: id }
                }).done(function (resp) {
                    if (!resp.success) {
                        notify('error', resp.message || 'Not found');
                        return;
                    }

                    var c = resp.condition;
                    // Force-clean stale modal state before showing
                    //forceCloseAllModals();
                    resetForm();
                    $('#fc_id_formula_condition').val(c.id_formula_condition);

                    // Set variable and trigger change, then set value after options load
                    var varType = resp.type;
                    $('#fc_id_variable').val(c.id_variable);

                    if (varType == 2) {
                        // For select type, we need to wait for options to load
                        $('#fc_id_variable').one('fc_options_loaded', function () {
                            $('#fc_id_option').val(c.id_option);
                        });
                        $('#fc_id_variable').trigger('change');
                    } else {
                        $('#fc_id_variable').trigger('change');
                        // For non-select, set the value directly
                        $('#fc_custom_value').val(c.id_option || '');
                    }

                    $('#fc_surcharge_type').val(c.surcharge_type);
                    $('#fc_amount').val(c.amount);
                    $('#fc_apply_type').val(c.apply_type);
                    $('#fc_notes').val(c.notes);
                    $('#fc_active').prop('checked', c.active == 1);

                    $('#formula_condition_modal .modal-title').text('Edit Formula Condition');
                    $('#btn-save-condition').attr('data-mode', 'edit');
                    $('#formula_condition_modal').modal('show');
                }).fail(function () {
                    notify('error', 'Failed to load condition data.');
                }).always(function () {
                    fcState.busy = false;
                    $link.html(originalHtml);
                });
            });

        // ── Delete ──
        $(document)
            .off('click', '.fc-delete')
            .on('click', '.fc-delete', function (e) {
            e.preventDefault();
            if (fcState.busy) return;

            var id = $(this).data('id');
            var $link = $(this);
            var originalHtml = $link.html();

            // Use inline confirm via swal v2
            swal({
                title: 'Are you sure?',
                text: 'This condition will be deleted.',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Cancel',
                        value: false,
                        visible: true
                    },
                    confirm: {
                        text: 'Yes, delete it!',
                        value: true,
                        closeModal: false
                    }
                },
                dangerMode: true
            }).then(function (confirmed) {
                if (!confirmed) return;

                fcState.busy = true;
                $link.html('<i class="fa fa-spinner fa-spin"></i>');
                $link.css('pointer-events', 'none');

                $.ajax({
                    url: fcState.ajaxUrl + '&action=FcDelete',
                    type: 'POST',
                    dataType: 'json',
                    data: { id_formula_condition: id }
                }).done(function (resp) {
                    swal.close();
                    if (resp.success) {
                        notify('success', 'Formula condition deleted successfully.');
                        loadConditionsTable();
                    } else {
                        notify('error', resp.message || 'Delete failed.');
                        $link.html(originalHtml);
                        $link.css('pointer-events', '');
                    }
                }).fail(function () {
                    swal.close();
                    notify('error', 'Delete failed.');
                    $link.html(originalHtml);
                    $link.css('pointer-events', '');
                }).always(function () {
                    fcState.busy = false;
                });
            });
        });

        // ── Toggle active status ──
        $(document)
            .off('change', '.fc-toggle-active')
            .on('change', '.fc-toggle-active', function () {
            if (fcState.busy) return;

            var $checkbox = $(this);
            var id = $checkbox.data('id');
            var newActive = $checkbox.is(':checked') ? 1 : 0;
            var $cell = $checkbox.closest('td');

            fcState.busy = true;

            // Replace checkbox with spinner
            var checkboxHtml = $cell.html();
            $cell.html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: fcState.ajaxUrl + '&action=FcToggleStatus',
                type: 'POST',
                dataType: 'json',
                data: {
                    id_formula_condition: id,
                    active: newActive
                }
            }).done(function (resp) {
                if (resp.success) {
                    notify('success', 'Status updated successfully.');
                    // Restore checkbox with updated state
                    $cell.html(checkboxHtml);
                    $cell.find('.fc-toggle-active').prop('checked', newActive == 1);
                } else {
                    notify('error', resp.message || 'Failed to update status.');
                    // Restore original checkbox (revert checked state)
                    $cell.html(checkboxHtml);
                    $cell.find('.fc-toggle-active').prop('checked', newActive != 1);
                }
            }).fail(function () {
                notify('error', 'Failed to update status.');
                $cell.html(checkboxHtml);
                $cell.find('.fc-toggle-active').prop('checked', newActive != 1);
            }).always(function () {
                fcState.busy = false;
            });
        });

        // ── Helpers ──

        // Force-close any visible modals and clean up stale Bootstrap 3 state.
        // This prevents the "wrong modal opens" bug where Bootstrap's internal
        // modal tracking gets confused after a show/hide cycle, causing it to
        // trigger #exampleModalCenter (add-rules-modal) instead of
        // #formula_condition_modal.
        function forceCloseAllModals() {
            // Hide any currently visible modal EXCEPT the FC modal
            $('.modal.in').not('#formula_condition_modal').each(function () {
                $(this).modal('hide');
            });
            // Remove stale backdrop and body class
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
        }

        function loadConditionsTable() {
            $.ajax({
                url: fcState.ajaxUrl + '&action=FcList',
                type: 'POST',
                dataType: 'json',
                data: { id_product: fcState.idProduct }
            }).done(function (resp) {
                if (resp.success && resp.html) {
                    $('#fc-table-body').html(resp.html);
                } else {
                    $('#fc-table-body').html('<tr><td colspan="9" class="text-center">No conditions found.</td></tr>');
                }
            }).fail(function () {
                $('#fc-table-body').html('<tr><td colspan="9" class="text-center text-danger">Failed to load conditions.</td></tr>');
            });
        }

        function resetForm() {
            $('#fc_id_formula_condition').val('');
            $('#fc_id_variable').val('');
            $('#fc_id_option').html('<option value="0">--</option>');
            $('#fc_custom_value').val('');
            $('#fc_surcharge_type').val('percentage');
            $('#fc_amount').val('');
            $('#fc_apply_type').val('total');
            $('#fc_notes').val('');
            $('#fc_active').prop('checked', true);
            $('#fc_value_wrap').hide();
            $('#fc_variable_spinner').hide();
        }

        function setBtnLoading($btn, loading) {
            if (loading) {
                $btn.data('original-html', $btn.html());
                $btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                $btn.prop('disabled', true);
            } else {
                $btn.html($btn.data('original-html') || 'Save');
                $btn.prop('disabled', false);
            }
        }

        function notify(type, message) {
            var $area = $('#fc-notification-area');
            if (!$area.length) return;

            var cls = type === 'success' ? 'fc-alert-success' : 'fc-alert-error';
            var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            $area.removeClass('fc-alert-success fc-alert-error').addClass(cls);
            $area.html('<i class="fa ' + icon + '"></i> ' + message);
            $area.slideDown(200);

            clearTimeout($area.data('timeout'));
            $area.data('timeout', setTimeout(function () {
                $area.slideUp(200);
            }, 4000));
        }

        // Make loadConditionsTable available for potential external calls
        window.fcReloadTable = loadConditionsTable;
    });

})(jQuery);
