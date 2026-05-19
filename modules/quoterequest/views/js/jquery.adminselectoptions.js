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

cp = {
	ajax : function() {
		this.init = function(options) {
			this.options = $.extend(this.options, options);
			this.request();
			return this;
		};
		this.options = {
			type     : 'POST',
			url      : theme_url + '&ajax=1',
			headers  : {"cache-control": "no-cache"},
			cache    : false,
			dataType : "json",
			async		 : false,
			success  : function() {}
		};
		this.request = function() {
			$.ajax(this.options);
		};
	},
	list: function(){
		this.init = function(json) {
			if (json == '') {
				json = '[]';
			}
			this.array = JSON.parse(json);
		};
		this.extend = function(json) {
			var options = JSON.parse(json);
			for (var i=0;i<options.length;i++) {
				this.array[this.array.length] = options[i];
			}

			return JSON.stringify(this.array);
		};
		this.add = function(elem){
			if (this.array.indexOf(elem) == -1) {
				this.array[this.array.length] = elem;
			}
			return JSON.stringify(this.array);
		};
		this.remove = function(elem){
			var index = this.array.indexOf(elem);
			this.array.splice(index, 1);
			return JSON.stringify(this.array);
		}
	},
	fancy: function(){
		this.init = function(options){
			this.options = $.extend(this.options, options);
			return this;
		};
		this.options = {
			type: 'inline',
			autoScale: true,
			minHeight: 30,
			minWidth: 285,
			padding: 0,
			content: '',
			showCloseButton: true,
			helpers: {
				overlay: {
					locked: false
				}
			}
		};
		this.show = function() {
			$.fancybox(this.options);
		};
	}
};
$(document).ready(function() {

	$('.fancybox-inner .close').live('click', function(){
		$.fancybox.close();
	});
	$('#manage-options').on('click', function(e){
		e.preventDefault();
		var options = {
			success: function(response) {
				var fancy_options = {
					content: response.content
				};
				var fancybox = new cp.fancy();
				fancybox.init(fancy_options).show();
			},
			data: {
				search: $('[name=search_name]').attr('value'),
				selected_options: $('input[name=selected_options]').attr('value'),
				method: 'getAjaxOptions',
				token : $.getUrlVar('token'),
			}
		};
		var ajax = new cp.ajax();
		ajax.init(options);

	});



	$('.fancybox-inner .option').live('click', function(){
		var options = new cp.list();
		options.init($(this).parents('.bootstrap').find('input[name=options]').attr('value'));
		var option_id = $(this).attr('data-option-id');
		if ($(this).hasClass('active')) {
			$(this).parents('.bootstrap').find('input[name=options]').attr('value', options.remove(option_id));
		} else {
			$(this).parents('.bootstrap').find('input[name=options]').attr('value', options.add(option_id));
		}
		$(this).toggleClass('active');
	});

	$('#select_all_options').live('click', function(e){
		e.preventDefault();
		$('.fancybox-inner .option:not(.active)').trigger('click');
	});

	$('#deselect_all_options').live('click', function(e){
		e.preventDefault();
		$('.fancybox-inner .option.active').trigger('click');
	});

	$('#add_options').live('click', function(e){
		e.preventDefault();
		$.fancybox.close();
		var options = new cp.list();
		options.init($('input[name=selected_options]').attr('value'));
		var new_options = $(this).parents('.bootstrap').find('input[name=options]').attr('value');
		$('input[name=selected_options]').attr('value', options.extend(new_options));
		$('.fancybox-inner .option.active').show().appendTo('#selected_options');
	});

	function removeProductFromList(child) {
		var options = new cp.list();
		var input = $('input[name=selected_options]');
		options.init(input.attr('value'));
		var elem = child.parents('.option');
		input.attr('value', options.remove(elem.attr('data-option-id')));

		elem.remove();
	}
	$('#selected_options .remove-option').live('click', function(e){
		e.preventDefault();
		removeProductFromList($(this));
	});

	$('#selected_options .remove-option').on('click', function(e){
		e.preventDefault();
		removeProductFromList($(this));
	});

	$('.categoryoptions_tabs > tbody tr, .categoryoptions_blocks > tbody tr').each(function(){
		var id = $(this).find('td:first').text();
		$(this).attr('id', 'item_'+id.trim());
	});
	$('.categoryoptions_tabs > tbody, .categoryoptions_blocks > tbody').sortable().bind('sortupdate', function() {
		var orders = $(this).sortable('toArray');
		console.log(orders);
		var options = {
			data: {
				action: 'updateposition',
				item: orders,
			},
			success: function(msg)
			{
				if (msg.error)
				{
					showErrorMessage(msg.error);
					return;
				}
				showSuccessMessage(msg.success);
			}
		};
		var ajax = new cp.ajax();
		ajax.init(options);
	});

	$( "#selected_options" ).sortable({
		cursor: 'move',
		update: function(event, ui) {
			var options = new cp.list();
			options.init('[]');
			$(this).find('.option').each(function() {
				options.add($(this).attr('data-option-id'));
			});
			$('input[name=selected_options]').attr('value', JSON.stringify(options.array));
		}
	});
	$( "#selected_options" ).disableSelection();

	$('.fancybox-inner input[name=option_search]').live('keyup', function(){
		var find_text = $('.fancybox-inner input[name=option_search]').attr('value').toLowerCase();
		$('.fancybox-inner .option').hide();
		$('.fancybox-inner .option p').each(function(){
			var text = $(this).text().toLowerCase();
			if(text.indexOf(find_text) + 1) {
				$(this).parents('.option').show();
			}
		});
	});
	$('.clear_serach').live('click', function(e){
		e.preventDefault();
		$('.fancybox-inner input[name=option_search]').attr('value', '').trigger('keyup');
	});
});
