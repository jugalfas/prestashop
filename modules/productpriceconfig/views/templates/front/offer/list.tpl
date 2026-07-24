{extends file='page.tpl'}

{block name='page_title'}
    <span class="page-title">{l s='My Offers' mod='productpriceconfig'}</span>
{/block}

{block name='page_content_container'}
    <div id="offer-list-page" class="offer-page-container">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-6">
                <h2>{l s='My Quotations' mod='productpriceconfig'}</h2>
            </div>
            <div class="col-md-6 text-right">
                <a href="{$create_url}" class="btn btn-primary btn-lg">
                    <i class="material-icons">add</i> {l s='New Quotation' mod='productpriceconfig'}
                </a>
            </div>
        </div>

        {if $offers}
            <div class="offer-list-grid">
                {foreach from=$offers item=offer}
                    <div class="card offer-list-card{if $offer.is_expired} offer-card-expired{/if}{if $offer.status == 'accepted'} offer-card-accepted{/if}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="offer-thumbnails">
                                        {foreach from=$offer.thumbnails item=thumb}
                                            <img src="{$thumb}" alt="" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;" />
                                        {/foreach}
                                        {if !$offer.thumbnails}
                                            <i class="material-icons" style="font-size: 48px; color: #ddd;">image</i>
                                        {/if}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <h5 class="card-title">
                                        <a href="{$offer.detail_url}">{$offer.reference}</a>
                                    </h5>
                                    {if $offer.title}
                                        <p class="text-muted small">{$offer.title}</p>
                                    {/if}
                                    <p class="small">
                                        <strong>{l s='Products:' mod='productpriceconfig'}</strong> {$offer.total_products}
                                    </p>
                                    <p class="small">
                                        <strong>{l s='Created:' mod='productpriceconfig'}</strong> {$offer.date_add}
                                    </p>
                                    {if $offer.expire_date}
                                        <p class="small">
                                            <strong>{l s='Expires:' mod='productpriceconfig'}</strong> {$offer.expire_date}
                                        </p>
                                    {/if}
                                </div>
                                <div class="col-md-4 text-right">
                                    <span class="badge badge-{if $offer.status == 'accepted'}success{elseif $offer.status == 'rejected' || $offer.status == 'cancelled' || $offer.status == 'expired'}danger{elseif $offer.status == 'price_offered'}warning{else}info{/if} offer-status-badge">
                                        {$offer.status_label}
                                    </span>
                                    <div style="margin-top: 10px;">
                                        <a href="{$offer.detail_url}" class="btn btn-sm btn-primary">
                                            <i class="material-icons">visibility</i> {l s='View' mod='productpriceconfig'}
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-duplicate-offer" data-id-offer="{$offer.id_offer}">
                                            <i class="material-icons">content_copy</i> {l s='Duplicate' mod='productpriceconfig'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        {else}
            <div class="alert alert-info text-center">
                <p>{l s='You have no quotations yet.' mod='productpriceconfig'}</p>
                <a href="{$create_url}" class="btn btn-primary">
                    <i class="material-icons">add</i> {l s='Create Your First Quotation' mod='productpriceconfig'}
                </a>
            </div>
        {/if}

        <input type="hidden" id="ajax-url" value="{$ajax_url}" />
        <input type="hidden" id="my-offers-url" value="{$create_url}" />
    </div>
{/block}
