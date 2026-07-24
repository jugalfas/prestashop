{extends file='page.tpl'}

{block name='page_title'}
    <span class="page-title">{l s='Quotation' mod='productpriceconfig'} {$offer.reference}</span>
{/block}

{block name='page_content_container'}
    <div id="offer-detail-page" class="offer-page-container">

        {* Offer Header *}
        <div class="card offer-header-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h2>{$offer.title|default:$offer.reference}</h2>
                        <p class="text-muted small">
                            <strong>{l s='Reference:' mod='productpriceconfig'}</strong> {$offer.reference}
                            &nbsp;|&nbsp;
                            <strong>{l s='Status:' mod='productpriceconfig'}</strong>
                            <span class="badge badge-{if $offer.status == 'accepted'}success{elseif $offer.status == 'rejected' || $offer.status == 'cancelled' || $offer.status == 'expired'}danger{elseif $offer.status == 'price_offered'}warning{else}info{/if}">
                                {ucfirst(str_replace('_', ' ', $offer.status))}
                            </span>
                        </p>
                        <p class="text-muted small">
                            <strong>{l s='Created:' mod='productpriceconfig'}</strong> {$offer.date_add}
                            {if $offer.expire_date}
                                &nbsp;|&nbsp;
                                <strong>{l s='Valid Until:' mod='productpriceconfig'}</strong> {$offer.expire_date}
                                {if $is_expired}
                                    <span class="text-danger">({l s='Expired' mod='productpriceconfig'})</span>
                                {/if}
                            {/if}
                        </p>
                        {if $offer.customer_note}
                            <div class="alert alert-light">
                                <strong>{l s='Your Note:' mod='productpriceconfig'}</strong>
                                <p>{$offer.customer_note nofilter}</p>
                            </div>
                        {/if}
                        {if $offer.admin_note}
                            <div class="alert alert-info">
                                <strong>{l s='Admin Note:' mod='productpriceconfig'}</strong>
                                <p>{$offer.admin_note nofilter}</p>
                            </div>
                        {/if}
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="{$my_offers_url}" class="btn btn-outline-secondary">
                            <i class="material-icons">arrow_back</i> {l s='Back' mod='productpriceconfig'}
                        </a>
                        <button type="button" class="btn btn-outline-primary btn-duplicate-offer" data-id-offer="{$offer.id_offer}">
                            <i class="material-icons">content_copy</i> {l s='Duplicate' mod='productpriceconfig'}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {* Products *}
        <div class="offer-products-section">
            <h3>{l s='Products' mod='productpriceconfig'}</h3>
            {foreach from=$products item=p}
                <div class="card offer-product-card" data-id-offer-product="{$p.id_offer_product}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                {if $p.image}
                                    <img src="{$p.image}" alt="{$p.product_name}" class="img-fluid rounded" />
                                {else}
                                    <i class="material-icons" style="font-size: 64px; color: #ddd;">image</i>
                                {/if}
                            </div>
                            <div class="col-md-5">
                                <h5>{$p.product_name}</h5>
                                <p class="text-muted small">{$p.product_reference}</p>
                                <p class="small"><strong>{l s='Quantity:' mod='productpriceconfig'}</strong> {$p.quantity}</p>
                                <p class="small"><strong>{l s='Configuration:' mod='productpriceconfig'}</strong> {$p.configuration_summary}</p>
                                {if $p.customer_note}
                                    <p class="small"><strong>{l s='Your Note:' mod='productpriceconfig'}</strong> {$p.customer_note}</p>
                                {/if}
                                {if $p.admin_note}
                                    <div class="alert alert-info small">
                                        <strong>{l s='Admin Note:' mod='productpriceconfig'}</strong> {$p.admin_note}
                                    </div>
                                {/if}
                            </div>
                            <div class="col-md-3">
                                {if $p.offered_price}
                                    <p class="offer-price-display">
                                        <strong>{l s='Offered Price:' mod='productpriceconfig'}</strong><br/>
                                        <span class="price text-success">{$p.offered_price}</span>
                                    </p>
                                {else}
                                    <p class="text-muted">{l s='Price not yet offered' mod='productpriceconfig'}</p>
                                {/if}
                                {if $p.weight}
                                    <p class="small"><strong>{l s='Weight:' mod='productpriceconfig'}</strong> {$p.weight} kg</p>
                                {/if}
                                {if $p.production_time}
                                    <p class="small"><strong>{l s='Production:' mod='productpriceconfig'}</strong> {$p.production_time}</p>
                                {/if}
                            </div>
                            <div class="col-md-2 text-right">
                                <span class="badge badge-{if $p.product_status == 'accepted'}success{elseif $p.product_status == 'rejected' || $p.product_status == 'cancelled' || $p.product_status == 'expired'}danger{elseif $p.product_status == 'price_offered'}warning{else}info{/if}">
                                    {$p.product_status_label}
                                </span>
                                {* Attachments *}
                                {if $p.attachments}
                                    <div class="offer-attachments small" style="margin-top: 10px;">
                                        <strong>{l s='Artwork:' mod='productpriceconfig'}</strong>
                                        {foreach from=$p.attachments item=att}
                                            <div class="attachment-item">
                                                <i class="material-icons" style="font-size: 16px;">attach_file</i>
                                                {$att.original_name}
                                            </div>
                                        {/foreach}
                                    </div>
                                {/if}
                            </div>
                        </div>

                        {* Action buttons per product *}
                        <div class="offer-product-actions" style="margin-top: 15px;">
                            {if $p.product_status == 'price_offered' && !$is_expired}
                                <button type="button" class="btn btn-success btn-accept-product" data-id-offer="{$offer.id_offer}" data-id-offer-product="{$p.id_offer_product}">
                                    <i class="material-icons">check</i> {l s='Accept' mod='productpriceconfig'}
                                </button>
                                <button type="button" class="btn btn-danger btn-reject-product" data-id-offer="{$offer.id_offer}" data-id-offer-product="{$p.id_offer_product}">
                                    <i class="material-icons">close</i> {l s='Reject' mod='productpriceconfig'}
                                </button>
                            {elseif $p.product_status == 'accepted' && !$is_expired}
                                <div class="alert alert-success">
                                    <i class="material-icons">check_circle</i> {l s='Accepted - You can add this to your cart.' mod='productpriceconfig'}
                                </div>
                            {elseif $p.product_status == 'rejected'}
                                <div class="alert alert-danger">
                                    {l s='Rejected - You can request a new price.' mod='productpriceconfig'}
                                </div>
                            {elseif $p.product_status == 'expired' || $is_expired}
                                <div class="alert alert-warning">
                                    {l s='Expired - You can request a new offer.' mod='productpriceconfig'}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>

        {* Overall Actions *}
        <div class="offer-overall-actions" style="margin-top: 20px;">
            {if $has_accepted && !$is_expired}
                <button type="button" id="btn-add-to-cart" class="btn btn-primary btn-lg" data-id-offer="{$offer.id_offer}">
                    <i class="material-icons">shopping_cart</i> {l s='Add Accepted Products to Cart' mod='productpriceconfig'}
                </button>
            {/if}
        </div>

        {* History Timeline *}
        <div class="offer-history-section" style="margin-top: 30px;">
            <h3>{l s='History Timeline' mod='productpriceconfig'}</h3>
            <div class="timeline">
                {foreach from=$history item=h}
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <i class="material-icons">circle</i>
                        </div>
                        <div class="timeline-content">
                            <span class="timeline-date">{$h.date_add}</span>
                            <span class="timeline-action badge badge-info">{$h.action_label}</span>
                            {if $h.actor}
                                <span class="timeline-actor text-muted small">by {$h.actor}</span>
                            {/if}
                            {if $h.description}
                                <p class="timeline-description small">{$h.description}</p>
                            {/if}
                            {if $h.previous_value && $h.new_value}
                                <p class="timeline-values small text-muted">
                                    <span class="text-danger">{$h.previous_value}</span>
                                    &rarr;
                                    <span class="text-success">{$h.new_value}</span>
                                </p>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>

        {* Notification area *}
        <div id="offer-notification-area" class="offer-notification-area" style="display:none;"></div>

        <input type="hidden" id="ajax-url" value="{$ajax_url}" />
        <input type="hidden" id="my-offers-url" value="{$my_offers_url}" />
        <input type="hidden" id="is-logged" value="{if $is_logged}1{else}0{/if}" />
    </div>
{/block}
