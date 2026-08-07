{extends file='page.tpl'}

{block name='page_title'}
    <span class="page-title">{l s='Create Quotation' mod='productpriceconfig'}</span>
{/block}

{block name='page_content_container'}
    <div id="offer-create-page" class="offer-page-container">

        {* Guest login prompt *}
        {if !$is_logged}
            <div id="guest-info-bar" class="alert alert-info ppc-guest-bar">
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

        {* Search Section *}
        <div class="offer-search-section">
            <div class="search-box-wrapper">
                <div class="offer-search-input-wrap" id="offer-search-input-wrap">
                    <i class="material-icons offer-search-icon" id="offer-search-icon">search</i>
                    <input type="text" id="product-search-input"
                           class="form-control offer-search-input"
                           placeholder="{l s='Search Product by Name, Reference or SKU...' mod='productpriceconfig'}"
                           autocomplete="off" />
                    <button type="button" class="offer-search-clear" id="search-clear-btn" style="display:none;">
                        <i class="material-icons">close</i>
                    </button>

                    {* Selected product preview inside search box *}
                    <div id="search-selected-preview" class="search-selected-preview" style="display:none;">
                        <img id="ssp-image" src="" alt="" class="ssp-thumb" />
                        <div class="ssp-info">
                            <div id="ssp-name" class="ssp-name"></div>
                            <div id="ssp-desc" class="ssp-desc"></div>
                        </div>
                        <button type="button" class="ssp-clear" id="ssp-clear-btn" title="{l s='Clear selection' mod='productpriceconfig'}">
                            <i class="material-icons">close</i>
                        </button>
                    </div>
                </div>
                <div id="search-results-dropdown" class="search-results-dropdown" style="display:none;"></div>
            </div>

            {* Recently Selected Products *}
            <div id="recent-products-section" class="recent-products-section" style="display:none;">
                <div class="recent-products-title">
                    <i class="material-icons">history</i>
                    <span>{l s='Recently Selected' mod='productpriceconfig'}</span>
                </div>
                <div id="recent-products-list" class="recent-products-list"></div>
            </div>

            {* Custom Quote Product Request *}
            {if $custom_quote_product}
                <div class="custom-quote-chip-section">
                    <div class="custom-quote-divider">
                        <span>{l s='OR' mod='productpriceconfig'}</span>
                    </div>
                    <button type="button" id="btn-custom-quote-product" class="custom-quote-chip" data-id-product="{$custom_quote_product.id_product}">
                        <span class="cqc-thumb">
                            {if $custom_quote_product.image}
                                <img src="{$custom_quote_product.image}" alt="" />
                            {else}
                                <i class="material-icons">add_circle_outline</i>
                            {/if}
                        </span>
                        <span class="cqc-label">{l s='Request Custom Product' mod='productpriceconfig'}</span>
                        <span class="cqc-info-icon" data-toggle="tooltip" data-placement="top" title="{$custom_quote_tooltip|escape:'html':'UTF-8'}">
                            <i class="material-icons">info_outline</i>
                        </span>
                    </button>
                </div>
            {/if}
        </div>

        {* Main Layout: Configurator + Quote Cart *}
        <div class="row offer-main-row">
            {* Left Column: Configurator (60%) *}
            <div class="col-lg-7 col-md-12">
                <div id="configurator-section" class="configurator-section">
                    {* Selected Product Card — at top of configurator, before steps *}
                    <div id="selected-product-card" class="selected-product-card" style="display:none;">
                        <div class="card spc-card">
                            <div class="spc-body">
                                <div class="spc-image-wrap">
                                    <img id="selected-product-image" src="" alt="" class="spc-image" />
                                </div>
                                <div class="spc-info">
                                    <h5 id="selected-product-name" class="spc-title"></h5>
                                    <p id="selected-product-reference" class="spc-ref text-muted"></p>
                                    <p id="selected-product-description" class="spc-desc"></p>
                                </div>
                                <div class="spc-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary spc-btn" id="btn-change-product">
                                        <i class="material-icons">swap_horiz</i> {l s='Change Product' mod='productpriceconfig'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="configurator-placeholder" class="configurator-placeholder">
                        <i class="material-icons config-placeholder-icon">add_shopping_cart</i>
                        <p class="text-muted">{l s='Search and select a product to start configuring your quotation.' mod='productpriceconfig'}</p>
                    </div>
                    <div id="configurator-content" style="display:none;"></div>

                    {* File Upload Section (hidden until configurator loads) *}
                    <div id="offer-upload-section" class="offer-upload-section" style="display:none;">
                        <div class="offer-upload-header">
                            <i class="material-icons">attach_file</i>
                            <span>{l s='Artwork Upload' mod='productpriceconfig'}</span>
                        </div>
                        <div id="offer-drop-zone" class="offer-drop-zone">
                            <i class="material-icons offer-drop-icon">cloud_upload</i>
                            <p>{l s='Drag and drop files here or click to browse' mod='productpriceconfig'}</p>
                            <p class="offer-upload-formats text-muted small">{l s='Supported: PDF, AI, EPS, SVG, PNG, JPG, JPEG, TIFF (max 20MB)' mod='productpriceconfig'}</p>
                            <input type="file" id="offer-file-input" multiple style="display:none;" accept=".pdf,.ai,.eps,.svg,.png,.jpg,.jpeg,.tiff" />
                        </div>
                        <div id="offer-file-list" class="offer-file-list"></div>
                        <div id="offer-upload-error" class="alert alert-danger" style="display:none;"></div>
                    </div>

                    {* Product Note *}
                    <div id="offer-product-note-section" class="offer-product-note-section" style="display:none;">
                        <div class="form-group">
                            <label for="offer-product-note"><i class="material-icons">note</i> {l s='Printing Instructions / Customer Notes' mod='productpriceconfig'}</label>
                            <textarea id="offer-product-note" class="form-control" rows="3" placeholder="{l s='Enter special printing instructions or notes for this product...' mod='productpriceconfig'}"></textarea>
                        </div>
                    </div>

                    {* Add To Quote Cart Button *}
                    <div id="offer-add-to-quote-section" class="offer-add-to-quote-section" style="display:none;">
                        <button type="button" id="btn-add-to-quote" class="btn btn-primary btn-lg btn-block">
                            <i class="material-icons">add_shopping_cart</i> {l s='Add To Quote Cart' mod='productpriceconfig'}
                        </button>
                        <div id="offer-config-error" class="alert alert-danger" style="display:none; margin-top: 10px;"></div>
                    </div>
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
                            <span id="quote-cart-count" class="badge badge-primary quote-cart-badge">0</span>
                        </div>
                        <div class="card-body quote-cart-body" id="quote-cart-body">
                            <div id="quote-cart-uploads"></div>
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
        <script type="text/javascript">
            window.ppcCustomerId = '{if $is_logged}{$id_customer|intval}{else}guest_{$guest_email|escape:'javascript'}{/if}';
        </script>
    </div>
{/block}
