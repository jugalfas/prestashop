{extends file='checkout/_partials/steps/shipping_primary.tpl'}

{if Module::getInstanceByName('pshowadvancedstock') && Module::isEnabled('pshowadvancedstock') && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT_WAREHOUSES_CARRIERS')}
{block name='delivery_options'}
   {foreach from=$delivery_options item=carriers key=carrier_id}
      {foreach from=Warehouse::getWarehouses() item=warehouse}
      {if isset($carriers[$warehouse['id_warehouse']])}
       <div style="font-size: 1.25rem;line-height: normal;padding: 0px 0px 5px;margin-bottom: 15px;font-weight: bold;margin-right: -15px;margin-left: -15px;">
		  {l s='Shipping from' mod='pshowadvancedstock'}: {$warehouse['name']}
		  <p style="margin: 1rem 0px 0.5rem;">{l s='Products' mod='pshowadvancedstock'}: {$carriers[$warehouse['id_warehouse']]['cart_quantity']}<span style="float: right;">{l s='Total' mod='pshowadvancedstock'}: {Tools::displayPrice($carriers[$warehouse['id_warehouse']]['cart_price'])}</span></p>
          <ul style="margin: 0px;list-style: none;padding-left: 0px;">
          {foreach from=$carriers[$warehouse['id_warehouse']]["products"] item=product}
          	<li style="font-size: 1rem;line-height: normal;padding: 1rem;border: 1px solid #ddd;font-weight: normal;  position: relative;margin-bottom: 0.5rem;min-height: 7rem;">
          		<img src="{$product["img_url"]}" style="max-width: 15%;" /> <a href="{$product['url']}" target="_blank" style="cursor: pointer;position: absolute;top: 1rem;display: inline-block;margin-left: 1rem;max-width: 70%;line-height: 1.25rem;">{$product['name']}</a>
          		<span style="position: absolute;bottom: 1rem;display: inline-block;margin-left: 1rem;">{$product['cart_quantity']} x {Tools::displayPrice($product.price)}</span> <span style="position: absolute;bottom: 1rem;display: inline-block;margin-left: 1rem;right: 1rem;">{l s='Total' mod='pshowadvancedstock'}: {Tools::displayPrice($product.price*$product.cart_quantity)}</span>
          	</li>
          {/foreach}
          </ul>
       </div>
	   <div class="delivery-options">
          {foreach from=$carriers[$warehouse['id_warehouse']]["carriers"] item=carrier key=w_id}
              <div class="row delivery-option js-delivery-option">
                <div class="col-sm-1">
                  <span class="custom-radio float-xs-left">
                    <input type="radio" name="delivery_option[{$warehouse['id_warehouse']}][{$id_address}]" id="delivery_option_{$warehouse['id_warehouse']}_{$carrier.id_carrier}" value="{$carrier.id_carrier}"{if $delivery_option == $carrier_id} checked{/if}>
                    <span></span>
                  </span>
                </div>
                <label for="delivery_option_{$carrier.id}" class="col-xs-9 col-sm-11 delivery-option-2">
                  <div class="row">
                    <div class="col-sm-5 col-xs-12">
                      <div class="row carrier{if $carrier.logo} carrier-hasLogo{/if}">
                        {if $carrier.logo}
                        <div class="col-xs-12 col-md-4 carrier-logo">
                            <img src="{$carrier.logo}" alt="{$carrier.name}" loading="lazy" />
                        </div>
                        {/if}
                        <div class="col-xs-12 carriere-name-container{if $carrier.logo} col-md-8{/if}">
                          <span class="h6 carrier-name">{if $carrier.name}{$carrier.name}{else}{$carrier.delay}{/if}</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-sm-4 col-xs-12">
                      <span class="carrier-delay">{$carrier.delay}</span>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                      <span class="carrier-price">{$carrier.price}</span>
                    </div>
                  </div>
                </label>
              </div>
              <div class="row carrier-extra-content js-carrier-extra-content"{if $delivery_option != $carrier_id} style="display:none;"{/if}>
                {$carrier.extraContent nofilter}
              </div>
              <div class="clearfix"></div>
          {/foreach}
       </div>
       {/if}
    {/foreach}
    {if isset($carriers[0])}
       <div style="font-size: 1.25rem;line-height: normal;padding: 0px 0px 5px;margin-bottom: 15px;font-weight: bold;margin-right: -15px;margin-left: -15px;">
		  {l s='Other products' mod='pshowadvancedstock'}:
          <p style="margin: 1rem 0px 0.5rem;">{l s='Products' mod='pshowadvancedstock'}: {$carriers[0]['cart_quantity']}<span style="float: right;">{l s='Total' mod='pshowadvancedstock'}: {Tools::displayPrice($carriers[0]['cart_price'])}</span></p>
          <ul style="margin: 0px;list-style: none;padding-left: 0px;">
          {foreach from=$carriers[0]["products"] item=product}
          	<li style="font-size: 1rem;line-height: normal;padding: 1rem;border: 1px solid #ddd;font-weight: normal;  position: relative;margin-bottom: 0.5rem;min-height: 7rem;">
          		<img src="{$product["img_url"]}" style="max-width: 15%;" /> <a href="{$product['url']}" target="_blank" style="cursor: pointer;position: absolute;top: 1rem;display: inline-block;margin-left: 1rem;max-width: 70%;line-height: 1.25rem;">{$product['name']}</a>
          		<span style="position: absolute;bottom: 1rem;display: inline-block;margin-left: 1rem;">{$product['cart_quantity']} x {Tools::displayPrice($product.price)}</span> <span style="position: absolute;bottom: 1rem;display: inline-block;margin-left: 1rem;right: 1rem;">{l s='Total' mod='pshowadvancedstock'}: {Tools::displayPrice($product.price*$product.cart_quantity)}</span>
          	</li>
          {/foreach}
          </ul>
       </div>
	   <div class="delivery-options">
          {foreach from=$carriers[0]["carriers"] item=carrier key=w_id}
              <div class="row delivery-option js-delivery-option">
                <div class="col-sm-1">
                  <span class="custom-radio float-xs-left">
                    <input type="radio" name="delivery_option[0][{$id_address}]" id="delivery_option_{0}_{$carrier.id_carrier}" value="{$carrier.id_carrier}"{if $delivery_option == $carrier_id} checked{/if}>
                    <span></span>
                  </span>
                </div>
                <label for="delivery_option_{$carrier.id}" class="col-xs-9 col-sm-11 delivery-option-2">
                  <div class="row">
                    <div class="col-sm-5 col-xs-12">
                      <div class="row carrier{if $carrier.logo} carrier-hasLogo{/if}">
                        {if $carrier.logo}
                        <div class="col-xs-12 col-md-4 carrier-logo">
                            <img src="{$carrier.logo}" alt="{$carrier.name}" loading="lazy" />
                        </div>
                        {/if}
                        <div class="col-xs-12 carriere-name-container{if $carrier.logo} col-md-8{/if}">
                          <span class="h6 carrier-name">{if $carrier.name}{$carrier.name}{else}{$carrier.delay}{/if}</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-sm-4 col-xs-12">
                      <span class="carrier-delay">{$carrier.delay}</span>
                    </div>
                    <div class="col-sm-3 col-xs-12">
                      <span class="carrier-price">{$carrier.price}</span>
                    </div>
                  </div>
                </label>
              </div>
              <div class="row carrier-extra-content js-carrier-extra-content"{if $delivery_option != $carrier_id} style="display:none;"{/if}>
                {$carrier.extraContent nofilter}
              </div>
              <div class="clearfix"></div>
          {/foreach}
       </div>
       {/if}
    {break}
  {/foreach}
{if $step_is_current}
<script>
{literal}
	document.addEventListener('DOMContentLoaded', function (event) {
		var isClicked = false;
		$(document).one('DOMSubtreeModified', function(){
			setTimeout(() => {
				if(!isClicked) {
					isClicked = true;
					$('.delivery-options-list .delivery-options input[type="radio"]').eq(0).trigger('click');
				}
			}, 500);
		});
	});
{/literal}
</script>
{/if}
{/block}
{/if}