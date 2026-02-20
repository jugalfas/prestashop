/**
* 2017-2018 Krupaludev
*
* Bulk Discount Manager for Imorting and Creating
*
* NOTICE OF LICENSE
*
* This source file is subject to the General Public License (GPL 2.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/GPL-2.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade the module to newer
* versions in the future.
*
*  @author    Vipul <krupaludev@icloud.com>
*  @copyright 2017-2018 Krupaludev
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/
(function ($) {
    var url = '';

    var kdcontent = '';

    var modalimportexportcsv = '';

    $.fn.AdminProductPriceConfig = function (options) {
      
        // variable initializtion
        url = options.url;
        kdcontent = $('#kd-content');

        modalimportexportcsv = $("#modalimportexportcsv");

        kdcontent.find('.alert-close').on('click blur', onAlertClose);
        // kdcontent.find('#kd-add-order').on('click', onAddOrderClick);
        kdcontent.find('#kd-importcsv').on('click', onImportCSVClick);

        kdcontent.find('#kd-reload').on('click', onReloadClick);

        modalimportexportcsv.on('click', '.kd-importexportcsv-form-submit', onImportExportCSVSubmitClick);

        // jquery file upload
        modalimportexportcsv.find('#fileupload').fileupload({
            url: url + '&method=processAttachment',
            acceptFileTypes: /(\.|\/)(csv)$/i
        });
        modalimportexportcsv.find('#fileupload').addClass('fileupload-processing');
        $.ajax({
            method: 'get',
            url: modalimportexportcsv.find('#fileupload').fileupload('option', 'url'),
            dataType: 'json',
            context: modalimportexportcsv.find('#fileupload')[0]
        }).always(function () {
            $(this).removeClass('fileupload-processing');
        }).done(function (result) {
            $(this).fileupload('option', 'done').call(this, $.Event('done'), {result: result});
        });

    };

    var onReloadClick = function (e) {
        location.reload(true);
    };


    var onAlertClose = function () {
        $(this).closest('.alert').fadeOut('slow');
    };

    var onImportCSVClick = function () {
        resetImportExportModal('import-csv');
        kdcontent.find('#modalimportexportcsv').modal('show');
        kdcontent.find("#csv_file").focus();
    };

    var onExportCSVClick = function () {
        resetImportExportModal('export-csv');
        kdcontent.find('#modalimportexportcsv').modal('show');
        kdcontent.find("#csv_file").focus();
    };

    var resetImportExportModal = function (resetfor) {
        modalimportexportcsv.find('#csv-file-form-group').show();
        modalimportexportcsv.find('#csv-separator-form-group').show();
        modalimportexportcsv.find('.panel-footer').show();

        modalimportexportcsv.find("#kd-importexportcsv-action").val(resetfor);

        switch (resetfor) {
            case 'import-csv':
                modalimportexportcsv.find(".modal-title").html('<i class="glyphicon glyphicon-import"></i> <span>Import CSV</span>');
                modalimportexportcsv.find('.panel-footer').hide();
                break;
            case 'export-csv':
                modalimportexportcsv.find(".modal-title").html('<i class="glyphicon glyphicon-export"></i> <span>Export CSV</span>');
                modalimportexportcsv.find('#csv-file-form-group').hide();
                break;
        }
    };

    var onImportExportCSVSubmitClick = function () {
            csv_separator = $.trim(modalimportexportcsv.find("#csv_separator").val()),
            action = modalimportexportcsv.find("#kd-importexportcsv-action").val();

        if (csv_separator == '') {
            alert('CSV separator is required');
            return;
        }


        switch (action) {
            case 'import-csv':
                var importstatustimeout,
                    sendimportstatusrequest = true,
                    me = $(this);

                ajaxRequest({
                    'method': 'import',
                    'file': $(this).attr('data-file'),
                    'csv_separator': csv_separator,
                    'beforeSend': function () {
                        me.find('.glyphicon').after(' <span class="import-status">(0)</span>');
                        importstatustimeout = setInterval(function () {
                            if (sendimportstatusrequest) {
                                ajaxRequest({
                                    'method': 'getImportStatus',
                                    'beforeSend': function () {
                                        sendimportstatusrequest = false;
                                    }
                                }, $('#kd-table-loader'))
                                    .success(function (response) {
                                        me.find('.import-status').text('(' + response.rowImported + ')');
                                        sendimportstatusrequest = true;
                                    });
                            }
                        }, 3000);
                    }
                }, me)
                    .success(function (response) {
                        if (response.status == 'success' || response.status == 'warning') {
                            onReloadClick();
                        }
                    })
                    .complete(function () {
                        me.find('.import-status').remove();
                        clearInterval(importstatustimeout);
                    });

                break;
            case 'export-csv':
                window.open(url + '&method=export&header=csv&csv_separator=' + csv_separator, '_blank');

                break;
        }
    };



    var ajaxRequest = function (data, button) {
        if (!button.find('i').hasClass('icon-spinner')) {
            button.find('i').data('iconclass', button.find('i').attr('class'));
        }

        return $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data,
            beforeSend: function () {
                button.find('i').removeClass(button.find('i').data('iconclass')).addClass('icon-spinner');
            },
            complete: function () {
                button.find('i').removeClass('icon-spinner').addClass(button.find('i').data('iconclass'));
            },
            success: function (response) {
                showAjaxRequestMessage(response.status, response.message);
            }
        });
    };

    var showAjaxRequestMessage = function (status, message) {
        if (typeof status != 'undefined' && status != '' && typeof message != 'undefined' && message != '') {
            kdcontent.find('.alert').removeClass('alert-success');
            kdcontent.find('.alert').removeClass('alert-danger');
            kdcontent.find('.alert').removeClass('alert-warning');
            kdcontent.find('.alert-message').text(message);
            kdcontent.find('.alert').addClass('alert-' + status).fadeIn('slow');
            kdcontent.find('.alert-close').focus();
        }
    };

})(jQuery);

$(function () {
    $.extend({
        getUrlVars: function () {
            var vars = [], hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            return vars;
        },
        getUrlVar: function (name) {
            return $.getUrlVars()[name];
        }
    });

    $(document).AdminProductCustomizer({
        'url': 'index.php?controller=AdminProductCustomizer&token=' + $.getUrlVar('token') + '&ajax=1'
    });
});
