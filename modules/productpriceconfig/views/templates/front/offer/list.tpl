{extends file='page.tpl'}

{block name='page_title'}
    <span class="page-title">{l s='My Offers' mod='productpriceconfig'}</span>
{/block}

{block name='page_content_container'}
    <div id="offer-list-page" class="offer-page-container">

        {* Header row: title left, New Quotation button right *}
        <div class="offer-list-header-row">
            <div>
                <h2 class="offer-list-title">{l s='My Quotations' mod='productpriceconfig'}</h2>
                <p class="offer-list-subtitle text-muted">
                    {l s='Review your quotations, accept offers, and reorder.' mod='productpriceconfig'}
                </p>
            </div>
            <div class="offer-list-actions">
                <a href="{$create_url}" class="btn btn-primary btn-lg offer-new-btn">
                    <i class="material-icons">add</i>
                    <span>{l s='New Quotation' mod='productpriceconfig'}</span>
                </a>
            </div>
        </div>

        {if $offers}
            <div class="offer-list-grid">
                {foreach from=$offers item=offer}
                    <div class="card offer-list-card{if $offer.is_expired} offer-card-expired{/if}{if $offer.status == 'accepted'} offer-card-accepted{/if}{if $offer.status == 'converted_to_order'} offer-card-converted{/if}">
                        <div class="card-body offer-card-body">
                            <div class="offer-card-top">
                                {* Thumbnail - individual images with borders *}
                                <div class="offer-card-thumb">
                                    {if $offer.thumbnails}
                                        {foreach from=$offer.thumbnails item=thumb}
                                            <img src="{$thumb}" alt="" class="img-thumbnail" style="width: 50px; height: 50px; " />
                                        {/foreach}
                                    {else}
                                        <i class="material-icons offer-thumb-placeholder">image</i>
                                    {/if}
                                </div>

                                {* Info *}
                                <div class="offer-card-info">
                                    <h5 class="card-title offer-card-ref">
                                        <a href="{$offer.detail_url}">{$offer.reference}</a>
                                    </h5>
                                    {if $offer.title}
                                        <p class="offer-card-title-text text-muted">{$offer.title}</p>
                                    {/if}
                                    {if $offer.product_names}
                                        <p class="offer-card-products" title="{$offer.product_names}">
                                            <i class="material-icons">inventory_2</i>
                                            {$offer.product_names}
                                        </p>
                                    {/if}
                                    <div class="offer-card-meta">
                                        <span class="offer-meta-item">
                                            <i class="material-icons">widgets</i>
                                            {$offer.total_products} {l s='product(s)' mod='productpriceconfig'}
                                        </span>
                                        <span class="offer-meta-item">
                                            <i class="material-icons">event</i>
                                            {$offer.date_add}
                                        </span>
                                        {if $offer.expire_date}
                                            <span class="offer-meta-item">
                                                <i class="material-icons">schedule</i>
                                                {$offer.expire_date}
                                            </span>
                                        {/if}
                                    </div>
                                </div>

                                {* Status + actions *}
                                <div class="offer-card-right">
                                    <span class="badge offer-status-badge badge-{$offer.status}">
                                        {$offer.status_label}
                                    </span>
                                    <div class="offer-card-btns">
                                        <a href="{$offer.detail_url}" class="btn btn-sm btn-primary" title="{l s='View' mod='productpriceconfig'}">
                                            <i class="material-icons">visibility</i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-print-pdf-list" data-id-offer="{$offer.id_offer}" title="{l s='Print PDF' mod='productpriceconfig'}">
                                            <i class="material-icons">picture_as_pdf</i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-duplicate-offer" data-id-offer="{$offer.id_offer}" title="{l s='Duplicate' mod='productpriceconfig'}">
                                            <i class="material-icons">content_copy</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        {else}
            <div class="alert alert-info text-center offer-empty-state">
                <i class="material-icons offer-empty-icon">description</i>
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
