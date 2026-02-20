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
				<i class="process-icon-save"></i> {l s='Save' mod='productpriceconfig'}
			</button>
		</div>
	{else if $type == 'resetBtn'}
		<a href="#" class="resetSelectors"><i class="icon-undo"></i> {l s='Reset' mod='productpriceconfig'}</a>
	{/if}
{/function}
{$product_html}
<div class="bootstrap af clearfix" data-id_product="{$id_product}">
	<div class="menu-panel col-lg-2 col-md-3">
		<div class="list-group">
			<a href="#filter-templates" class="list-group-item active"><i class="icon-filter"></i>
				{l s='Product variables' mod='productpriceconfig'}</a>
			<a href="#hook-settings" class="list-group-item"><i class="icon-cogs"></i>
				{l s='Formula setting' mod='productpriceconfig'}</a>
			<a href="#general-settings" class="list-group-item"><i class="icon-anchor"></i>
				{l s='Tired  price' mod='productpriceconfig'}</a>
			<a href="#customer-filters" class="list-group-item"><i class="icon-user"></i>
				{l s='Baned combinations' mod='productpriceconfig'}</a>
			<a href="#price_for_odd_quantities" class="list-group-item"><i class="icon-user"></i>
				{l s='Percentage for odd quantity' mod='productpriceconfig'}</a>

		</div>
	</div>
	<div class="panel tab-content col-lg-10 col-md-9">
		<div id="filter-templates" class="tab-pane active">
			<div class="template-group">
				<h3>
					{if isset($product_name)}
						{$product_name = Tools::strtolower($product_name)}
					{/if}
					{l s='Variables for %s' mod='productpriceconfig' sprintf=[$product_name]}
					<a href="{$back}" class="pull-right">
						<i class="icon-back"></i> {l s='Back' mod='productpriceconfig'}
					</a>
				</h3>
				<div class="template-list category sortable">
					{foreach $variables as $t}
						{include file="./variable-form.tpl"}
					{/foreach}
				</div>
				<a href="#" class="addNewFilter" data-toggle="modal" data-target="#dynamic-popup">
					<i class="icon-plus"></i> Add variable </a>
			</div>

		</div>
		<div id="hook-settings" class="tab-pane">
			<h3>{l s='Formula settings' mod='productpriceconfig'}</h3>
			<div class="ajax-warning alert alert-warning hidden"></div>
			<form method="post" action="" class="settigns_form form-horizontal clearfix">

				<div class="settings-item">
					<label class="settings-label">
						<span>
							{l s='Formula Price' mod='productpriceconfig'}
						</span>
					</label>
					<div class="settings-input">
						<input type="text" name="formula_price" value="{$product_setting->formula_price}" class="">
					</div>
				</div>
				<div class="settings-item">
					<label class="settings-label">
						<span>
							{l s='Formula Weight' mod='productpriceconfig'}
						</span>
					</label>
					<div class="settings-input">
						<input type="text" name="formula_weight" value="{$product_setting->formula_weight}" class="">
					</div>
				</div>
				<div class="settings-item">
					<label class="settings-label">
						<span>
							{l s='Formula Thickness' mod='productpriceconfig'}
						</span>
					</label>
					<div class="settings-input">
						<input type="text" name="formula_thickness" value="{$product_setting->formula_thickness}"
							class="">
					</div>
				</div>
				<div class="settings-item">
					<label class="settings-label">
						<span>
							{l s='Formula Shipping' mod='productpriceconfig'}
						</span>
					</label>
					<div class="settings-input">
						<input type="text" name="formula_shipping" value="{$product_setting->formula_shipping}"
							class="">
					</div>
				</div>
			</form>
			<div class="clearfix"></div>
			<div class="panel-footer">
				<button type="button" class="saveFormulaSettings btn btn-default">
					<i class="process-icon-save"></i> {l s='Save' mod='productpriceconfig'}
				</button>
			</div>
		</div>
		<div id="general-settings" class="tab-pane">
			<form method="post" action="" class="settigns_form form-horizontal clearfix" data-type="general">
				<h3>{l s='Tired Price' mod='productpriceconfig'}</h3>
				<div class="clearfix">
					{$tiered_price}
				</div>
			</form>
			{renderElement type='saveMultipleSettingsBtn'}
		</div>
		<div id="customer-filters" class="tab-pane">
			<h3>{l s='Banned combiations' mod='productpriceconfig'}</h3>

			<div class="alert alert-info">
				You can add banned combination rules
			</div>

			<div class="clearfix"></div>
			<button type="button" class="btn btn-primary" style="float:right;" data-toggle="modal"
				data-target="#exampleModalCenter">Add
				Rule</button>

			<div class="clearfix"></div>
			<table id="rules_list" style="width:100%">
				<thead>
					<tr>
						<th scope="col">#</th>
						<th scope="col">Name</th>
						<th scope="col">Rule</th>
						<th scope="col">Disallowed</th>
						<th scope="col">Actions</th>
					</tr>
				</thead>
				<tbody>
					{* <pre>
					{$rules_list|print_r}
					</pre> *}

				</tbody>
			</table>

			<div class="clearfix"></div>
			<div class="panel-footer">
				<button type="button" class="saveBanedCombination btn btn-default">
					<i class="process-icon-save"></i> {l s='Save' mod='productpriceconfig'}
				</button>
			</div>
			<div class="modal fade add-rules-modal" id="exampleModalCenter" tabindex="-1" role="dialog"
				aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document" style="width:max-content">
					<div class="modal-content">
						<div class="modal-header">
							{* <h3 class="modal-title" id="exampleModalLongTitle">{l s='Add Rule' mod='productpriceconfig'}
							</h3>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button> *}
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">{l s='Add Rule' mod='productpriceconfig'}</h4>
						</div>
						<div class="modal-body">
							<div id="main_section">
								<form id="rules_form">
									<input type="hidden" id="id_rule_list" name="id_rule_list" value="">
									<div class="row">
										<div class="form-group">
											<label for="ruleName">Rule Name</label>
											<input type="text" class="form-control" id="ruleName" name="rule_name"
												placeholder="Enter rule name">
											<span class="error_msg_for_name"></span>
										</div>
									</div>
									<label for="rules">Rules</label>
									<div class="rules_container">
										<div id="clone" class="clone_div">
											<div class="form-group rule_div">
												<label for="rules">Variables</label>
												<select class="form-control variables" id="variables1"
													name="variables1">
													<option value="0">Default select</option>
													{foreach $globle_variables as $t}
														<option value="{$t['id_variable']}"
															data-variable_name="{$t['name']}">{$t['name']}</option>
													{/foreach}
												</select>
												<span class="error_msg_for_variable"></span>
											</div>
											<div class="form-group rule_div">
												<label for="rules">Sign</label>
												<select class="form-control sign" id='sign1' name="sign1">
													<option value="0">Please select</option>
													<option value="1">=</option>
													<option value="3">
														< </option>
													<option value="2">></option>
												</select>
												<span class="error_msg_for_sign"></span>
											</div>
											<div class="form-group rule_div">
												<label for="rules">Constraint</label>
												{foreach $variable_options as $variable => $option}
													{if is_array($option)}
														<select class="form-control options options1" {if $variable != 1}
															style="display:none" {/if} data-id_variable="{$variable}"
															name="options1[]">
															<option value="0">Please Select Option</option>
															{foreach $option as $o}
																<option value="{$o['id_option']}">{$o['label']}</option>
															{/foreach}
														</select>
													{else}
														{foreach $option as $o}
															{if ($o == 1 || $o == 4 )}
																<input type="number" class="form-control options options1"
																	style="display: none;" data-id_variable="{$variable}"
																	name="options1[]">
															{/if}
															{if $o == 5}
																<input type="text" class="form-control options options1"
																	style="display: none;" data-id_variable="{$variable}"
																	name="options1[]">
															{/if}
														{/foreach}
													{/if}
												{/foreach}
												<span class="error_msg_for_constraint"></span>
											</div>
											<div class="form-group rule_div">
												<select class="form-control and_or_sign" name="and_or_sign1">
													<option value="0" selected>None</option>
													<option value="1">OR</option>
													<option value="2">AND</option>
												</select>
												<span class="error_msg_for_and_or_sign"></span>
											</div>
										</div>
										<input type="hidden" id="id_product" value="{$id_product}">
										<div class="disallow_group">
											<div class="disallow_variables clone" id="disallow_variables-1"
												style="display:flex;align-items: flex-end;">
												<div class="form-group rule_div">
													<label for="rules">Disallow</label>
													<div class="disallow_multiselect">
														<select class="form-control disallow" name="disallow1[]">
															<option value="">Please Select Option</option>
															{foreach $globle_variables as $t}
																<option value="{$t['id_variable']}">{$t['name']}</option>
															{/foreach}
														</select>
														<span class="error_msg_for_disallow_variable"></span>
													</div>
												</div>
												<div class="form-group option_for_disallow rule_div">
													<label for="rules">Options for disallow</label>
													<div class="multiselect-main-div">
														<select class="form-control disallow_option">
															<option value="">Please select variable first</option>
														</select>
														<span class="error_msg_for_disallow_option"></span>
													</div>
												</div>
												<button class="add_more form-group"
													style="background:white;border: 1px solid #bbcdd2;padding: 10px;border-radius: 4px;">
													<label></label><i class="fa fa-plus"></i>
												</button>
											</div>
										</div>
									</div>

									<button type="button" class="btn btn-secondary"
										data-dismiss="modal">{l s='Close' mod='productpriceconfig'}</button>
									{* <button type="submit" class="btn btn-primary save_as_txt">{l s='Save as text' mod='productpriceconfig'}</button> *}
									<button type="button" id='saveRule'
										class="btn btn-primary json_encoded">{l s='Save' mod='productpriceconfig'}</button>
									<button type="reset" id='resetRule'
										class="btn btn-danger">{l s='Reset' mod='productpriceconfig'}</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal fade edit_rules_modal" id="edit_rules_modal" tabindex="-1" role="dialog"
				aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document" style="width:max-content">
					<div class="modal-content">
						<div class="modal-header">
							{* <h3 class="modal-title" id="exampleModalLongTitle">{l s='Add Rule' mod='productpriceconfig'}
							</h3>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button> *}
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">{l s='Add Rule' mod='productpriceconfig'}</h4>
						</div>
						<div class="modal-body">
							<div id="main_section">
								<form id="rules_form">
									<input type="hidden" id="id_rule_list" name="id_rule_list" value="">
									<div class="row">
										<div class="form-group">
											<label for="ruleName">Rule Name</label>
											<input type="text" class="form-control" id="ruleName" name="rule_name"
												placeholder="Enter rule name">
											<span class="error_msg_for_name"></span>
										</div>
									</div>
									<label for="rules">Rules</label>
									<div class="rules_container">
										<div id="clone" class="clone_div">
											<div class="form-group rule_div">
												<label for="rules">Variables</label>
												<select class="form-control variables" id="variables1"
													name="variables1">
													<option value="0">Default select</option>
													{foreach $globle_variables as $t}
														<option value="{$t['id_variable']}"
															data-variable_name="{$t['name']}">{$t['name']}</option>
													{/foreach}
												</select>
												<span class="error_msg_for_variable"></span>
											</div>
											<div class="form-group rule_div">
												<label for="rules">Sign</label>
												<select class="form-control sign" id='sign1' name="sign1">
													<option value="0">Please select</option>
													<option value="1">=</option>
													<option value="3">
														< </option>
													<option value="2">></option>
												</select>
												<span class="error_msg_for_sign"></span>
											</div>
											<div class="form-group rule_div">
												<label for="rules">Constraint</label>
												{foreach $variable_options as $variable => $option}
													{if is_array($option)}
														<select class="form-control options options1" {if $variable != 1}
															style="display:none" {/if} data-id_variable="{$variable}"
															name="options1[]">
															<option value="0">Please Select Option</option>
															{foreach $option as $o}
																<option value="{$o['id_option']}">{$o['label']}</option>
															{/foreach}
														</select>
													{else}
														{foreach $option as $o}
															{if ($o == 1 || $o == 4 )}
																<input type="number" class="form-control options options1"
																	style="display: none;" data-id_variable="{$variable}"
																	name="options1[]">
															{/if}
															{if $o == 5}
																<input type="text" class="form-control options options1"
																	style="display: none;" data-id_variable="{$variable}"
																	name="options1[]">
															{/if}
														{/foreach}
													{/if}
												{/foreach}
												<span class="error_msg_for_constraint"></span>
											</div>
											<div class="form-group rule_div">
												<select class="form-control and_or_sign" name="and_or_sign1">
													<option value="0" selected>None</option>
													<option value="1">OR</option>
													<option value="2">AND</option>
												</select>
												<span class="error_msg_for_and_or_sign"></span>
											</div>
										</div>
										<input type="hidden" id="id_product" value="{$id_product}">
										<div class="disallow_group">
											<div class="disallow_variables clone" id="disallow_variables-1"
												style="display:flex;align-items: flex-end;">
												<div class="form-group rule_div">
													<label for="rules">Disallow</label>
													<div class="disallow_multiselect">
														<select class="form-control disallow" name="disallow1[]">
															<option value="">Please Select Option</option>
															{foreach $globle_variables as $t}
																<option value="{$t['id_variable']}">{$t['name']}</option>
															{/foreach}
														</select>
														<span class="error_msg_for_disallow_variable"></span>
													</div>
												</div>
												<div class="form-group option_for_disallow rule_div">
													<label for="rules">Options for disallow</label>
													<div class="multiselect-main-div">
														<select class="form-control disallow_option">
															<option value="">Please select variable first</option>
														</select>
														<span class="error_msg_for_disallow_option"></span>
													</div>
												</div>
												<button class="add_more form-group"
													style="background:white;border: 1px solid #bbcdd2;padding: 10px;border-radius: 4px;">
													<label></label><i class="fa fa-plus"></i>
												</button>
											</div>
										</div>
									</div>

									<button type="button" class="btn btn-secondary"
										data-dismiss="modal">{l s='Close' mod='productpriceconfig'}</button>
									{* <button type="submit" class="btn btn-primary save_as_txt">{l s='Save as text' mod='productpriceconfig'}</button> *}
									<button type="button" id='saveRule'
										class="btn btn-primary json_encoded">{l s='Save' mod='productpriceconfig'}</button>
									<button type="reset" id='resetRule'
										class="btn btn-danger">{l s='Reset' mod='productpriceconfig'}</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="price_for_odd_quantities" class="tab-pane">
			<h3>{l s='Percentage for odd quantity' mod='productpriceconfig'}</h3>

			<div class="alert alert-info">
				{l s='Here you can set percentage for odd quantity' mod='productpriceconfig'}
			</div>
			<div class="ajax-warning alert alert-warning hidden"></div>
			<form method="post" action="" class="percentage_for_odd_quantity_Form form-horizontal clearfix">

				<div class="settings-item">
					<label class="settings-label">
						<span>
							{l s='Percentage (%)' mod='productpriceconfig'}
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
					<i class="process-icon-save"></i> {l s='Save' mod='productpriceconfig'}
				</button>
			</div>
		</div>

	</div>
</div>
<div class="alert alert-warning reindex-reminder orig hidden">
	{l s='Don\'t forget to re-index all products' mod='productpriceconfig'}
	<button type="button" class="close close-reminder">&times;</button>
</div>
<div class="modal fade" id="dynamic-popup" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title"></h3>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="dynamic-content clearfix"></div>
		</div>
	</div>
</div>
{* since 3.0.2 *}
<script>
	var ajax_action_path = window.location.href.split('#')[0] + '&ajax=1';
	var id_product = $('.af').data('id_product');
	$('#rules_list').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": ajax_action_path + '&action=AjaxDatatableRulesList&id_product=' + id_product,
			"type": "GET"
		},
		"columns": [
			{ "data": "id", 'width': '10%' },
			{ "data": "name", 'width': '20%' },
			{ "data": "rule_text", 'width': '30%' },
			{ "data": "disllowed_text", 'width': '30%' },
			{ "data": "actions", 'width': '10%' }
		],
		"order": [
			[0, 'asc']
		]
	});
	var classList = $('.option_for_disallow select')[0].classList

	//classList.each(function(index,className){
	$.each(classList, function(index, className) {
		//	console.log('I am here', $(this).html())
		console.log(className)
		if (className == 'active') {
			console.log('i am in right position')
			//$(this).multiselect({
			//	includeSelectAllOption : true,
			//	nonSelectedText: 'Select an Option'
			//});
		}
	})
	//$('.option_for_disallow select').multiselect({
	//	includeSelectAllOption : true,
	//	nonSelectedText: 'Select an Option',
	//},'widget').addClass('test');

	$(document).on('click', '.edit-rule', function() {
		cloneCount = 0;
		cloneDisallowVariable = 0;
		var id_rule = $(this).data('id_rule');
		// Clear previous form data
		$('#edit_rules_modal #rules_form')[0].reset();
		$('#edit_rules_modal #id_rule_list').val(id_rule);
		$('#edit_rules_modal .modal-title').text('Edit Rule');

		$.ajax({
			url: ajax_action_path + '&action=AjaxGetRuleData',
			type: 'POST',
			dataType: 'json',
			data: {
				id_rule_list: id_rule
			},
			success: function(response) {
				if (response.success) {
					var ruleData = response.rule;
					$('#edit_rules_modal #ruleName').val(ruleData.name);

					var rules = JSON.parse(ruleData.rule);
					var disallow = JSON.parse(ruleData.disallow);

					// convert rules to json format
					var rulesJson = [];
					var $container = $('<div class="rules_container"></div>');
					var $disallow_container = $('<div class="disallow_group"></div>');
					$.each(rules, function(i, r) {
						// build one rule row
						var $row = $($('#clone').html());
						$row.find('.variables').val(r.variable).attr('id', 'variables' + (
							i + 1)).attr('name', 'variables' + (i + 1));
						$row.find('.options').val(r.variable)
						$row.find('.options').attr('name', 'options' + (i +
							1) + '[]');
						$row.find('.sign').val(r.sign).attr('id', 'sign' + (i + 1)).attr(
							'name', 'sign' + (i + 1));
						// show correct constraint input/select
						$row.find('.options').hide().filter('[data-id_variable="' + r
							.variable + '"]').show().val(r.option).attr(
							'name', 'options' + (i + 1) + '[]');


						$row.find('.and_or_sign').val(r.and_or_sign).attr('name',
							'and_or_sign' + (i + 1));

						// disallow section
						var $dis = $('.disallow_variables').first();
						$dis.attr('id', 'disallow_variables-' + (i + 1));
						$dis.find('.disallow').val(r.disallow_variable).attr('name',
							'disallow' + (i + 1) + '[]');

						// append to container
						var $wrapper = $('<div>', {
							id: (i == 0 ? 'clone' : 'clone-' + i),
							class: 'clone_div'
						}).append($row);

						$container.append($wrapper);

						// keep json for later
						rulesJson.push(r);
						cloneCount++;
					});

					$container.append('<input type="hidden" id="id_product" value="{$id_product}">');

					$.each(disallow, function(i, r) {
						// For each disallow item, dynamically build disallow structure, as above for rules. 
						// Start index from i+1 to keep consistent naming, or 1 if not array of objects.
						var $disallowWrapper = $('<div>', {
							id: 'disallow_variables-' + (i + 1),
							class: 'disallow_variables clone',
							css: {
								display: 'flex',
								alignItems: 'flex-end'
							}
						});

						// DISALLOW VARIABLE SELECT
						var $formGroupDisallow = $('<div class="form-group rule_div">')
							.append('<label for="rules">Disallow</label>');
						var $multiSelectDiv = $('<div class="disallow_multiselect"></div>');
						var $selectDisallow = $(
							'<select class="form-control disallow" name="disallow' + (i +
								1) + '[]"></select>');
						$selectDisallow.append(
							'<option value="">Please Select Option</option>');
						// Populate disallow variable select (copy-paste option HTML, or use PHP passed list)
						{foreach $globle_variables as $t}
							$selectDisallow.append('<option value="{$t["id_variable"]}">{$t["name"]}</option>');
						{/foreach}
						if (r.disallow_variable[0]) $selectDisallow.val(r.disallow_variable[
							0]);

						$multiSelectDiv.append($selectDisallow);
						$multiSelectDiv.append(
							'<span class="error_msg_for_disallow_variable"></span>');
						$formGroupDisallow.append($multiSelectDiv);

						// OPTIONS FOR DISALLOW
						var $optionDisallowGroup = $(
								'<div class="form-group option_for_disallow rule_div">')
							.append('<label for="rules">Options for disallow</label>');
						var $multiselectMainDiv = $(
							'<div class="multiselect-main-div"></div>');
						var $selectDisallowOption = $(
							'<select class="form-control disallow_option" data-id_variable="' +
							r.disallow_variable[0] +
							'" name="disallow_options' + (i + 1) + '[]" multiple></select>'
						);
						$selectDisallowOption.append(
							'<option value="">Please select variable first</option>');

						// Use AJAX to load options for the selected disallow variable,
						// and directly set the HTML of $selectDisallowOption using the returned HTML.
						if (r.disallow_variable && r.disallow_variable[0]) {
							$.ajax({
								type: 'POST',
								url: ajax_action_path +
									'&action=getOptionsBasedOnVariableId&id_variable=' +
									r.disallow_variable[0] + '&count=' + (i + 1) +
									'&selected_options=' + r.disallow_options,
								dataType: 'json',
								async: false,
								success: function(optResult) {
									if (optResult.type == 'select') {
										$selectDisallowOption.html(optResult.html);
										$multiselectMainDiv.append(
											$selectDisallowOption);
									} else {
										$multiselectMainDiv.append(optResult.html)
									}
								}
							});
						}


						$multiselectMainDiv.append(
							'<span class="error_msg_for_disallow_option"></span>');
						$optionDisallowGroup.append($multiselectMainDiv);

						// ADD MORE BUTTON
						var $addMoreBtn = $(
							'<button class="add_more form-group" style="background:white;border: 1px solid #bbcdd2;padding: 10px;border-radius: 4px;"><label></label><i class="fa fa-plus"></i></button>'
						);

						// Append groups to wrapper
						$disallowWrapper.append($formGroupDisallow)
							.append($optionDisallowGroup)
							.append($addMoreBtn);

						$disallow_container.append($disallowWrapper);
						cloneDisallowVariable++;
					});

					$container.append($disallow_container);

					// $('.rules_container').append($container);
					$('#edit_rules_modal .rules_container').replaceWith($container);

					$('.disallow_option').multiselect({
						includeSelectAllOption: true,
						nonSelectedText: 'Select an Option'
					});
					$('#edit_rules_modal').modal('show');
					$('#edit_rules_modal #saveRule').attr('id', 'updateRule')
				} else {
					swal("Error", "Could not fetch rule data.", "error");
				}
			},
			error: function(xhr, status, error) {
				console.error(xhr.responseText);
				swal("Error", "An error occurred while fetching rule data.", "error");
			}
		});
	});

	$(document).on('click', '#updateRule', function() {
		var formData = $('#rules_form')
			.find(':input:visible:not(:disabled)')
			.serializeArray();
		formData.push({ name: 'id_product', value: id_product });
		formData.push({ name: 'id_rule_list', value: $('#id_rule_list').val() });
		$('.disallow_option').each(function(index) {
			formData.push({ name: 'disallow_options' + (index + 1), value: $(this).val() });
		});

		var action = $('#id_rule_list').val() ? 'AjaxUpdateRule' : 'SaveBanCombSettings';

		$.ajax({
			url: ajax_action_path + '&action=' + action + '&count=' + cloneCount + '&cloneOptionCount=' +
				cloneDisallowVariable,
			type: 'POST',
			dataType: 'json',
			data: formData,
			success: function(response) {
				if (response.success) {
					swal("Success", response.message, "success");
					$('#exampleModalCenter').modal('hide');
					$('#rules_list').DataTable().ajax.reload();
				} else {
					swal("Error", response.message, "error");
				}
			},
			error: function(xhr, status, error) {
				console.error(xhr.responseText);
				swal("Error", "An error occurred while saving the rule.", "error");
			}
		});
	});

	// helper: populate disallow option select
	function loadDisallowOptions($select, varId, optId) {
		$.ajax({
			url: ajax_action_path + '&action=AjaxGetVariableOptions&id_variable=' + varId,
			type: 'GET',
			dataType: 'json',
			success: function(opts) {
				var $optSel = $select.closest('.disallow_variables').find('.disallow_option');
				$optSel.empty().append('<option value="">Please select option</option>');
				$.each(opts, function(idx, o) {
					$optSel.append('<option value="' + o.id_option + '">' + o.label + '</option>');
				});
				if (optId) $optSel.val(optId);
			}
		});
	}
</script>