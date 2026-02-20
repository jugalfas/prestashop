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
	<h3><i class="icon-tag"></i> {l s='Banned Combination'}</h3>

	<form action="{$currentIndex|escape}" id="customised_price_form" class="form-horizontal" method="post" enctype="multipart/form-data">
		
		<div id="stage_option" class="form-group">
			<label class="control-label col-lg-3">{l s='Banned Combination'}</label>
			<div class="col-lg-9">
				<div class="row">
					<div class="col-lg-3">
						<input type="checkbox" id="id_comb" name="id_comb" value="1"  />
					</div>
					
				</div>
			</div>
		</div>

	</form>

</div>
