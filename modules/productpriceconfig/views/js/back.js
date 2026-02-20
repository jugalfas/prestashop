/**
 *  @author    Amazzing
 *  @copyright Amazzing
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)*
 */

var ajax_action_path = window.location.href.split('#')[0] + '&ajax=1',
    productIds = [],
    productIdsTotal = 0;
var id_product = '';

$(document).ready(function() {
    id_product = $('.af').data('id_product');

    updateProductVariablePosition();
    activateTemplateSortable();
    $(document).on('click', 'a[href="#"], .list-group-item', function(e) {
        e.preventDefault();
    }).on('click', '.saveMultipleSettings', function() {
        id_product = $('.af').data('id_product');

        var $btn = $(this),
            $form = $btn.closest('.tab-pane').find('form');

        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&action=SaveTieredSettings&id_product=' + id_product,
            data: $form.serialize(),
            dataType: 'json',
            success: function(r) {
                if ('errors' in r) {
                    prependErrors($parent, utf8_decode(r.errors));
                } else {
                    $.growl.notice({ title: '', message: savedTxt });
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });

    }).on('click', '.saveFormulaSettings', function() {
        id_product = $('.af').data('id_product');

        var $btn = $(this),
            $form = $btn.closest('.tab-pane').find('form');

        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&action=SaveFormulaSettings&id_product=' + id_product,
            data: $form.serialize(),
            dataType: 'json',
            success: function(r) {
                if ('errors' in r) {
                    prependErrors($parent, utf8_decode(r.errors));
                } else {
                    $.growl.notice({ title: '', message: savedTxt });
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });
    }).on('click', '.savePercentageSettings', function() {
        id_product = $('.af').data('id_product');

        var $btn = $(this),
            $form = $btn.closest('.tab-pane').find('form');

        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&action=SavePercentageSettings&id_product=' + id_product,
            data: $form.serialize(),
            dataType: 'json',
            success: function(r) {
                if ('errors' in r) {
                    prependErrors($parent, utf8_decode(r.errors));
                } else {
                    $.growl.notice({ title: '', message: savedTxt });
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });

    }).on('click', '.saveBanedCombination', function() {
        id_product = $('.af').data('id_product');

        var $btn = $(this),
            $form = $btn.closest('.tab-pane').find('form');

        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&action=SaveBanCombSettings&id_product=' + id_product,
            data: $form.serialize(),
            dataType: 'json',
            success: function(r) {
                if ('errors' in r) {
                    prependErrors($parent, utf8_decode(r.errors));
                } else {
                    $.growl.notice({ title: '', message: savedTxt });
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });

    }).on('click', '.resetSelectors', function() {
        $(this).closest('form').find('input[type="text"]').each(function() {
            $(this).val($(this).attr('name'));
        });
    }).on('click', '.form-group.blocked', function() {
        alert('You should contact module developer to activate this option');
    });

    $('.menu-panel').find('.list-group-item').on('click', function() {
        $(this).addClass('active').siblings().removeClass('active');
        $($(this).attr('href')).addClass('active').siblings().removeClass('active');
    });

    $('.hookSelector').on('change', function() {
        var hookName = $(this).val(),
            params_string = 'action=UpdateHook&hook_name=' + hookName;
        $('.special-hook-note').toggleClass('hidden', hookName != 'displayAmazzingFilter');
        $('#hook-settings').find('.ajax-warning').html('').addClass('hidden');
        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&' + params_string,
            dataType: 'json',
            success: function(r) {
                console.dir(r);
                if ('warning' in r) {
                    $('#hook-settings').find('.ajax-warning').html(utf8_decode(r.warning)).removeClass('hidden');
                }
                if ('positions_form_html' in r) {
                    $('.dynamic-positions').html(utf8_decode(r.positions_form_html));
                    $.growl.notice({ title: '', message: savedTxt });
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });
    });

    $('.toggleIndexationSettings').on('click', function() {
        $(this).parent().toggleClass('show-settings');
    });


    $('.af').on('click', '.addNewFilter', function() {
        var params = 'action=ShowAvailableVariables',
            response = function(r) {
                $('#dynamic-popup').find('.dynamic-content').html(utf8_decode(r.content));
                $('#dynamic-popup').find('.modal-title').html(utf8_decode(r.title));
            };
        ajaxRequest(params, response);
    });

    $('.af').on('click', '.editTemplate, .addTemplate', function(e) {
        e.preventDefault();
        $('.scrollUp').click();
        var $btn = $(this);
        id = 0, controller = $(this).data('controller') || 'category';
        if ($(this).hasClass('editTemplate')) {
            id = $btn.closest('.af_template').attr('data-id');
            controller = $(this).closest('.af_template').attr('data-controller');
        }
        var params_string = 'action=CallVariableForm&template_controller=' + controller + '&id_product_variable=' + id;
        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&' + params_string,
            dataType: 'json',
            success: function(r) {
                if (!id) {
                    $('.template-list.' + controller).prepend(utf8_decode(r.form_html));
                } else {
                    $('.af_template[data-id="' + id + '"]').replaceWith(utf8_decode(r.form_html));
                }
                var $template = $('.af_template[data-id="' + r.id_product_variable + '"]');
                prepareTemplate($template);
                $template.find('.template_settings').slideDown();
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });
    });

    function prepareTemplate($template) {
        $template.on('click', '.removeFilter', function() {
            $(this).closest('.filter').remove();
        }).on('click', '.toggleFilterSettings', function() {
            $(this).closest('.filter').toggleClass('show-settings').siblings().removeClass('show-settings');
        }).on('change', '.f-type', function() {
            var $excOptions = $(this).closest('.filter').find('.type-exc');
            $excOptions.removeClass('hidden').filter('.not-for-' + $(this).val()).addClass('hidden');

        }).on('change', '[name="template_controller"]', function() {
            var controller = $(this).val();
            $template.find('.controller-option').addClass('hidden').filter('.' + controller).removeClass('hidden');
        }).tooltip({ selector: '.label-tooltip' });

        $template.find('[name="template_controller"]').change();
        $template.find('.controller-settings').find('.basic-item').each(function() {
            updateSelectedOptionsTxt($(this));
            $(this).find('.opt.closed').each(function() {
                markCheckedChildren($(this));
            });
        });

        prepareFilters($template.find('.f-list'));
        activateSortable();
        activateTemplateSortable();
        relatedOptions.init();
    }

    function prepareFilters($filtersList) {
        $filtersList.find('.form-group.custom-name').find('.lang-' + id_language).find('input')
            .off('keyup').on('keyup', function() {
                var $nameHolder = $(this).closest('.filter').find('.name'),
                    customName = $.trim($(this).val()) || $nameHolder.data('name');
                $nameHolder.html(customName);
            });
        $filtersList.find('.f-type').change();
        $filtersList.find('.nesting-lvl').off('change').on('change', function() {
            var allowTextBoxes = $(this).val() == '1',
                $tbOption = $(this).closest('.filter').find('.f-type').find('option[value="5"]');
            $tbOption.prop('disabled', !allowTextBoxes);
            if ($tbOption.is(':selected:disabled')) {
                $tbOption.parent().val('1').change(); // select checkbox if textbox is not available
            }
        }).change();
    }

    $('#dynamic-popup').on('click', '.close', function() {
        $('.dynamic-content, .dynamic-header-txt').html('');
    });
    $(document).on('click', '.addSelectedFilters', function() {
        if ($(this).hasClass('btn-blocked')) {
            return;
        }
        var keys = [];
        $('.filter-group-item.selected').not('.blocked').each(function() {
            keys.push($(this).data('key'));
        });

        $(this).text('Please wait...')
        id_product = $('.af').data('id_product');

        var params = 'action=SaveVariableElements&id_product=' + id_product + '&keys=' + keys.join(','),
            response = function(r) {
                $('#dynamic-popup').find('.close').click();
                location.reload(true);
            };
        ajaxRequest(params, response);
    });

    $(document).on('click', '.selectLanguage', function() {
        var idLang = $(this).data('lang');
        $(this).closest('form').find('.multilang').addClass('hidden').filter('.lang-' + idLang).removeClass('hidden');
    });


    function activateSortable() {
        //console.log('dawda');
        $('.f-list.sortable').sortable({
            placeholder: 'sortable-filter-placeholder',
        });
    }

    function activateTemplateSortable() {
        //console.log('sortable');
        $('.template-list.sortable').sortable({
            placeholder: 'sortable-filter-placeholder',
            cursor: 'move',
            update: function(event, ui) {
                var orders = $(this).sortable('toArray');
                console.log(orders);
                $('.thrown-errors').remove();
                var $parent = $(this).closest('.af_template'),
                    id_product = $('.af').data('id_product');
                $.ajax({
                    type: 'POST',
                    url: ajax_action_path + '&action=SaveProductVariablePosition&id_product=' + id_product,
                    data: {
                        positions: orders,
                    },
                    dataType: 'json',
                    success: function(r) {
                        if ('errors' in r) {
                            prependErrors($parent, utf8_decode(r.errors));
                        } else {
                            $.growl.notice({ title: '', message: savedTxt });
                        }
                    },
                    error: function(r) {
                        console.warn($(r.responseText).text() || r.responseText);
                    }
                });
            }
        });

    }

    function updateProductVariablePosition() {
        var orders = [];

        $('.template-list.sortable').children('.af_template').each(function() {
            orders.push($(this).data('id'));
        });
        //console.log(orders);
        $('.thrown-errors').remove();
        var $parent = $(this).closest('.af_template'),
            id_product = $('.af').data('id_product');
        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&action=SaveProductVariablePosition&id_product=' + id_product,
            data: {
                positions: orders,
            },
            dataType: 'json',
            success: function(r) {
                if ('errors' in r) {
                    prependErrors($parent, utf8_decode(r.errors));
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });

    }

    $('.af').on('click', '.template-action', function() {
        var action = $(this).data('action'),
            $parent = $(this).closest('.af_template'),
            idTemplate = $parent.data('id');
        if (action == 'Delete' && !confirm(areYouSureTxt)) {
            return;
        }
        var params = 'action=' + action + 'Variable&id_product_variable=' + idTemplate,
            response = function(r) {
                if ('errors' in r) {
                    $parent.before(utf8_decode(r.errors));
                } else if (action == 'Delete' && r.success) {
                    $parent.fadeOut(function() { $(this).remove() });
                    updateProductVariablePosition();
                }
            };
        $parent.prev('.thrown-errors').remove();
        ajaxRequest(params, response);
    });

    $('.af').on('click', '.scrollUp', function() {
        scrollUp($(this).closest('.af_template'));
    });

    function prependErrors($parent, errorsHTML) {
        $parent.prepend(errorsHTML);
        $('html, body').animate({ scrollTop: $parent.offset().top - 150 }, 300);
    }

    $('.template-list').on('click', '.template-tab-option', function(e) {
        e.preventDefault();
        $(this).addClass('active').siblings('.template-tab-option').removeClass('active');
        $('.template-tab-content' + $(this).attr('href')).addClass('active').siblings('.template-tab-content').removeClass('active');
    }).on('click', '.saveTemplate', function() {
        $('.thrown-errors').remove();
        var $parent = $(this).closest('.af_template'),
            $form = $(this).closest('.template-form');

        id_product = $('.af').data('id_product');
        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&action=SaveVariable&id_product=' + id_product,
            data: $form.serialize(),
            dataType: 'json',
            success: function(r) {
                if ('errors' in r) {
                    prependErrors($parent, utf8_decode(r.errors));
                } else {
                    var callBack = function() {
                        var templateName = $('<div>' + $parent.find('input[name="variable_name"]').val() + '</div>').text(), // extract only text
                            controllerNew = $parent.find('select[name="template_controller"]').val(),
                            controllerPrev = $parent.data('controller');
                        $parent.find('.template-name').find('h4').html(templateName);
                        if (controllerNew != controllerPrev) {
                            moveTemplate($parent, controllerNew); // may be used in next versions
                        }
                    };
                    scrollUp($parent, callBack);
                    $.growl.notice({ title: '', message: savedTxt });
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });
    });

    function scrollUp($template, callBack) {
        callBack = callBack || function() {};
        $template.find('.template_settings').slideUp(function() {
            $template.removeClass('open');
            $(this).html('');
            callBack();
        });
    }

    function moveTemplate($template, controller) { // may be used in next versions
        var $newContainer = $('.template-list.' + controller);
        if ($newContainer.hasClass('hidden')) {
            $template.slideUp(500, function() {
                $template.prependTo($newContainer);
            });
        } else {
            var placeholderHTML = '<div class="template-placeholder"></div>',
                id = $template.data('id');
            $newContainer.find('.af_template').each(function(i) {
                if ($(this).data('id') < id) { // templates are sorted by ID DESC
                    $(this).before(placeholderHTML);
                    placeholderHTML = '';
                }
            });
            if (placeholderHTML) {
                $newContainer.prepend(placeholderHTML);
            }
        }
    }

    $('.af').on('click', '.saveAvailableCustomerFilters', function() {
        var data = $(this).closest('form').serialize();
        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&action=SaveAvailableCustomerFilters',
            data: data,
            dataType: 'json',
            success: function(r) {
                console.dir(r);
                if (r.success) {
                    $.growl.notice({ title: '', message: savedTxt });
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });
    });

    $(document).on('click', '.activateTemplate', function(e) {
        e.preventDefault();
        $('.thrown-errors').remove();
        var id_product_variable = $(this).closest('.af_template').attr('data-id'),
            active = $(this).hasClass('action-enabled') ? 0 : 1,
            $button = $(this);
        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&action=ToggleActiveStatus',
            dataType: 'json',
            data: {
                id_product_variable: id_product_variable,
                active: active,
            },
            success: function(r) {
                if ('errors' in r) {
                    $button.closest('.af_template').before(utf8_decode(r.errors));
                } else if (r.success) {
                    $button.toggleClass('action-enabled action-disabled');
                    $button.find('input[name="active"]').val(active)
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });
    });

    // ----- multiple options
    var parentSelector = '.basic-item',
        blockUpdateSelectedOptionsTxt = false;
    $('.template-list, .merged-list').on('click', '.selected-options-inline, .hideOptions', function(e) {
        var $parent = $(this).closest(parentSelector);
        $parent.find('.available-options').toggleClass('hidden');
        $parent.find('.toggleIndicator').toggleClass('icon-rotate-180');
    }).on('click', '.toggleChildren', function(e) {
        e.preventDefault();
        var $opt = $(this).closest('.opt');
        $opt.toggleClass('closed');
        markCheckedChildren($opt);
    }).on('click', '.checkChildren', function() {
        var $checkboxes = $(this).siblings('.opt-level').find('.opt-checkbox'),
            uncheck = $checkboxes.filter(':checked').length;
        $checkboxes.prop('checked', !uncheck).change();
        $('.opt.closed').each(function() {
            markCheckedChildren($(this));
        });
    }).on('change', '.opt-checkbox', function() {
        $(this).closest('label').toggleClass('checked', $(this).prop('checked'));
        updateSelectedOptionsTxt($(this).closest(parentSelector));
    }).on('click', '.opt-action', function(e) {
        var $group = $(this).closest(parentSelector),
            action = $(this).data('bulk-action'),
            toggleOtherOption = $(this).data('toggle');
        switch (action) {
            case 'open':
            case 'close':
                var selector = action == 'open' ? '.opt.closed' : '.opt:not(.closed)';
                $group.find(selector).children('.opt-label').children('.toggleChildren').click();
                break;
            case 'check':
            case 'uncheck':
            case 'invert':
                blockUpdateSelectedOptionsTxt = true;
                $group.find('.opt-checkbox').each(function() {
                    var checked = action == 'check' ? true : action == 'uncheck' ? false : !$(this).prop('checked');
                    $(this).prop('checked', checked).change();
                });
                $('.opt.closed').each(function() {
                    markCheckedChildren($(this));
                });
                blockUpdateSelectedOptionsTxt = false;
                updateSelectedOptionsTxt($group);
                break;
        }
        if (toggleOtherOption) {
            $(this).addClass('hidden');
            $(this).siblings('.opt-action[data-bulk-action="' + toggleOtherOption + '"]').removeClass('hidden');
        }
    }).on('change', '.toggleIDs', function() {
        $(this).closest(parentSelector).find('.opt-id').toggleClass('hidden', !$(this).prop('checked'));
    });

    function updateSelectedOptionsTxt($group) {
        if (blockUpdateSelectedOptionsTxt) {
            return;
        }
        var $checked = $group.find('.opt-checkbox:checked'),
            total = $checked.length,
            displayedNum = 7,
            extra = '';
        if ($group.find('.dynamic-name').length) {
            selectedTxt = [];
            $checked.each(function() {
                if (selectedTxt.length < displayedNum) {
                    selectedTxt.push($(this).closest('.opt-label').find('.opt-name').text());
                } else {
                    extra = ', ... + ' + (total - displayedNum);
                    return false;
                }
            });
            selectedTxt = selectedTxt.join(', ') + extra;
            $group.find('.item-names').html(selectedTxt);
            $group.find('.total-num').html(total);
        }
        $group.toggleClass('has-selection', !!total);
    }

    function markCheckedChildren($opt) {
        var childrenChecked = $opt.find('.opt-level').find('.opt-checkbox:checked').length,
            showNum = childrenChecked && $opt.hasClass('closed');
        $opt.children('.checked-num').toggleClass('hidden', !showNum).find('.dynamic-num').html(childrenChecked);
    }
    // ----- /multiple options

    $('.toggle-cron').on('click', function() {
        $(this).closest('.shop-indexation-data').toggleClass('show-cron')
            .closest('.grid-item').siblings().find('.shop-indexation-data').removeClass('show-cron');
    });

    $('.close-cron').on('click', function() {
        $(this).closest('.shop-indexation-data').removeClass('show-cron');
    });

    var relatedOptions = {
        init: function() {
            $('.has-related-options').not('.ready').each(function() {
                var $related = $(this).closest('form').find($(this).data('related'));
                if ($related.length) {
                    $(this).find('input[type="text"]').on('keyup', function() {
                        relatedOptions.toggle($related, parseInt($(this).val()));
                    }).keyup();
                    $(this).find('input:not([type="text"]), select').on('change', function() {
                        relatedOptions.toggle($related, $(this).val());
                    }).filter('input:checked, select').change();
                }
            }).addClass('ready');
        },
        toggle: function($els, value) {
            $els.filter('[class*=" hidden-on-"]').removeClass('hidden').filter('.hidden-on-' + value).addClass('hidden');
            $els.filter('[class*=" visible-on-"]').addClass('hidden').filter('.visible-on-' + value).removeClass('hidden');
        }
    }
    relatedOptions.init();

    $.each(['attribute', 'feature'], function(k, type) {
        $('.settings-item.merged' + type + 's').on('change', 'input', function() {
            $('#general-settings').find('.saveMultipleSettings').click();
            toggleMergedParams(type);
        });
    });

    function toggleMergedParams(type) {
        var $tab = $('.list-group-item[href="#merged-' + type + 's"]');
        if ($('#merged' + type + 's').is(':checked')) {
            $('html, body').animate({ scrollTop: $tab.offset().top - 250 }, 20);
            $tab.removeClass('hidden').addClass('active flashing');
            setTimeout(function() { $tab.removeClass('active flashing'); }, 500);
        } else {
            $tab.addClass('hidden').removeClass('active flashing');
        }
    }

    // caching
    var cachingSettings = {
        updateInfo: function() {
            var params = { action: 'getCachingInfo' },
                response = function(r) {
                    $.each(r.info, function(name, text) {
                        var $note = $('.form-group.' + name).find('.field-note');
                        if (!$note.length) {
                            $note = $('<span class="field-note"></span>').appendTo('.form-group.' + name);
                        }
                        $note.html(utf8_decode(text));
                    });
                };
            ajaxRequest(params, response);
        },
        toggleCombinationsAvailability: function() {
            var hide = !$('#combinationsexistence:checked, #combinationsstock:checked').length;
            $('#caching-settings').find('.form-group.comb_data').toggleClass('hidden', hide);
        },
        clear: function() {
            ajaxRequest({ action: 'clearCache' }, cachingSettings.updateInfo);
        },
        init: function() {
            cachingSettings.toggleCombinationsAvailability();
            cachingSettings.updateInfo();
            $('.clearCache').on('click', function() { cachingSettings.clear(); });
            if ($('#caching-settings').find('input[type="radio"][value="1"]:checked').length) {
                setInterval(function() { cachingSettings.updateInfo(); }, 60000);
            }
        },
    };
    cachingSettings.init();

    //sort templates
    var templateSorting = {
        default: { by: 'date_add', way: 'desc' }, // date_add represents sorting by ID
        getParamName: function(controllerType) {
            return 'sort_' + controllerType + '_templates';
        },
        init: function() {

            $('.ts-current-option').on('click', function() {
                $(this).parent().toggleClass('show-options');
            });
            $('.ts-way').on('click', function() {
                $(this).children().toggleClass('hidden current');
                templateSorting.apply($(this));
            });
            $('.ts-by').on('click', function() {
                var txt = $(this).text();
                $(this).siblings().removeClass('current');
                $(this).addClass('current').closest('.template-sorting').removeClass('show-options').
                find('.ts-current-option').text(txt);
                templateSorting.apply($(this));
            });
            templateSorting.applyFromURL();
        },
        apply: function($el) {
            var $group = $el.closest('.template-group'),
                $templates = $group.find('.af_template'),
                order = {
                    by: $group.find('.ts-by.current').data('by'),
                    way: $group.find('.ts-way').find('.current').data('way'),
                },
                asc = order.way == 'asc';
            if (order.by == 'name') {
                $templates.sort(function(a, b) {
                    var a = $(a).find('input[name="variable_name"]').val().toUpperCase(),
                        b = $(b).find('input[name="variable_name"]').val().toUpperCase();
                    return asc ? a.localeCompare(b) : b.localeCompare(a);
                });
            } else {
                $templates.sort(function(a, b) {
                    return asc ? $(a).data('id') - $(b).data('id') : $(b).data('id') - $(a).data('id');
                });
            }
            $group.find('.template-list').prepend($templates);
            templateSorting.updateURL($group.find('.addTemplate').data('controller'), order);
        },
        updateURL: function(controllerType, order) {
            if (window.location.search) {
                var sortingParam = templateSorting.getParamName(controllerType),
                    params = JSON.parse('{"' + decodeURI(window.location.search)
                        .replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"') + '"}');
                delete params[sortingParam];
                if (order.way != templateSorting.default.way || order.by != templateSorting.default.by) {
                    params[sortingParam] = order.by + ':' + order.way;
                }
                var newURL = window.location.href.split('?')[0] + decodeURIComponent($.param(params, true));
                if (newURL != window.location.href) {
                    window.history.pushState(null, null, newURL);
                }
            }
        },
        applyFromURL: function() {
            $('.template-sorting').each(function() {
                var $group = $(this).closest('.template-group'),
                    controllerType = $group.find('.addTemplate').data('controller'),
                    forcedSorting = getUrlParam(templateSorting.getParamName(controllerType)).split(':');
                if (forcedSorting.length == 2) {
                    $(this).find('.ts-way').find('[data-way="' + forcedSorting[1] + '"]')
                        .addClass('current').removeClass('hidden').siblings().addClass('hidden').removeClass('current');
                    $(this).find('.ts-by[data-by="' + forcedSorting[0] + '"]').click();
                }
                $group.removeClass('not-ready');
            });
        },
    };
    templateSorting.init();

    function ajaxRequest(params, response, errorResponse) {
        errorResponse = typeof errorResponse == 'undefined' ? function(r) {} : errorResponse;
        $.ajax({
            type: 'POST',
            url: ajax_action_path,
            data: params,
            dataType: 'json',
            success: function(r) {
                response(r);
                if ('notice' in r) {
                    $.growl.notice({ title: '', message: utf8_decode(r.notice) });
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
                errorResponse(r);
            }
        });
    }

    var forcedTab = getUrlParam('tab');
    if (forcedTab) {
        $('.list-group-item[href="#' + forcedTab).click();
    }

    function getUrlParam(name) {
        return (location.search.split(name + '=')[1] || '').split('&')[0];
    }
});

function utf8_decode(utfstr) {
    var res = '';
    for (var i = 0; i < utfstr.length;) {
        var c = utfstr.charCodeAt(i);
        if (c < 128) {
            res += String.fromCharCode(c);
            i++;
        } else if ((c > 191) && (c < 224)) {
            var c1 = utfstr.charCodeAt(i + 1);
            res += String.fromCharCode(((c & 31) << 6) | (c1 & 63));
            i += 2;
        } else {
            var c1 = utfstr.charCodeAt(i + 1);
            var c2 = utfstr.charCodeAt(i + 2);
            res += String.fromCharCode(((c & 15) << 12) | ((c1 & 63) << 6) | (c2 & 63));
            i += 3;
        }
    }
    return res;
}
var cloneCount = 2;
var cloneVariable = 2;
var cloneVariableName = 2;
var cloneDisallowVariable = 2;
var cloneSign = 2;
var cloneOption = 2;
var cloneCondition = 2;
var variableCount = 1;
$(document).on('change', 'select.and_or_sign', function() {
    var that = $(this);
    //0 is for none
    //1 is for OR
    //2 is for AND
    if (that.val() != 0) {
        // $(".main_rule_div .clone").first().clone().addClass('cloneChild').appendTo(".main_rule_div");
        $('#clone')
            .clone()
            .attr('id', 'clone-' + cloneCount++)
            .find('#variables1').attr('id', 'variables' + cloneVariable++).parent().parent()
            .insertAfter($('[id^=clone]:last'));
        //alert(cloneCount);
        $('#clone-' + (cloneCount - 1)).find('.variables').attr('name', 'variables' + cloneVariableName++);
        $('#clone-' + (cloneCount - 1)).find('.sign').attr('name', 'sign' + cloneSign++);
        $('#clone-' + (cloneCount - 1)).find('.options').attr('class', 'form-control options options' + (cloneOption));
        $('#clone-' + (cloneCount - 1)).find('.options').attr('name', 'options' + (cloneOption++) + '[]');
        $('#clone-' + (cloneCount - 1)).find('.and_or_sign').attr('name', 'and_or_sign' + cloneCondition++);
        variableCount++;
    } else {
        that.parents('#rules_form')
            .find('.and_or_sign')
            .each(function() {
                if ($(this).val() === that.val()) {
                    that.parents('div.clone_div').nextAll('div.clone_div').remove();
                }
            });
        variableCount--;
    }
});

$(document).on('click', '.disallow_variables button.add_more', function(e) {
    e.preventDefault();
    $(this).parents('.disallow_variables:first')
        .clone().attr('id', 'disallow_variables-' + cloneDisallowVariable).removeClass('clone')
        .find('.add_more').addClass('remove_disallow_variable').removeClass('add_more')
        .find('.fa-plus').addClass('fa-minus').removeClass('fa-plus').parents('.disallow_variables')
        .find('.multiselect-main-div').html('<select class="form-control disallow_option"><option value="">Please select variable first</option></select><span class="error_msg_for_disallow_option"></span>').parents('.disallow_variables')
        .find('.disallow_multiselect select.disallow').attr('name', 'disallow' + cloneDisallowVariable + '[]')
        .parents('.disallow_variables')
        .insertAfter($('.disallow_group .disallow_variables:last'));

    cloneDisallowVariable++;

});

$(document).on('click', '.disallow_variables button.remove_disallow_variable', function(e) {
    e.preventDefault();
    $(this).parents('.disallow_variables').remove();
    cloneDisallowVariable--;
});

function getRulesList() {
    var id_product = $('.af').data('id_product');
    $.ajax({
        type: 'POST',
        url: ajax_action_path + '&action=AjaxDatatableRulesList&id_product=' + id_product,
        dataType: 'html',
        success: function(resp) {
            $('#rules_list tbody').replaceWith(resp)
        },
        error: function(r) {
            console.warn($(r.responseText).text() || r.responseText);
        }
    });
}

$(document).ready(function() {
    // $('[id^=variables]').on('change',function(){
    $(document).on("change", "[id^=variables]", function(e) {
        var that = $(this);
        $.each($(this).parents('.clone_div').find('input[type=number]'), function() {
            $(this).val('');
        });
        for (let i = 1; i <= variableCount; i++) {
            $.each($(this).parents('.clone_div').find('.options' + i), function() {
                $(this).find('option[value="0"]').prop('selected', true);
                if (that.val() == $(this).data('id_variable')) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });

    $(document).on("change", "select.disallow", function(e) {
        var value = $(this).val()
        var count = $(this).parents('.disallow_variables').attr('id').split('-')[1];
        var that = $(this);
        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&action=getOptionsBasedOnVariableId&id_variable=' + value + '&count=' + count,
            dataType: 'json',
            // dataType: 'html',
            success: function(r) {
                $(that).parents('.disallow_variables').find('div.multiselect-main-div').html(r.html + "<span class='error_msg_for_disallow_option'></span>")
                if (r.type == 'select') {
                    $('div.multiselect-main-div select').multiselect({
                        includeSelectAllOption: true,
                        nonSelectedText: 'Select an Option'
                    });
                }
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });
    });

    $(document).on('change', '#variable_id', function() {
        var variable_id = $(this).val();
        console.log(variable_id);
        update_option_select_box(variable_id);
    });

    if ($("#id_alert_messages").length > 0) {
        $('input[name="submitAddalert_messages"]').attr('name', 'submitEditalert_messages');
    }
});
// $('input#save_as_txt').on('click', function(e) {
$(document).on('click', '#saveRule', function(e) {
    var data = $('form#rules_form').serializeArray();

    if ($('#ruleName').val() == '') {
        $('.error_msg_for_name').html("<p class='text-danger'>Please fill this field</p>")
        $('#ruleName').css('border', '1px solid red');
    } else {
        $('.error_msg_for_name').html("");
        $('#ruleName').css('border', '1px solid #bbcdd2');
    }
    var count = 1;
    var flag = [];
    for (let i = 1; i <= variableCount; i++) {
        $('.variables').each(function() {
            if ($('#variables' + i).val() == '0') {
                $('#variables' + i).parent().find('.error_msg_for_variable').html("<p class='text-danger'>Please select variable</p>")
                $('#variables' + i).css('border', '1px solid red');
                flag.push(1);
            } else {
                flag.push(0);
                $('#variables' + i).parent().find('.error_msg_for_variable').html("")
                $('#variables' + i).css('border', '1px solid #bbcdd2');
            }
        });
        $('.sign').each(function() {
            if ($('[name=sign' + i + ']').val() == '0') {
                // if($('#sign'+i).val() == '0'){
                $('[name=sign' + i + ']').parent().find('.error_msg_for_sign').html("<p class='text-danger'>Please select sign</p>")
                $('[name=sign' + i + ']').css('border', '1px solid red');
                // $('#sign'+i).css('border','1px solid red');
                flag.push(1);
            } else {
                flag.push(0);
                $('[name=sign' + i + ']').css('border', '1px solid #bbcdd2');
                $('[name=sign' + i + ']').parent().find('.error_msg_for_sign').html("")
            }
        });
        $('.options' + i).each(function() {
            if ($('.options' + i + ':visible').val() == '0') {
                $('.options' + i).parent().find('.error_msg_for_constraint').html("<p class='text-danger'>Please select option</p>")
                $('.options' + i).css('border', '1px solid red');
                flag.push(1);
            } else {
                flag.push(0);
                $('.options' + i).css('border', '1px solid #bbcdd2');
                $('.options' + i).parent().find('.error_msg_for_constraint').html("")
            }
        });
    }

    // if($.isEmptyObject($('.disallow').val())){
    //     $('.error_msg_for_disallow').html("<p class='text-danger'>Please select one or more variables</p>")
    //     $('.disallow').css('border','1px solid red');
    //     flag.push(1);
    // }else{
    //     $('.error_msg_for_disallow').html("")
    //     $('.disallow').css('border','1px solid #bbcdd2');
    //     flag.push(0);
    // }

    $('.disallow_group select.disallow').each(function() {
        var value = $(this).val();
        if (value == "") {
            $(this).parent().find('.error_msg_for_disallow_variable').html("<p class='text-danger'>Please select disallow variable</p>")
            $(this).css('border', '1px solid red');
            flag.push(1);
        } else {
            flag.push(0);
            $(this).css('border', '1px solid #bbcdd2');
            $(this).parent().find('.error_msg_for_disallow_variable').html("")
        }
    });
    $('.disallow_group select.disallow_option').each(function() {
        var value = $(this).val();
        console.log(value, value.length)
        if (value == null || value.length == 0) {
            $(this).parent().find('.error_msg_for_disallow_option').html("<p class='text-danger'>Please select disallow option</p>")
            $(this).css('border', '1px solid red');
            flag.push(1);
        } else {
            flag.push(0);
            $(this).css('border', '1px solid #bbcdd2');
            $(this).parent().find('.error_msg_for_disallow_option').html("")
        }
    });

    id_product = $('.af').data('id_product');


    if ($.inArray(1, flag) !== -1) {
        return false;
    } else {
        $.ajax({
            type: 'POST',
            url: ajax_action_path + '&action=SaveBanCombSettings&id_product=' + id_product + '&count=' + cloneCount + '&cloneOptionCount=' + cloneDisallowVariable,
            data: $('form#rules_form').serialize(),
            dataType: 'json',
            success: function(r) {
                if ('errors' in r) {
                    //prependErrors($parent, utf8_decode(r.errors));
                } else {
                    $.growl.notice({ title: '', message: savedTxt });
                }
                swal(
                    'Done!',
                    'Rule has been added.',
                    'success'
                ).then(function(result) {
                    $('.add-rules-modal').modal('hide');
                    $('#rules_list').DataTable().ajax.reload();
                    // getRulesList();
                });
            },
            error: function(r) {
                console.warn($(r.responseText).text() || r.responseText);
            }
        });
    }
});
$(document).on('click', '.delete-rule', function(e) {
    // $('.delete-rule').click(function(){
    e.preventDefault();
    id_rule = $(this).data('id_rule');

    swal({
        title: 'Are you sure?',
        text: "It will permanently deleted !",
        icon: 'warning',
        dangerMode: true,
        buttons: true,
    }).then(function(result) {
        if (result) {
            $.ajax({
                type: 'POST',
                url: ajax_action_path + '&action=DeleteRule',
                data: {
                    id_rule: id_rule
                },
                dataType: 'json',
                success: function(r) {
                    if ('errors' in r) {
                        //prependErrors($parent, utf8_decode(r.errors));
                    } else {
                        $.growl.notice({ title: '', message: savedTxt });
                    }
                    // location.reload();
                    $('#rules_list').DataTable().ajax.reload();
                    // getRulesList();
                },
                error: function(r) {
                    console.warn($(r.responseText).text() || r.responseText);
                }
            });
            swal(
                'Deleted!',
                'Rule has been deleted.',
                'success'
            );
        }
    });


});
$(document).on('click', '#resetRule', function(e) {
    $('[id^=clone]').not(':first').remove();
});

function update_option_select_box(variable_id) {
    var fd = new FormData();
    fd.append('action', 'getOptions');
    fd.append('variable_id', variable_id);
    var id_alert_message = "";
    if ($("#id_alert_messages").length > 0) {
        id_alert_message = $("#id_alert_messages").val();
        fd.append('id_alert_messages', id_alert_message);
    }
    $.ajax({
        type: "POST",
        url: alertAjaxUrl,
        data: fd,
        dataType: 'html',
        processData: false,
        contentType: false,
        success: function(response) {
            $('#option_id').replaceWith(response);
        },
        error: function(error) {
            console.log("Error:", error);
        }
    });
}