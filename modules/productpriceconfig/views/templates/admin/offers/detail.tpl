<div class="panel offer-admin-detail">
    {* Offer Header *}
    <div class="panel-heading">
        <i class="icon icon-file"></i>
        {l s='Quotation' mod='productpriceconfig'} {$offer.reference}
        <span class="label label-{if $offer.status == 'accepted'}success{elseif $offer.status == 'rejected' || $offer.status == 'cancelled' || $offer.status == 'expired'}danger{elseif $offer.status == 'price_offered'}warning{else}info{/if} pull-right">
            {ucfirst(str_replace('_', ' ', $offer.status))}
        </span>
    </div>

    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <h3>{$offer.title|default:$offer.reference}</h3>
                <p><strong>{l s='Reference:' mod='productpriceconfig'}</strong> {$offer.reference}</p>
                <p><strong>{l s='Created:' mod='productpriceconfig'}</strong> {$offer.date_add}</p>
                <p><strong>{l s='Updated:' mod='productpriceconfig'}</strong> {$offer.date_upd}</p>
                {if $offer.expire_date}
                    <p><strong>{l s='Expiry:' mod='productpriceconfig'}</strong> {$offer.expire_date}</p>
                {/if}

                {if $offer.customer_note}
                    <div class="alert alert-info">
                        <strong>{l s='Customer Note:' mod='productpriceconfig'}</strong>
                        <p>{$offer.customer_note nofilter}</p>
                    </div>
                {/if}
                {if $offer.admin_note}
                    <div class="alert alert-warning">
                        <strong>{l s='Admin Note:' mod='productpriceconfig'}</strong>
                        <p>{$offer.admin_note nofilter}</p>
                    </div>
                {/if}
            </div>
            <div class="col-md-4">
                {* Customer Info *}
                {if $customer_info}
                    <div class="panel panel-default">
                        <div class="panel-heading">{l s='Customer' mod='productpriceconfig'}</div>
                        <div class="panel-body">
                            <p><strong>{$customer_info.name}</strong></p>
                            <p>{$customer_info.email}</p>
                            <p>
                                <a href="{$customer_info.admin_url}" target="_blank" class="btn btn-xs btn-default">
                                    <i class="icon icon-user"></i> {l s='View Profile' mod='productpriceconfig'}
                                </a>
                            </p>
                            {if $customer_info.addresses}
                                <select id="address-select" class="form-control" style="margin-top: 10px;">
                                    {foreach from=$customer_info.addresses item=addr}
                                        <option value="{$addr.id_address}">{$addr.address1}, {$addr.city}, {$addr.country}</option>
                                    {/foreach}
                                </select>
                            {/if}
                        </div>
                    </div>
                {else}
                    <div class="alert alert-info">
                        <p><strong>{l s='Guest Customer' mod='productpriceconfig'}</strong></p>
                        <p>{$offer.guest_name}</p>
                        <p>{$offer.guest_email}</p>
                    </div>
                {/if}
            </div>
        </div>

        {* Action Buttons *}
        <div class="offer-admin-actions" style="margin-top: 15px;">
            <a href="{$list_url}" class="btn btn-default">
                <i class="icon icon-arrow-left"></i> {l s='Back' mod='productpriceconfig'}
            </a>
            <a href="{$pdf_url}" class="btn btn-default" target="_blank">
                <i class="icon icon-file-pdf"></i> {l s='Print PDF' mod='productpriceconfig'}
            </a>
            <button type="button" class="btn btn-warning" id="btn-extend-expiry" data-id-offer="{$offer.id_offer}">
                <i class="icon icon-calendar"></i> {l s='Extend Expiry' mod='productpriceconfig'}
            </button>
            <button type="button" class="btn btn-danger" id="btn-cancel-offer" data-id-offer="{$offer.id_offer}">
                <i class="icon icon-ban-circle"></i> {l s='Cancel Offer' mod='productpriceconfig'}
            </button>
            {if $has_accepted}
                <button type="button" class="btn btn-success" id="btn-convert-order" data-id-offer="{$offer.id_offer}">
                    <i class="icon icon-shopping-cart"></i> {l s='Convert To Order' mod='productpriceconfig'}
                </button>
            {/if}
        </div>
    </div>
</div>

{* Products *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-th-large"></i> {l s='Products' mod='productpriceconfig'}
    </div>
    <div class="panel-body">
        {foreach from=$products item=p}
            <div class="card offer-product-card" data-id-offer-product="{$p.id_offer_product}" style="margin-bottom: 20px;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            {if $p.image}
                                <img src="{$p.image}" alt="" class="img-fluid rounded" style="max-height: 80px;" />
                            {else}
                                <i class="icon icon-picture" style="font-size: 48px; color: #ddd;"></i>
                            {/if}
                        </div>
                        <div class="col-md-4">
                            <h5>{$p.product_name}</h5>
                            <p class="text-muted small">{$p.product_reference}</p>
                            <p class="small"><strong>{l s='Quantity:' mod='productpriceconfig'}</strong> {$p.quantity}</p>
                            <p class="small"><strong>{l s='Configuration:' mod='productpriceconfig'}</strong> {$p.configuration_summary}</p>
                            {if $p.customer_note}
                                <div class="alert alert-info small">
                                    <strong>{l s='Customer Note:' mod='productpriceconfig'}</strong> {$p.customer_note}
                                </div>
                            {/if}
                            {if $p.attachments}
                                <div class="small">
                                    <strong>{l s='Artwork:' mod='productpriceconfig'}</strong>
                                    {foreach from=$p.attachments item=att}
                                        <div>
                                            <i class="icon icon-file"></i>
                                            {$att.original_name}
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                        <div class="col-md-3">
                            <span class="label label-{if $p.product_status == 'accepted'}success{elseif $p.product_status == 'rejected' || $p.product_status == 'cancelled'}danger{elseif $p.product_status == 'price_offered'}warning{else}info{/if}">
                                {$p.product_status_label}
                            </span>
                            {if $p.offered_price}
                                <p class="small"><strong>{l s='Offered:' mod='productpriceconfig'}</strong> {Tools::displayPrice($p.offered_price)}</p>
                            {/if}
                            {if $p.weight}
                                <p class="small"><strong>{l s='Weight:' mod='productpriceconfig'}</strong> {$p.weight} kg</p>
                            {/if}
                            {if $p.production_time}
                                <p class="small"><strong>{l s='Production:' mod='productpriceconfig'}</strong> {$p.production_time}</p>
                            {/if}
                        </div>
                        <div class="col-md-3 text-right">
                            {* Price Offer Form *}
                            <div class="price-offer-form" data-id-offer-product="{$p.id_offer_product}">
                                <div class="form-group">
                                    <label class="small">{l s='Offered Price' mod='productpriceconfig'}</label>
                                    <input type="number" class="form-control input-sm offered-price-input" step="0.01" value="{$p.offered_price|default:''}" />
                                </div>
                                <div class="form-group">
                                    <label class="small">{l s='Weight (kg)' mod='productpriceconfig'}</label>
                                    <input type="number" class="form-control input-sm weight-input" step="0.01" value="{$p.weight|default:''}" />
                                </div>
                                <div class="form-group">
                                    <label class="small">{l s='Production Time' mod='productpriceconfig'}</label>
                                    <input type="text" class="form-control input-sm production-time-input" value="{$p.production_time|default:''}" />
                                </div>
                                <div class="form-group">
                                    <label class="small">{l s='Admin Note' mod='productpriceconfig'}</label>
                                    <textarea class="form-control input-sm admin-note-input" rows="2">{$p.admin_note|default:''}</textarea>
                                </div>
                                <div class="form-group">
                                    <label class="small">{l s='Validity (days)' mod='productpriceconfig'}</label>
                                    <input type="number" class="form-control input-sm expire-days-input" value="{$default_validity_days}" />
                                </div>
                                <button type="button" class="btn btn-primary btn-sm btn-save-price" data-id-offer="{$offer.id_offer}" data-id-offer-product="{$p.id_offer_product}">
                                    <i class="icon icon-save"></i> {l s='Save & Send' mod='productpriceconfig'}
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-delete-product" data-id-offer="{$offer.id_offer}" data-id-offer-product="{$p.id_offer_product}">
                                    <i class="icon icon-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
</div>

{* History *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-time"></i> {l s='History' mod='productpriceconfig'}
    </div>
    <div class="panel-body">
        <div class="timeline">
            {foreach from=$history item=h}
                <div class="timeline-item">
                    <div class="timeline-content">
                        <span class="text-muted small">{$h.date_add}</span>
                        <span class="label label-info">{$h.action_label}</span>
                        {if $h.actor}<span class="text-muted small">by {$h.actor}</span>{/if}
                        {if $h.description}<p class="small">{$h.description}</p>{/if}
                        {if $h.previous_value && $h.new_value}
                            <p class="small text-muted">
                                <span class="text-danger">{$h.previous_value}</span> &rarr; <span class="text-success">{$h.new_value}</span>
                            </p>
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
</div>

{* Convert to Order Modal *}
<div class="modal fade" id="convert-order-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Convert To Order' mod='productpriceconfig'}</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{l s='Delivery Address' mod='productpriceconfig'}</label>
                    <select id="convert-address-delivery" class="form-control">
                        {if $customer_info.addresses}
                            {foreach from=$customer_info.addresses item=addr}
                                <option value="{$addr.id_address}">{$addr.address1}, {$addr.city}</option>
                            {/foreach}
                        {else}
                            <option value="0">{l s='No addresses available' mod='productpriceconfig'}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="form-group">
                    <label>{l s='Invoice Address' mod='productpriceconfig'}</label>
                    <select id="convert-address-invoice" class="form-control">
                        {if $customer_info.addresses}
                            {foreach from=$customer_info.addresses item=addr}
                                <option value="{$addr.id_address}">{$addr.address1}, {$addr.city}</option>
                            {/foreach}
                        {else}
                            <option value="0">{l s='No addresses available' mod='productpriceconfig'}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="form-group">
                    <label>{l s='Carrier' mod='productpriceconfig'}</label>
                    <select id="convert-carrier" class="form-control">
                        {foreach from=$carriers item=carrier}
                            <option value="{$carrier.id_reference}">{$carrier.name}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="form-group">
                    <label>{l s='Payment Method' mod='productpriceconfig'}</label>
                    <select id="convert-payment" class="form-control">
                        {foreach from=$payment_modules item=pm}
                            <option value="{$pm.id}">{$pm.name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Cancel' mod='productpriceconfig'}</button>
                <button type="button" class="btn btn-success" id="btn-confirm-convert" data-id-offer="{$offer.id_offer}">
                    <i class="icon icon-check"></i> {l s='Create Order' mod='productpriceconfig'}
                </button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="ajax-url" value="{$ajax_url}" />
<input type="hidden" id="list-url" value="{$list_url}" />
