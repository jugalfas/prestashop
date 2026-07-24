{extends file='page.tpl'}

{block name='page_title'}
    <span class="page-title">{l s='Create Quotation' mod='productpriceconfig'}</span>
{/block}

{block name='page_content_container'}
    <div id="offer-create-page" class="offer-page-container">

        {* Guest login prompt *}
        {if !$is_logged}
            <div id="guest-info-bar" class="alert alert-info" style="margin-bottom: 20px;">
                <div class="row">
                    <div class="col-md-6">
                        <label for="guest_name">{l s='Your Name' mod='productpriceconfig'}</label>
                        <input type="text" id="guest_name" class="form-control" value="{$guest_name}" placeholder="{l s='Enter your name' mod='productpriceconfig'}" />
                    </div>
                    <div class="col-md-6">
                        <label for="guest_email">{l s='Your Email' mod='productpriceconfig'}</label>
                        <input type="email" id="guest_email" class="form-control" value="{$guest_email}" placeholder="{l s='Enter your email' mod='productpriceconfig'}" />
                    </div>
                </div>
            </div>
        {/if}

        {* Large Search Box *}
        <div class="offer-search-section">
            <div class="search-box-wrapper">
                <div class="input-group search-input-group">
                    <span class="input-group-addon search-icon">
                        <i class="material-icons">search</i>
                    </span>
                    <input type="text" id="product-search-input"
                           class="form-control"
                           placeholder="{l s='Search Product by Name, Reference or SKU...' mod='productpriceconfig'}"
                           autocomplete="off" />
                    <span class="input-group-btn">
                        <button class="btn btn-default search-clear-btn" type="button" id="search-clear-btn" style="display:none;">
                            <i class="material-icons">close</i>
                        </button>
                    </span>
                </div>
                <div id="search-results-dropdown" class="search-results-dropdown" style="display:none;"></div>
            </div>
        </div>

        {* Selected Product Card *}
        <div id="selected-product-card" class="selected-product-card" style="display:none;">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img id="selected-product-image" src="" alt="" class="img-fluid rounded" style="max-height: 80px;" />
                        </div>
                        <div class="col-md-7">
                            <h5 id="selected-product-name" class="card-title"></h5>
                            <p id="selected-product-reference" class="text-muted small"></p>
                            <p id="selected-product-description" class="card-text small"></p>
                            <p id="selected-product-category" class="badge badge-info"></p>
                        </div>
                        <div class="col-md-3 text-right">
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btn-remove-product">
                                <i class="material-icons">close</i> {l s='Remove' mod='productpriceconfig'}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-change-product">
                                <i class="material-icons">swap_horiz</i> {l s='Change' mod='productpriceconfig'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {* Main Layout: Configurator + Quote Cart *}
        <div class="row offer-main-row">
            {* Left Column: Configurator (60%) *}
            <div class="col-lg-7 col-md-12">
                <div id="configurator-section" class="configurator-section">
                    <div id="configurator-placeholder" class="configurator-placeholder">
                        <i class="material-icons" style="font-size: 64px; color: #ccc;">add_shopping_cart</i>
                        <p class="text-muted">{l s='Search and select a product to start configuring your quotation.' mod='productpriceconfig'}</p>
                    </div>
                    <div id="configurator-content" style="display:none;"></div>
                </div>
            </div>

            {* Right Column: Quote Cart (40%) *}
            <div class="col-lg-5 col-md-12">
                <div class="quote-cart-section">
                    <div class="card quote-cart-card">
                        <div class="card-header quote-cart-header">
                            <h4 class="mb-0">
                                <i class="material-icons">shopping_cart</i>
                                {l s='Quote Cart' mod='productpriceconfig'}
                            </h4>
                            <span id="quote-cart-count" class="badge badge-light">0</span>
                        </div>
                        <div class="card-body quote-cart-body" id="quote-cart-body">
                            <div id="quote-cart-empty" class="quote-cart-empty">
                                <p class="text-muted text-center">{l s='No products added yet.' mod='productpriceconfig'}</p>
                            </div>
                            <div id="quote-cart-items"></div>
                        </div>
                        <div class="card-footer quote-cart-footer">
                            {* Send Quotation Form *}
                            <div id="send-quotation-section">
                                <div class="form-group">
                                    <label for="quote-title">{l s='Quotation Title' mod='productpriceconfig'}</label>
                                    <input type="text" id="quote-title" class="form-control" placeholder="{l s='Enter a title for this quotation' mod='productpriceconfig'}" />
                                </div>
                                <div class="form-group">
                                    <label for="quote-customer-note">{l s='Customer Note' mod='productpriceconfig'}</label>
                                    <textarea id="quote-customer-note" class="form-control" rows="2" placeholder="{l s='Add any notes for the admin...' mod='productpriceconfig'}"></textarea>
                                </div>
                                <button type="button" id="btn-submit-offer" class="btn btn-primary btn-block btn-lg" disabled>
                                    <i class="material-icons">send</i> {l s='Send Quotation' mod='productpriceconfig'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {* Notification area *}
        <div id="offer-notification-area" class="offer-notification-area" style="display:none;"></div>

        {* Hidden fields *}
        <input type="hidden" id="current-id-offer" value="0" />
        <input type="hidden" id="current-id-product" value="0" />
        <input type="hidden" id="ajax-url" value="{$ajax_url}" />
        <input type="hidden" id="my-offers-url" value="{$my_offers_url}" />
        <input type="hidden" id="is-logged" value="{if $is_logged}1{else}0{/if}" />
        <input type="hidden" id="default-validity-days" value="{$default_validity_days}" />
    </div>
{/block}
