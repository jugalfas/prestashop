{**
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
*}
<div class="panel">
	<h3><i class="icon-tag"></i> {l s='Tiered Price'}</h3>

	<form action="{$currentIndex|escape}" id="customised_price_form" class="form-horizontal" method="post" enctype="multipart/form-data">
		
		<div id="stage_option" class="form-group">
			<label class="control-label col-lg-3">{l s='Tiered price'}</label>
			<div class="col-lg-9">
				<div class="row">
					<div class="col-lg-3">
						<input type="text" id="from_quantity" name="from_quantity" value="" onchange="this.value = this.value.replace(/,/g, '.');" placeholder="From x unit(s)" />
					</div>
					<div class="col-lg-2">
						<input type="text" id="price" name="price" value="" onchange="this.value = this.value.replace(/,/g, '.');" placeholder="Value" />
					</div>
					<div class="col-lg-2">
						<a class="btn btn-sm btn-default clear-fix" id="add-stage">{l s='Add'}</a>
					</div>
				</div>
				<div class="row" >
				 <div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th><span class="title_box ">From Quantity</span></th>
									<th><span class="title_box ">Discount</span></th>
									<th></th>
								</tr>
							</thead>
							<tbody>
                           {if count($tiered_price)}
								{foreach from=$tiered_price item='tiered'}
								 <tr>
									<td>{$tiered.from_quantity}</td>
									<td>{$tiered.price}</td>
									<td><a class="btn btn-sm btn-default clear-fix delete-stage" ><i class="icon-remove"></i></a></td>
									<input type="hidden" name="qty[]" value="{$tiered.from_quantity}" />
			 					  <input type="hidden" name="price[]" value="{$tiered.price}" />

								</tr>
								{/foreach}
                            {/if}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

	</form>

	<script type="text/javascript">

		var currentToken = '{$currentToken|escape:'quotes'}';

    $("#add-stage").click(function(){
					 var qty = $("#from_quantity").val();
					 var price = $("#price").val();

					 if(!qty){
						 alert('Please add units');
						 return false;
					 }

					 if(!price){
						 alert('Please add amount');
						 return false;
					 }


					 var button = '<a class="btn btn-sm btn-default clear-fix delete-stage" ><i class="icon-remove"></i></a>';

					 var markup = "<tr><td>"+qty+"</td><td>" + price + "</td><td>" + button + "</td>";
					 markup += '<input type="hidden" name="qty[]"" value="'+qty+'" />';
					 markup += '<input type="hidden" name="price[]"" value="'+price+'" />';
					 markup += ' </tr>';
					 $(".table-responsive table tbody").append(markup);
			 });

		$(".table-responsive").on("click", ".delete-stage",  function(){
		   $(this).parents("tr").remove();
		});


	</script>

</div>
