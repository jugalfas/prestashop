{extends file='page.tpl'}

{block name='page_title'}
    <span class="page-title">{l s='Quotation' mod='productpriceconfig'} {$offer.reference}</span>
{/block}

{block name='page_content_container'}
    <div id="offer-detail-page" class="offer-page-container">

        {* Offer Header *}
        <div class="card offer-header-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="offer-detail-title">{$offer.title|default:$offer.reference}</h2>
                        <div class="offer-detail-meta">
                            <span class="offer-meta-pill">
                                <i class="material-icons">tag</i>
                                <strong>{$offer.reference}</strong>
                            </span>
                            <span class="offer-meta-pill">
                                <i class="material-icons">info</i>
                                <span class="badge offer-status-badge badge-{$offer.status}">{$offer.status_label}</span>
                            </span>
                            <span class="offer-meta-pill">
                                <i class="material-icons">event</i>
                                {$offer.date_add}
                            </span>
                            {if $offer.expire_date}
                                <span class="offer-meta-pill">
                                    <i class="material-icons">schedule</i>
                                    {$offer.expire_date}
                                    {if $is_expired}
                                        <span class="badge badge-danger">{l s='Expired' mod='productpriceconfig'}</span>
                                    {/if}
                                </span>
                            {/if}
                        </div>
                        {if $offer.customer_note}
                            <div class="alert alert-light offer-compact-alert">
                                <strong><i class="material-icons">note</i> {l s='Your Note:' mod='productpriceconfig'}</strong>
                                <p class="mb-0">{$offer.customer_note nofilter}</p>
                            </div>
                        {/if}
                        {if $offer.admin_note}
                            <div class="alert alert-info offer-compact-alert">
                                <strong><i class="material-icons">message</i> {l s='Admin Note:' mod='productpriceconfig'}</strong>
                                <p class="mb-0">{$offer.admin_note nofilter}</p>
                            </div>
                        {/if}
                    </div>
                    <div class="col-md-4 text-right offer-detail-actions">
                        <a href="{$my_offers_url}" class="btn btn-outline-secondary offer-action-btn">
                            <i class="material-icons">arrow_back</i> {l s='Back to List' mod='productpriceconfig'}
                        </a>
                        <button type="button" class="btn btn-outline-primary btn-duplicate-offer offer-action-btn" data-id-offer="{$offer.id_offer}">
                            <i class="material-icons">content_copy</i> {l s='Duplicate' mod='productpriceconfig'}
                        </button>
                        <button type="button" class="btn btn-primary btn-print-pdf offer-action-btn" data-id-offer="{$offer.id_offer}">
                            <i class="material-icons">picture_as_pdf</i> {l s='Print PDF' mod='productpriceconfig'}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {* Products *}
        <div class="offer-products-section">
            <h3 class="offer-section-title"><i class="material-icons">inventory_2</i> {l s='Products' mod='productpriceconfig'}</h3>
            {foreach from=$products item=p}
                <div class="card offer-product-card" data-id-offer-product="{$p.id_offer_product}">
                    <div class="card-body">
                        <div class="offer-product-grid">
                            {* Image *}
                            <div class="offer-product-image">
                                {if $p.image}
                                    <img src="{$p.image}" alt="{$p.product_name}" class="img-fluid rounded" />
                                {else}
                                    <i class="material-icons offer-product-placeholder">image</i>
                                {/if}
                            </div>

                            {* Info + Config Summary *}
                            <div class="offer-product-info">
                                <h5 class="offer-product-name">{$p.product_name}</h5>
                                {if $p.product_reference}
                                    <p class="offer-product-ref text-muted"><i class="material-icons">label</i> {$p.product_reference}</p>
                                {/if}
                                <p class="offer-product-qty small">
                                    <strong><i class="material-icons">widgets</i> {l s='Quantity:' mod='productpriceconfig'}</strong>
                                    {$p.quantity}
                                </div>
                                <div class="offer-meta-item">
                                    <strong><i class="material-icons">category</i> {l s='Type:' mod='productpriceconfig'}</strong>
                                    {if $p.product_type == 'custom'}{l s='Custom Product Request' mod='productpriceconfig'}{else}{l s='Normal Product' mod='productpriceconfig'}{/if}
                                </p>

                                {* Configuration Summary Component *}
                                {if $p.configuration_summary_html}
                                    <div class="offer-config-summary-wrap">
                                        <h6 class="offer-config-title"><i class="material-icons">tune</i> {l s='Configuration' mod='productpriceconfig'}</h6>
                                        {$p.configuration_summary_html nofilter}
                                    </div>
                                {/if}

                                {if $p.customer_note || $p.admin_note}
                                    <div class="offer-notes-chat">
                                        {if $p.customer_note}
                                            <div class="offer-note-bubble offer-note-customer">
                                                <div class="offer-note-bubble-header">
                                                    <i class="material-icons">note</i>
                                                    <span>{l s='Your Note' mod='productpriceconfig'}</span>
                                                </div>
                                                <div class="offer-note-bubble-body">{$p.customer_note}</div>
                                            </div>
                                        {/if}
                                        {if $p.admin_note}
                                            <div class="offer-note-bubble offer-note-admin">
                                                <div class="offer-note-bubble-header">
                                                    <i class="material-icons">message</i>
                                                    <span>{l s='Admin Note' mod='productpriceconfig'}</span>
                                                </div>
                                                <div class="offer-note-bubble-body">{$p.admin_note}</div>
                                            </div>
                                        {/if}
                                    </div>
                                {/if}

                                {* Uploaded Files *}
                                {if $p.attachments}
                                    <div class="offer-attachments">
                                        <h6 class="offer-attachments-title"><i class="material-icons">attach_file</i> {l s='Artwork Files' mod='productpriceconfig'}</h6>
                                        {foreach from=$p.attachments item=att}
                                            <div class="offer-attachment-item">
                                                <div class="offer-attachment-preview">
                                                    {if $att.is_image && $att.thumb_url}
                                                        <img src="{$att.thumb_url}" alt="{$att.original_name}" class="offer-att-thumb" />
                                                    {else}
                                                        <i class="material-icons offer-att-file-icon">insert_drive_file</i>
                                                    {/if}
                                                </div>
                                                <div class="offer-attachment-info">
                                                    <span class="offer-att-name">{$att.original_name}</span>
                                                    <span class="offer-att-size">{$att.formatted_size}</span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-download-attachment" data-id-attachment="{$att.id_attachment}" title="{l s='Download' mod='productpriceconfig'}">
                                                    <i class="material-icons">download</i>
                                                </button>
                                            </div>
                                        {/foreach}
                                    </div>
                                {/if}
                            </div>

                            {* Pricing & Status *}
                            <div class="offer-product-pricing">
                                <span class="badge offer-status-badge badge-{$p.product_status}">{$p.product_status_label}</span>

                                {if $p.offered_price !== null && $p.offered_price|default:'' !== ''}
                                    <div class="offer-price-block">
                                        {if $p.estimated_price && $p.estimated_price != $p.offered_price}
                                            <div class="offer-price-row offer-price-estimated">
                                                <span class="offer-price-label">{l s='Estimated' mod='productpriceconfig'}</span>
                                                <span class="offer-price-value">{OfferService::formatPrice($p.estimated_price)}</span>
                                            </div>
                                        {/if}
                                        {if $p.discounted_amount && $p.discounted_amount > 0}
                                            <div class="offer-price-row offer-price-discount">
                                                <span class="offer-price-label">{l s='Discount' mod='productpriceconfig'}</span>
                                                <span class="offer-price-value">- {Tools::displayPrice($p.discounted_amount)}</span>
                                            </div>
                                        {/if}
                                        <div class="offer-price-row offer-price-offered">
                                            <span class="offer-price-label">{l s='Offered Price' mod='productpriceconfig'}</span>
                                            <span class="offer-price-value offer-price-highlight">{OfferService::formatPrice($p.offered_price)}</span>
                                        </div>
                                    </div>
                                {else}
                                    <p class="text-muted offer-no-price">{l s='Price not yet offered' mod='productpriceconfig'}</p>
                                {/if}

                                {if $p.weight}
                                    <p class="offer-product-detail small"><i class="material-icons">scale</i> <strong>{l s='Weight:' mod='productpriceconfig'}</strong> {$p.weight} kg</p>
                                {/if}
                                {if $p.production_time}
                                    <p class="offer-product-detail small"><i class="material-icons">build</i> <strong>{l s='Production:' mod='productpriceconfig'}</strong> {$p.production_days} {l s='Days' mod='productpriceconfig'}</p>
                                {/if}
                            </div>
                        </div>

                        {* Accept / Reject Panel *}
                        {if $p.product_status == 'price_offered' && !$is_expired}
                            <div class="offer-accept-panel" data-id-offer-product="{$p.id_offer_product}">
                                <div class="offer-accept-left">
                                    <div class="offer-accept-info">
                                        <i class="material-icons">message</i>
                                        <div>
                                            <span class="offer-accept-label">{l s='Latest Admin Note' mod='productpriceconfig'}</span>
                                            <p class="offer-accept-value">{$p.admin_note|default:'—'}</p>
                                        </div>
                                    </div>
                                    <div class="offer-accept-info">
                                        <i class="material-icons">scale</i>
                                        <div>
                                            <span class="offer-accept-label">{l s='Estimated Weight' mod='productpriceconfig'}</span>
                                            <p class="offer-accept-value">{if $p.weight}{$p.weight} kg{else}—{/if}</p>
                                        </div>
                                    </div>
                                    <div class="offer-accept-info">
                                        <i class="material-icons">build</i>
                                        <div>
                                            <span class="offer-accept-label">{l s='Production Time' mod='productpriceconfig'}</span>
                                            <p class="offer-accept-value">{$p.production_days|default:'—'} {l s='Days' mod='productpriceconfig'}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="offer-accept-middle">
                                    <div class="offer-accept-price-line">
                                        <span>{l s='Estimated Price' mod='productpriceconfig'}</span>
                                        <strong>{OfferService::formatPrice($p.estimated_price)}</strong>
                                    </div>
                                    {if $p.discounted_amount && $p.discounted_amount > 0}
                                        <div class="offer-accept-price-line">
                                            <span>{l s='Discount' mod='productpriceconfig'}</span>
                                            <strong>- {Tools::displayPrice($p.discounted_amount)}</strong>
                                        </div>
                                    {/if}
                                    <div class="offer-accept-price-line offer-accept-final">
                                        <span>{l s='Offered Price' mod='productpriceconfig'}</span>
                                        <strong>{OfferService::formatPrice($p.offered_price)}</strong>
                                    </div>
                                </div>
                                <div class="offer-accept-right">
                                    <button type="button" class="btn btn-success btn-lg btn-accept-product" data-id-offer="{$offer.id_offer}" data-id-offer-product="{$p.id_offer_product}">
                                        <i class="material-icons">check_circle</i> {l s='Accept' mod='productpriceconfig'}
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-reject-product" data-id-offer="{$offer.id_offer}" data-id-offer-product="{$p.id_offer_product}">
                                        <i class="material-icons">cancel</i> {l s='Reject' mod='productpriceconfig'}
                                    </button>
                                </div>
                            </div>
                        {elseif $p.product_status == 'accepted' && !$is_expired}
                            <div class="alert alert-success offer-compact-alert offer-accepted-alert">
                                <i class="material-icons">check_circle</i>
                                <span>{l s='Accepted — You can add this product to your cart.' mod='productpriceconfig'}</span>
                            </div>
                        {elseif $p.product_status == 'rejected'}
                            <div class="alert alert-danger offer-compact-alert">
                                <i class="material-icons">cancel</i>
                                <span>{l s='Rejected — You can request a new price.' mod='productpriceconfig'}</span>
                            </div>
                        {elseif $p.product_status == 'expired' || $is_expired}
                            <div class="alert alert-warning offer-compact-alert">
                                <i class="material-icons">schedule</i>
                                <span>{l s='Expired — You can request a new offer.' mod='productpriceconfig'}</span>
                            </div>
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>

        {* Overall Actions *}
        {if $has_accepted && !$is_expired}
            <div class="offer-add-cart-row">
                <button type="button" id="btn-add-to-cart" class="btn btn-primary btn-lg" data-id-offer="{$offer.id_offer}">
                    <i class="material-icons">shopping_cart</i> {l s='Add Accepted Products to Cart' mod='productpriceconfig'}
                </button>
            </div>
        {/if}

        {* History Timeline *}
        {if $history}
            <div class="offer-history-section">
                <h3 class="offer-section-title"><i class="material-icons">history</i> {l s='History Timeline' mod='productpriceconfig'}</h3>
                <div class="offer-history-scroll">
                    <div class="timeline">
                        {foreach from=$history item=h}
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <div class="timeline-top">
                                        <span class="timeline-date">{$h.date_add}</span>
                                        <span class="badge badge-info timeline-action">{$h.action_label}</span>
                                        {if $h.actor}
                                            <span class="timeline-actor text-muted small">by {$h.actor}</span>
                                        {/if}
                                    </div>
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
            </div>
        {/if}

        {* Notification area *}
        <div id="offer-notification-area" class="offer-notification-area" style="display:none;"></div>

        <input type="hidden" id="ajax-url" value="{$ajax_url}" />
        <input type="hidden" id="my-offers-url" value="{$my_offers_url}" />
        <input type="hidden" id="cart-url" value="{$cart_url}" />
        <input type="hidden" id="is-logged" value="{if $is_logged}1{else}0{/if}" />
    </div>
{/block}
