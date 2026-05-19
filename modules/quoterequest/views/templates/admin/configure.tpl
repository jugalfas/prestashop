{*
* 2007-2020 Amazzing
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright 2007-2020 Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
	integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
	crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
{* <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/5.0.7/sweetalert2.min.js"></script> *}
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<link rel="stylesheet"
	href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">

{function renderElement type='saveMultipleSettingsBtn' cls=''}
	{if $type == 'saveMultipleSettingsBtn'}
		<div class="panel-footer">
			<button type="button" class="saveMultipleSettings btn btn-default{if $cls} {$cls|escape:'html':'UTF-8'}{/if}">
				<i class="process-icon-save"></i> {l s='Save' mod='quoterequest'}
			</button>
		</div>
	{else if $type == 'resetBtn'}
		<a href="#" class="resetSelectors"><i class="icon-undo"></i> {l s='Reset' mod='quoterequest'}</a>
	{/if}
{/function}


<!-- {$quote_html} -->


<div class="order_data">

	<div class="row">
		<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 order-panel">
			<h2>Quote #{$id_quote} details</h2>
			<p>Created at {$quote->date_add}</p>
		</div>
		<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12order-panel">
			<p><strong>Title:</strong> {$quote_title}</p>
		</div>
		<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12 order-panel">
			<p><strong>Customer:</strong> {$quote->customer->firstname|escape:'html'}
				{$quote->customer->lastname|escape:'html'} <a href="{$quote->customer->profile_link|escape:'html'}">
					Profile →</a></p>
		</div>
		<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12 order-panel">
			<p><strong>Email :</strong> {$quote->email}</p>
		</div>
		<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12 order-panel">
			<p><strong>Current Status:</strong>
				<mark class="order-status status-on-hold tips"><span>{$status[$quote->current_status]} </span></mark>
			</p>
		</div>
		<div class="col-lg-1 col-md-1 col-sm-12 col-xs-12 order-panel">
			<a href="#" class="btn btn-outline-danger btn-sm" data-toggle="modal"
				data-target="#convert_to_order_popup">Convert to order</a>
		</div>
	</div>
</div>


<div class="bootstrap af quoterequest clearfix" data-id_quote="{$id_quote}">
	<div class="menu-panel col-lg-2 col-md-3">
		<div class="list-group">
			<a href="#filter-templates" class="list-group-item active"><i class="icon-filter"></i>
				{l s='Quote Products' mod='quoterequest'}</a>
			<a href="#hook-settings" class="list-group-item"><i class="icon-cogs"></i>
				{l s='Quote setting' mod='quoterequest'}</a>

		</div>
	</div>
	<div class="panel tab-content col-lg-10 col-md-9">
		<div id="filter-templates" class="tab-pane active">
			<div class="template-group">
				<h3>
					{if isset($quote_title)}
						{$quote_title = Tools::strtolower($quote_title)}
					{/if}
					{l s='Products for %s' mod='quoterequest' sprintf=[$quote_title]}
					<a href="{$back}" class="pull-right">
						<i class="icon-back"></i> {l s='Back' mod='quoterequest'}
					</a>
				</h3>
				<div class="template-list category sortable">
					{foreach $quote_products as $t}
						{include file="./product-form.tpl"}
					{/foreach}
				</div>
				<a href="#" class="add_new_product hide" data-toggle="modal" data-target="#add_new_product">
					<i class="icon-plus"></i> Add product </a>
			</div>

		</div>
		<div id="hook-settings" class="tab-pane">
			<h3>{l s='Quote settings' mod='quoterequest'}</h3>
			<div class="ajax-warning alert alert-warning hidden"></div>
			<form method="post" action="" class="settigns_form form-horizontal clearfix">

				<div class="settings-item">
					<label class="settings-label">
						<span>
							{l s='Title' mod='quoterequest'}
						</span>
					</label>
					<div class="settings-input">
						<input type="text" name="title" value="{$quote->title}" class="">
					</div>
				</div>
				<div class="settings-item">
					<label class="settings-label">
						<span>
							{l s='Email' mod='quoterequest'}
						</span>
					</label>
					<div class="settings-input">
						<input type="text" name="email" value="{$quote->email}" class="">
					</div>
				</div>
				<div class="settings-item">
					<label class="settings-label">
						<span>
							{l s='Change Staus' mod='quoterequest'}
						</span>
					</label>
					<div class="settings-input">
						<select class="" name="current_status">
							<option value="">Select</option>
							{foreach $status as $i => $opt}
								<option value="{$i|escape:'html':'UTF-8'}" {if $quote->current_status == $i} selected {/if}
									{if $quote->current_status == 0 && ($i == 1 || $i == 4)} disabled
									{elseif $quote->current_status == 1 && ($i == 0)} disabled 
									{/if} data-title="{$opt|escape:'html':'UTF-8'}">
									{$opt|escape:'html':'UTF-8'}</option>

							{/foreach}
						</select>
					</div>
				</div>

				<div class="settings-item">
					<label class="settings-label">
						<span>

							{l s='Allow partial' mod='quoterequest'}
						</span>
					</label>
					<div class="settings-input">
						<span class="switch prestashop-switch partial">
							<input type="radio" id="allow_partial" name="allow_partial" value="1"
								{if !empty($quote->allow_partial)} checked {/if}>
							<label for="allow_partial">
								{l s='Yes' mod='quoterequest'}</label>
							<input type="radio" id="allow_partial_0" name="allow_partial" value="0"
								{if empty($quote->allow_partial)} checked {/if}>
							<label for="allow_partial_0">
								{l s='No' mod='quoterequest'}</label>
							<a class="slide-button btn"></a>
						</span>
					</div>
				</div>

				<div class="settings-item">
					<label class="settings-label">
						<span>

							{l s='Note' mod='quoterequest'}
						</span>
					</label>
					<div class="settings-input">
						<input type="text" name="note" value="{$quote->note}" class="">
					</div>
				</div>

			</form>
			<div class="clearfix"></div>
			<div class="panel-footer">
				<button type="button" class="saveQuoteSettings btn btn-default">
					<i class="process-icon-save"></i>
					{l s='Save' mod='quoterequest'}
				</button>
			</div>
		</div>
		<div id="general-settings" class="tab-pane">
			<form method="post" action="" class="settigns_form form-horizontal clearfix" data-type="general">
				<h3>
					{l s='Tired Price' mod='quoterequest'}</h3>
				<div class="clearfix">
					{$tiered_price}
				</div>
			</form>

			{renderElement type='saveMultipleSettingsBtn'}
		</div>

		<div id="price_for_odd_quantities" class="tab-pane">
			<h3>
				{l s='Percentage for odd quantity' mod='quoterequest'}</h3>

			<div class="alert alert-info">

				{l s='Here you can set percentage for odd quantity' mod='quoterequest'}
			</div>
			<div class="ajax-warning alert alert-warning hidden"></div>
			<form method="post" action="" class="percentage_for_odd_quantity_Form form-horizontal clearfix">

				<div class="settings-item">
					<label class="settings-label">
						<span>

							{l s='Percentage (%)' mod='quoterequest'}
						</span>
					</label>
					<div class="settings-input">
						<input type="number" name="percentage_for_odd_quantity" value="{$percentage_for_odd_quantity}"
							class="form-control">
					</div>
				</div>
			</form>
			<div class="clearfix"></div>
			<div class="panel-footer">
				<button type="button" class="savePercentageSettings btn btn-default">
					<i class="process-icon-save"></i>
					{l s='Save' mod='quoterequest'}
				</button>
			</div>
		</div>

	</div>
</div>
<div class="alert alert-warning reindex-reminder orig hidden">

	{l s='Don\'t forget to re-index all products' mod='quoterequest'}
	<button type="button" class="close close-reminder">&times;</button>
</div>
<div class="modal fade" id="add_new_product" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title"></h3>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<label for="products" class="form-label">Products</label>
								<select name="products" id="products" class="form-select ac_input" autocomplete="off">
									<option value="">Select Product</option>
									{foreach $productsByCategory as $category => $product}
										<optgroup label="{$category}">
											{foreach $product as $prod}
												<option value="{$prod['id_product']}">{$prod['product_name']}</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
							</div>
							<div class="row product_details" id="product_details" style="display: none;">
								<div class="col-md-4">
									<img src="https://dev.onlyprint.nl/192-home_default/flyers.jpg" id="product_image"
										class="w-100">
								</div>
								<div class="col-md-8">
									<label id="product_name"></label>
									<div class="review-area">
										<div class="star-area">
											<img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/star.svg">
											<img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/star.svg">
											<img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/star.svg">
											<img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/star.svg">
											<img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/star.svg">
										</div>
										<span id="ratings">0</span> (<span id="rating_count">0</span>)
									</div>
									<p class="description-text" id="product_description"></p>
									<a type="button" href="#add-another-product-top"
										class="table-commentbtn load_configuration" data-id_product="43">Load
										configuration <i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
								</div>
							</div>
							<div class="animation-section" style="display: none;"></div>
						</div>
						<div class="col-md-6">
							<div class="configuration_details">
								<div class="frm-select-text" id="product_configuration">
									<div class="" id="loader" onclick="$(this).hide()" style="display: none;">
										<div class="modal-backdrop-loader"
											style="position: absolute;top: 0;right: 0; bottom: 0; left: 0; z-index: 1000; background-color: #000; opacity: 0.5;">
											<i class="fa fa-spinner fa-spin text-white font-30"
												style="position: absolute;right: 50%;top: 50%;font-size: 26px;"></i>
										</div>
									</div>
									Please Select The Product
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="convert_to_order_popup" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">{l s='Create Order for Customer' mod='quoterequest'}</h3>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<div class="row">
						<div class="col-md-12">
							<div class="customer_details mb-3">
								<h4>
									{if $customer->firstname != '' && $customer->lastname != ''}
										{l s='Customer:' mod='quoterequest'} {$customer->firstname} {$customer->lastname}
										<span>({$customer->email})</span>
									{else}
										{l s='Customer:' mod='quoterequest'} {$quote->email}
									{/if}
								</h4>
							</div>
							<div class="address_details mb-3">
								<h4>{l s='Select Shipping Address' mod='quoterequest'}</h4>
								{if !empty($customer_addresses)}
									<select name="address" id="address" class="form-control mb-3">
										<option value="">{l s='-- Select an address --' mod='quoterequest'}</option>
										{foreach $customer_addresses as $address}
											<option value="{$address.id_address}">
												{$address.alias}: {$address.address1}{if $address.address2}, {$address.address2}{/if}, {$address.postcode} {$address.city}{if $address.state}, {$address.state}{/if}, {$address.country}
											</option>
										{/foreach}
									</select>
									<div id="selected_address_details" style="display:none;"></div>
								{else}
									<span>
										{l s='No addresses found. You can add an address by clicking ' mod='quoterequest'}
										<a href="{$add_new_address_page_link}" target="_blank">{l s='here' mod='quoterequest'}</a>
									</span>
								{/if}
							</div>
							<div class="order-actions text-right">
								<button type="button" class="btn btn-success" id="create_order_btn" disabled>
									<i class="fa fa-check"></i> {l s='Create Order' mod='quoterequest'}
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
/* Modal header */
#convert_to_order_popup .modal-header {
    border-bottom: 1px solid #e5e5e5;
    background: #f8f9fa;
    padding: 20px 24px 16px 24px;
}
#convert_to_order_popup .modal-title {
    font-size: 1.5em;
    font-weight: 600;
    color: #222;
}
#convert_to_order_popup .close {
    font-size: 1.4em;
    color: #888;
    opacity: 1;
}

/* Modal body */
#convert_to_order_popup .modal-body {
    background: #fff;
    padding: 28px 32px 24px 32px;
}

#convert_to_order_popup .customer_details h4 {
    font-size: 1.1em;
    font-weight: 500;
    margin-bottom: 18px;
    color: #333;
}

#convert_to_order_popup .address_details h4 {
    font-size: 1em;
    font-weight: 600;
    margin-bottom: 10px;
    color: #444;
}

#convert_to_order_popup select.form-control {
    border-radius: 6px;
    border: 1px solid #d1d5db;
    font-size: 1em;
    padding: 10px 12px;
    transition: border-color 0.2s;
    background: #f6f8fa;
    margin-bottom: 0;
}
#convert_to_order_popup select.form-control:focus {
    border-color: #007bff;
    background: #fff;
}
#convert_to_order_popup select.form-control:hover {
    border-color: #007bff;
}

/* Address card */
#selected_address_details .card {
    margin-top: 22px;
    margin-bottom: 18px;
    border: 1px solid #e3e3e3;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    background: #fafdff;
    padding: 22px 28px 18px 28px;
    max-width: 520px;
    font-size: 1em;
    color: #222;
}
#selected_address_details .card .address-alias {
    font-size: 1.08em;
    font-weight: 600;
    color: #007bff;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 7px;
}
#selected_address_details .card .address-alias i {
    color: #007bff;
    font-size: 1.1em;
}
#selected_address_details .card .address-section {
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 7px;
}
#selected_address_details .card .address-section i {
    color: #888;
    font-size: 1em;
    width: 18px;
    text-align: center;
}
#selected_address_details .card .address-section:last-child {
    margin-bottom: 0;
}

/* Divider */
#convert_to_order_popup .divider {
    border-top: 1px solid #e5e5e5;
    margin: 28px 0 18px 0;
}

/* Button */
#convert_to_order_popup .order-actions {
    margin-top: 18px;
}
#convert_to_order_popup #create_order_btn:disabled {
    background: #b3d1fa;
    color: #fff;
    cursor: not-allowed;
    box-shadow: none;
}
</style>
<div class="divider"></div>
{* since 3.0.2 *}
<script>
	$(document).ready(function() {
		$(document).on('change', '#products', function() {
			var id_product = $(this).val();
			var fd = new FormData();
			fd.append('id_product', id_product);
			fd.append('action', 'get_product_details');

			$('.animation-section').show();
			$('.product_details').hide();
			$('.load_configuration').attr('data-id_product', id_product);
			$('#product_configuration').html(
				"<div class=' id='loader' onclick='$(this).hide()' style='display: none;'><div class='modal-backdrop-loader' style='position: absolute;top: 0;right: 0; bottom: 0; left: 0; z-index: 1000; background-color: #000; opacity: 0.5;'><i class='fa fa-spinner fa-spin text-white font-30' style='position: absolute;right: 50%;top: 50%;font-size: 26px;'></i></div></div>Please Select The Product"
			);

			$.ajax({
				url: '{$ajax_link}',
				type: 'POST', // Change to POST
				data: fd,
				processData: false,
				contentType: false, // Add this line when using FormData
				dataType: 'json',
				success: function(response) {
					if (response.status) {
						$('#product_name').text(response.product_name);
						$('#ratings').text(response.ratings);
						$('#rating_count').text(response.rating_count);
						$('#product_description').html(response.description);
						$('#product_image').attr("src", response.image_url);
						$('.product_details').show();
						$('.animation-section').hide();
					} else {
						console.error('Error:', response.message);
					}
				},
				error: function(xhr, status, error) {
					console.error('Error: ' + error);
				}
			});
		});

		$(document).on('click', '.load_configuration', function(e) {
			e.preventDefault();
			var id_product = $(this).attr('data-id_product');

			var fd = new FormData();
			fd.append('id_product', id_product);
			fd.append('action', 'load_configuration');

			$("#loader").show();
			$.ajax({
				url: '{$ajax_link}',
				type: 'POST',
				data: fd,
				processData: false,
				contentType: false,
				dataType: 'json',
				success: function(response) {
					$("#loader").hide();
					if (response.status) {
						$('#product_configuration').html(response.content);
					} else {
						console.error('Error:', response.message);
					}
				},
				error: function(xhr, status, error) {
					console.error('Error: ' + error);
				}
			});
		});

		$(document).on('click', '.configure-area .add_to_quote_list', function(e) {
			e.preventDefault();

			var ajax_action_path = window.location.href.split('#')[0] + '&ajax=1';
			var fd = new FormData();

			var elements = Array.from(document.querySelectorAll('.input_for_url, .btn-select-value'))
				.filter(element => element.type !== "hidden");
			var configuration = [];
			elements.forEach(function(element, index) {
				var variable_id = element.dataset.variable_id;
				var variable_name = element.dataset.variableName;
				var formula_name = element.dataset.formulaName;
				var value, valueText;

				if (element.classList.contains('input_for_url')) {
					value = element.value;
					valueText = value;
				} else if (element.classList.contains('btn-select-value')) {
					value = element.value;
					valueText = element.options[element.selectedIndex].text;
				}

				// Add the data to the currentData object using the variable_id as a key
				configuration[index] = {
					value: value,
					variable_id: variable_id,
					variable_name: variable_name,
					formula_name: formula_name,
					valueText: valueText
				};
			});
			var configurationJSON = JSON.stringify(configuration);
			fd.append('product_configuration', configurationJSON);
			fd.append('product_additional_information', $(".additional_information").val());

			var files = $(".attachments_images")[0].files;
			for (var i = 0; i < files.length; i++) {
				fd.append('product_files[]', files[i]);
			}

			$.ajax({
				url: ajax_action_path + '&action=add_product_to_quote_list&id_product=' + $(
					'.load_configuration').attr('data-id_product'),
				type: 'POST',
				data: fd,
				processData: false,
				contentType: false,
				dataType: 'json',
				success: function(response) {
					window.location.reload();
				},
				error: function(xhr, status, error) {
					console.error('Error: ' + error);
				}
			});
		});

		var addresses = {$customer_addresses|@json_encode};

		$('#address').on('change', function() {
			var id = $(this).val();
			if (!id) {
				$('#selected_address_details').hide();
				$('#create_order_btn').prop('disabled', true);
				return;
			}
			var addr = addresses.find(function(a) { return a.id_address == id; });
			if (addr) {
				var html = '<div class="card">';
				html += '<div class="address-alias"><i class="fa fa-map-marker-alt"></i> ' + addr.alias + '</div>';
				html += '<div class="address-section"><i class="fa fa-user"></i> ' + addr.firstname + ' ' + addr.lastname + (addr.company ? ' <span style="color:#888;">(' + addr.company + ')</span>' : '') + '</div>';
				html += '<div class="address-section"><i class="fa fa-home"></i> ' + addr.address1 + (addr.address2 ? ', ' + addr.address2 : '') + '</div>';
				html += '<div class="address-section"><i class="fa fa-city"></i> ' + addr.postcode + ' ' + addr.city + '</div>';
				if (addr.state) html += '<div class="address-section"><i class="fa fa-flag"></i> ' + addr.state + ', ' + addr.country + '</div>';
				else html += '<div class="address-section"><i class="fa fa-flag"></i> ' + addr.country + '</div>';
				if (addr.phone) html += '<div class="address-section"><i class="fa fa-phone"></i> <span style="color:#888;">' + addr.phone + '</span></div>';
				if (addr.phone_mobile) html += '<div class="address-section"><i class="fa fa-mobile-alt"></i> <span style="color:#888;">' + addr.phone_mobile + '</span></div>';
				html += '</div>';
				$('#selected_address_details').html(html).show();
				$('#create_order_btn').prop('disabled', false);
			}
		});

		// You can add your AJAX call for order creation here
		$('#create_order_btn').on('click', function() {
			var id_address = $('#address').val();
			if (!id_address) return;
			var id_quote = $('.bootstrap.af.quoterequest').data('id_quote');
			var btn = $(this);
			btn.prop('disabled', true).text('Processing...');
			$.ajax({
				url: window.location.href.split('#')[0] + '&ajax=1&action=createOrderFromQuote',
				type: 'POST',
				data: {
					id_quote: id_quote,
					id_address: id_address
				},
				dataType: 'json',
				success: function(response) {
					btn.prop('disabled', false).text('Create Order');
					if (response.success) {
						swal({
							title: 'Order Created!',
							text: 'Order ID: ' + response.order_id,
							icon: 'success',
							button: 'View Order'
						}).then(function() {
							// Optionally redirect to order page
							window.location.href = 'index.php?controller=AdminOrders&id_order=' + response.order_id + '&vieworder=1';
						});
					} else {
						swal('Error', response.error || 'Unknown error', 'error');
					}
				},
				error: function(xhr, status, error) {
					console.log(xhr, status, error);
					btn.prop('disabled', false).text('Create Order');
					swal('Error', 'AJAX error: ' + error, 'error');
				}
			});
		});
	});
</script>