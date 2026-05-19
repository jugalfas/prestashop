{*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    Prestaeg <infos@presta.com>
* @copyright Prestaeg
* @version   1.0.0
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{extends 'page.tpl'}

{block name='_partials/notifications'}{/block}

{block name='page_title'}
  {if $allow_upload_no_order == 0}
    {l s='My quote' mod='quoterequest'}
  {else}
    {l s='My quotes' mod='quoterequest'}
  {/if}
  <a href="{_PS_BASE_URL_}{__PS_BASE_URI__}module/quoterequest/frontquoterequest"
    style="display: flex;justify-content: flex-end;margin-bottom: 15px;">
    <button class="btn btn-primary">Add Quote</button>
  </a>
{/block}

{block name='page_content'}
  <link rel="stylesheet" media="print" href="https://{__PS_BASE_URI__}modules/quoterequest/views/css/print.css">

  {foreach $quotes as $quote}
    <div class="quote-wrapper" id="quote-{$quote.id_quote}">
      <div class="quote-top-section" data-quote_id="{$quote.id_quote}">
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="date-heading-area">

              <div class="quote-top-text-1">
                <span class="quote-top-number"> {l s='Quote' mod='quoterequest'} :</span>
                <span class="quote-pending">#{$quote['id_quote']} {$quote['title']}</span>
                <span class="quote-top-date">[ <span class="quote-green">{$quote['status_text']} </span> ]</span>
              </div>

              <div class="date-area">
                {l s='Posted on' mod='quoterequest'} : {$quote['date_add']}
              </div>
            </div>

          </div>
        </div>
      </div>
      <div class="quote-table-setion">
        <div class="table-responsive">
          <table class="table quote-table" id="product_review_table">
            <thead>
              <tr>
                <th> {l s='Item' mod='quoterequest'}</th>
                <th> {l s='Product details' mod='quoterequest'}</th>
                <th> {l s='Product configure' mod='quoterequest'}</th>
                <th> {l s='Files' mod='quoterequest'}</th>
              </tr>
            </thead>
            <tbody id="product_review_table_body">
              {foreach $quote['products'] as $product}
                <tr>
                  <td>
                    <div class="list-product-img">
                      <img src="{$product['image_link']}">
                    </div>
                  </td>
                  <td>
                    <div class="row">
                      <div class="col-lg-4 col-md-12 col-xs-12 quote-table-heading"> {l s='Product' mod='quoterequest'} </div>
                      <div class="col-lg-8 col-md-12 col-xs-12">: {$product['product_title']}</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-4 col-md-12 col-xs-12 quote-table-heading"> {l s='Price' mod='quoterequest'}</div>
                      <div class="col-lg-8 col-md-12 col-xs-12">:
                        {if isset($product['price']) && $product['price'] != '0.0000'} {$product['price']}
                          <i class="fa fa-check-circle ml-1 fa-1x text-success right-tick-icon"></i>
                        {else} ---
                        {/if}
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-12 col-md-12 col-xs-12 requote-button"><a href="" class="requote_single_product"
                          data-quote_id="{$quote['id_quote']}" data-quote_product_id="{$product['id_quote_product']}"> {l s='Requote 
                          product' mod='quoterequest'} <span class="loader" style="display: none;"><i
                              class="fa fa-spinner fa-spin"></i></span></a>
                      </div>
                    </div>
                  </td>
                  <td>
                    {foreach json_decode($product['details']) as $configuration}
                      <div class="row">
                        {if isset($configuration->additional_information)}
                          <div class="col-lg-4 col-md-12 col-xs-12 quote-table-heading">
                            {l s='Additional Information' mod='quoterequest'}</div>
                          <div class="col-lg-8 col-md-12 col-xs-12">: {$configuration->additional_information|default:'-'}</div>
                        {else}
                          <div class="col-lg-4 col-md-12 col-xs-12 quote-table-heading">{$configuration->variable_name}</div>
                          <div class="col-lg-8 col-md-12 col-xs-12">: {$configuration->valueText|default:'-'}</div>
                        {/if}
                      </div>
                    {/foreach}
                  </td>
                  <td>
                    <div class="list-product-img">
                      {foreach $product['file'] as $file}
                        {if $file|regex_replace:'/.*\.(.*)/':'\1'|lower == 'pdf'}
                          <a href="{_PS_BASE_URL_}{__PS_BASE_URI__}modules/quoterequest/uploads/{$file|basename}" target="_blank"
                            style="display: flex;flex-direction: column;align-items: center;gap: 10px;">
                            <img src="{_PS_BASE_URL_}{__PS_BASE_URI__}modules/quoterequest/views/img/pdf.png" alt="PDF Icon"
                              style="width: 80px; height: 80px;">
                            {$file|basename}
                          </a>
                        {else}
                          <img src="{$file}" alt="Product Image">
                        {/if}
                      {/foreach}
                    </div>
                  </td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        </div>

        <div class="quote-top-link">
          <a href="javascript:void(0);" class="print_btn" data-quote_id="{$quote['id_quote']}"> <i class="fa fa-print "></i>
            {l s='Print' mod='quoterequest'}</a>
          <div class="button-final-area-child">
            <a href="" class="requote_all" id="requote_whole_quote" data-quote_id="{$quote['id_quote']}">
              {l s='Requote' mod='quoterequest'}<span class="loader" style="display: none;"><i
                  class="fa fa-spinner fa-spin"></i></span></a>
          </div>
          <a class="order_quote" data-toggle="modal" data-target="#exampleModal" data-quote_id="{$quote['id_quote']}"
            {if isset($quote['current_status']) && ($quote['current_status'] == 0 || $quote['current_status'] == 4)}
            style="display: none;" {/if}> <i class="fa fa-shopping-cart "></i>
            {l s='Order' mod='quoterequest'}</a>
        </div>
      </div>
    </div>
  {/foreach}







  {* <div class="quote-top-section" data-quote_id="5528">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="date-heading-area">

          <div class="quote-top-text-1">
            <span class="quote-top-number">Order :</span>
            <span class="quote-pending">Product Alt Layout</span>
            <span class="quote-top-date">[ <span class="quote-red">Betaling ontvangen </span> ]</span>
          </div>

          <div class="date-area">
            Date : 2024-04-18 15:52:29
          </div>
        </div>

      </div>
    </div>
  </div>


  <div class="quote-table-setion">
    <div class="table-responsive">
      <table class="table quote-table" id="product_review_table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Product Details</th>
            <th>Additional Information</th>
            <th>Files</th>
          </tr>
        </thead>
        <tbody id="product_review_table_body">
          <tr>
            <td>
              <div class="list-product-img">
                <img
                  src="https://ecomphotogifts.printguru.com.sg/wp-content/uploads/2018/10/Screenshot-2018-10-24-15.01.06.png">
              </div>
            </td>
            <td>
              <div class="row">
                <div class="col-lg-4 col-md-12 col-xs-12 quote-table-heading">Product </div>
                <div class="col-lg-8 col-md-12 col-xs-12">: Simple Product_Alt Layout 1_Addon</div>
              </div>
              <div class="row">
                <div class="col-lg-4 col-md-12 col-xs-12 quote-table-heading">Quantity1 </div>
                <div class="col-lg-8 col-md-12 col-xs-12">: 100 : ---<i
                    class="fa fa-check-circle ml-1 fa-1x text-success right-tick-icon" style="display: none"></i></div>
              </div>
              <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12 requote-button"><a href="#!" id="requote_single_product"
                    data-quote_id="5528" data-quote_product_id="42">Requote product <span class="loader"
                      style="display: none;"><i class="fa fa-spinner fa-spin"></i></span></a> </div>
              </div>
            </td>
            <td>
              <div class="row">
                <div class="col-lg-4 col-md-12 col-xs-12 quote-table-heading">finishing</div>
                <div class="col-lg-8 col-md-12 col-xs-12">: Matt</div>
              </div>
              <div class="row">
                <div class="col-lg-4 col-md-12 col-xs-12 quote-table-heading">Additional information</div>
                <div class="col-lg-8 col-md-12 col-xs-12">: test</div>
              </div>
            </td>
            <td>
              <div class="list-product-img">
                <img
                  src="https://ecomphotogifts.printguru.com.sg/wp-content/uploads/2018/10/Screenshot-2018-10-24-15.01.06.png">
              </div>
            </td>
          </tr>

          <tr>
            <td>
              <div class="list-product-img">
                <img
                  src="https://ecomphotogifts.printguru.com.sg/wp-content/uploads/2018/10/Screenshot-2018-10-24-15.01.06.png">
              </div>
            </td>
            <td>
              <div class="row">
                <div class="col-lg-4 col-md-12 col-xs-12 quote-table-heading">Product </div>
                <div class="col-lg-8 col-md-12 col-xs-12">: Simple Product_Alt Layout 1_Addon</div>
              </div>
              <div class="row">
                <div class="col-lg-4 col-md-12 col-xs-4 quote-table-heading">Quantity1 </div>
                <div class="col-lg-8 col-md-12 col-xs-8">: 100 : ---<i
                    class="fa fa-check-circle ml-1 fa-1x text-success right-tick-icon" style="display: none"></i></div>
              </div>
              <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12 requote-button"><a href="#!" id="requote_single_product"
                    data-quote_id="5528" data-quote_product_id="42">Requote product <span class="loader"
                      style="display: none;"><i class="fa fa-spinner fa-spin"></i></span></a> </div>
              </div>
            </td>
            <td>
              <div class="row">
                <div class="col-lg-4 col-md-12 col-xs-4 quote-table-heading">finishing</div>
                <div class="col-lg-8 col-md-12 col-xs-8">: Matt</div>
              </div>
              <div class="row">
                <div class="col-lg-4 col-md-12 col-xs-12 quote-table-heading">Additional information</div>
                <div class="col-lg-8 col-md-12 col-xs-12">: test</div>
              </div>
            </td>
            <td>
              <div class="list-product-img">
                <img
                  src="https://ecomphotogifts.printguru.com.sg/wp-content/uploads/2018/10/Screenshot-2018-10-24-15.01.06.png">
              </div>
            </td>
          </tr>



        </tbody>
      </table>
    </div>



    <div class="quote-top-link">
      <a href="#" class="print_btn"> <i class="fa fa-print "></i> Print</a>
      <a class="order_quote" data-toggle="modal" data-target="#exampleModal"> <i class="fa fa-shopping-cart "></i>
        Order</a>
      <div class="button-final-area-child">
        <a href="#" class="requote_all" id="requote_whole_quote">Requote</a>
      </div>
    </div>


  </div> *}

  <!-- Modal -->
  <div class="modal fade quote_order_modal" id="exampleModal" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="loader" style="justify-content: center; display: flex; margin: 50px;">
          <i class="fa fa-spinner fa-spin" style="font-size: 40px"></i>
        </div>
        <div class="modal-body" style="display: none;">
          <div class="quote-top-section quote-top-section1">
            <div class="row">
              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="title-popup-area"><span>Order</span></div>
              </div>
              <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
                <div class="quote-top-text-1">
                  <span class="quote-top-number">Quote :</span>
                  <span class="quote-pending quote_title">Product</span>
                  <span class="quote-top-date">[ <span class="quote-red quote_status">Betaling ontvangen </span> ]</span>
                </div>
                <div class="quote-note-area">
                  Please note : products may require to upload files on cart page or order details page.
                </div>
              </div>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="main_content">
            <div class="quoted-box-section">
              <div class="quoted-box-area">
                <h3 class="fusion-responsive-typography-calculated">
                  <div class="product_popup-heading">
                    <input type="checkbox">
                    <span style="font-weight: 700;">
                      PhotoPrint_Variation Products_addon_Hide Mobile
                    </span>
                    x
                    <span style="font-weight: 700;"> 200</span>
                  </div>
                  <div class="product_price_section">
                    <div class="product_price">
                      $20
                    </div>
                  </div>
                </h3>
              </div>
              <div class="quoted-box-option-area">
                <img class="product_image"
                  src="https://ecomphotogifts.printguru.com.sg?wcuf_file_name=dropbox:/ecomphotogifts.printguru.com.sg/5527/3787-0/quotation_flow_99314.png&amp;wcuf_image_name=quotation_flow_99314.png&amp;wcuf_is_zip=false&amp;wcuf_order_id=5527&amp;preview_type=generic&amp;rand=generic">
              </div>
            </div>
            <div class="quoted-box-section">
              <div class="quoted-box-area">
                <h3 class="fusion-responsive-typography-calculated">
                  <div class="product_popup-heading">
                    <input type="checkbox">
                    PhotoPrint_Variation Products_addon_Hide Mobile x 200
                  </div>
                  <div class="product_price_section">
                    <div class="product_price">
                      $20
                    </div>
                  </div>
                </h3>
              </div>
              <div class="quoted-box-option-area">
                <img class="wcuf_file_preview_list_item_image"
                  src="https://ecomphotogifts.printguru.com.sg?wcuf_file_name=dropbox:/ecomphotogifts.printguru.com.sg/5527/3787-0/quotation_flow_99314.png&amp;wcuf_image_name=quotation_flow_99314.png&amp;wcuf_is_zip=false&amp;wcuf_order_id=5527&amp;preview_type=generic&amp;rand=generic">
              </div>
            </div>

            <div class="quoted-amount-area">
              <span class="quote-heading">Quoted Amount : </span> <span class="quote-price">--</span>
            </div>
            <div class="ammount-area">
              <!-- <label> <input type="checkbox"> I agree to Terms of Use </label> -->
              <button class="btn add-to-cart-amm"><i class="fa fa-shopping-cart"></i> Add to cart</button>
            </div>
            <div class="configure-area--infobox-wrapper">
              <div class="trustedshops-insurance">
                <p><img src="https://dev.onlyprint.nl/img/cms/trustedshops_badge_insurance_1.png" width="24" height="24"
                    alt="trustedshops_badge_insurance_1.png">Uw bestelling wordt tot € 100 gratis beschermd door Trusted
                  Shops.</p>
                <p><strong>Geen verzendkosten binnen Nederland. Productietijd: 2-3 werkdagen*</strong></p>
                <p>i Files can be uploaded after
                  ordering* Delivery times for countries can be found <a
                    href="https://dev.onlyprint.nl/flyers-ongevouwen/flyers.html">here</a></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <script>
    $(document).on('click', '.print_btn', function(e) {
      e.preventDefault();
      var quoteId = $(this).data('quote_id');
      var pdfUrl = '{$link->getModuleLink('quoterequest', 'downloadquotepdf', ['id_quote' => ''])|escape:'html':'UTF-8'}' + quoteId;
      window.open(pdfUrl, '_blank');
    });



    $(document).ready(function() {
      $(document).on('click', '.requote_single_product', function(e) {
        e.preventDefault();
        var quote_id = $(this).attr('data-quote_id');
        var quote_product_id = $(this).attr('data-quote_product_id');

        var fd = new FormData();
        fd.append('quote_id', quote_id);
        fd.append('quote_product_id', quote_product_id);
        fd.append('action', 'get_single_quote_data');

        var elem = $(this);
        elem.find('.loader').show();
        elem.prop('disabled', true);
        elem.toggleClass('disabled');
        $.ajax({
          url: '{$ajax_link}',
          type: 'POST',
          data: fd,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(response) {
            elem.find('.loader').hide();
            elem.prop('disabled', false);
            elem.toggleClass('disabled');
            if (response.status) {
              var quote = response.data;
              var storedData = JSON.parse(sessionStorage.getItem('quotes')) || [];

              const request = indexedDB.open("imageDB", 1);

              request.onupgradeneeded = function(event) {
                const db = event.target.result;
                db.createObjectStore("images", { keyPath: "id", autoIncrement: true });
              };

              request.onsuccess = function(event) {
                const db = event.target.result;
                var filePromises = [];

                if (quote.images && quote.images.length > 0) {
                  filePromises = quote.images.map(fileUrl => {
                    return new Promise(async (resolve, reject) => {
                      try {
                        const response = await fetch(fileUrl);
                        const blob = await response.blob();
                        const reader = new FileReader();
                        reader.readAsDataURL(blob);
                        reader.onload = function(e) {
                          const imageData = e.target.result;
                          const transaction = db.transaction("images", "readwrite");
                          const store = transaction.objectStore("images");
                          store.add({ fileData: imageData }).onsuccess = function(event) {
                            resolve(event.target.result);
                          };
                          store.add({ fileData: imageData }).onerror = function(error) {
                            reject(error);
                          };
                        };
                        reader.onerror = function(error) {
                          reject(error);
                        };
                      } catch (error) {
                        reject(error);
                      }
                    });
                  });
                }

                Promise.all(filePromises).then(function(imageIDs) {
                  quote.images = imageIDs; // Store image IDs in the quote object
                  storedData.push(quote);
                  sessionStorage.setItem('quotes', JSON.stringify(storedData));
                  window.location.href = response.redirect_url;
                }).catch(function(error) {
                  console.log("Error processing images: ", error);
                });
              };

              request.onerror = function(event) {
                console.error("Error opening IndexedDB:", event.target.errorCode);
              };
            } else {
              console.error('Error:', response.message);
            }
          },
          error: function(xhr, status, error) {
            console.error('Error: ' + error);
          }
        });
      });

      $(document).on('click', '.requote_all', function(e) {
        e.preventDefault();
        var quote_id = $(this).attr('data-quote_id');

        var fd = new FormData();
        fd.append('quote_id', quote_id);
        fd.append('action', 'get_quote_data');

        var elem = $(this);
        elem.find('.loader').show();
        elem.prop('disabled', true);
        elem.toggleClass('disabled');

        $.ajax({
          url: '{$ajax_link}',
          type: 'POST',
          data: fd,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(response) {
            elem.find('.loader').hide();
            elem.prop('disabled', false);
            elem.toggleClass('disabled');

            if (response.status) {
              var quote = response.data;
              var storedData = JSON.parse(sessionStorage.getItem('quotes')) || [];

              const request = indexedDB.open("imageDB", 1);

              request.onupgradeneeded = function(event) {
                const db = event.target.result;
                db.createObjectStore("images", { keyPath: "id", autoIncrement: true });
              };

              request.onsuccess = function(event) {
                const db = event.target.result;
                var allFilePromises = [];

                // Iterate over each quote in the array
                quote.forEach(quoteItem => {
                  if (quoteItem.images && quoteItem.images.length > 0) {
                    const filePromisesForQuote = quoteItem.images.map(fileUrl => {
                      return new Promise(async (resolve, reject) => {
                        try {
                          const response = await fetch(fileUrl);
                          const blob = await response.blob();
                          const reader = new FileReader();
                          reader.readAsDataURL(blob);
                          reader.onload = function(e) {
                            const imageData = e.target.result;
                            const transaction = db.transaction("images", "readwrite");
                            const store = transaction.objectStore("images");
                            store.add({ fileData: imageData }).onsuccess = function(
                              event) {
                              resolve(event.target.result);
                            };
                            store.add({ fileData: imageData }).onerror = function(error) {
                              reject(error);
                            };
                          };
                          reader.onerror = function(error) {
                            reject(error);
                          };
                        } catch (error) {
                          reject(error);
                        }
                      });
                    });
                    allFilePromises.push(Promise.all(filePromisesForQuote).then(imageIDs => {
                      quoteItem.images = imageIDs; // Update the quoteItem's file array with IDs
                  }));
                }
              });

              Promise.all(allFilePromises).then(function() {
                if (storedData && Object.keys(storedData).length == 0) {
                  storedData = quote; // If sessionStorage is empty, store the entire quote array
                } else {
                  storedData.push(...quote); // Push all new quotes to existing data
                }

                sessionStorage.setItem('quotes', JSON.stringify(storedData));
                window.location.href = response.redirect_url;
              }).catch(function(error) {
                console.log("Error processing images: ", error);
              });
            };

            request.onerror = function(event) {
              console.error("Error opening IndexedDB:", event.target.errorCode);
            };
          } else {
            console.error('Error:', response.message);
          }
        },
        error: function(xhr, status, error) {
          console.error('Error: ' + error);
        }
      });
    });

    $(document).on('click', '.order_quote', function(e) {
      var quote_id = $(this).attr('data-quote_id');
      var fd = new FormData();
      fd.append('quote_id', quote_id);
      fd.append('action', 'get_quote_data_for_order');
      $.ajax({
        url: '{$ajax_link}',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
          if (response.status) {
            $(".quote_order_modal .loader").hide();
            $(".quote_order_modal .modal-body").html(response.html);
            $(".quote_order_modal .modal-body").show();
          } else {
            console.error('Error:', response.message);
          }
        },
        error: function(xhr, status, error) {
          console.error('Error: ' + error);
        }
      });

    });

    $(document).on('change', '.product_check', function(e) {
      e.preventDefault();

      var total_quoted_amount = 0; // Initialize the variable as a number

      $('.product_check').each(function(index, element) {
        if ($(this).is(':checked')) {
          // Add the price to the total, ensuring it's treated as a number
            total_quoted_amount += parseFloat($(this).data('price'));
          }
        });

        // Convert the total to a fixed decimal string and update the HTML
        $('.quote-price').html("$ " + total_quoted_amount.toFixed(2));

      })
      $(document).on('click', '.add-to-cart-amm', function(e) {
        $(this).find('i').toggleClass('fa-spinner fa-spin');
        $(this).toggleClass('disabled');
        e.preventDefault();

        var id_quote_product = [];
        var id_products = [];

        var requestData = {};
        $('.product_check').each(function(index, element) {
          if ($(this).is(':checked')) {
            id_quote_product.push($(this).val());
            id_products.push($(this).attr('data-product-id'));
          }
        });
        var fd = new FormData();
        fd.append('id_quote_product', id_quote_product);
        fd.append('quote_id', $(this).attr('data-quote-id'));
        fd.append('action', 'add_to_cart');
        $.ajax({
          type: "POST",
          url: '{$ajax_link}',
          dataType: "json",
          async: false,
          data: fd,
          processData: false,
          contentType: false,
          success: function(r) {
            // return false;
            $(".main-add-to-cart").removeClass("spinning");
            console.log(r)
            if (!r.error) {
              var firstKey = Object.keys(r)[0];
              id_customization = r[firstKey].id_customization;
              console.log("inside ajax", id_customization);
            }
          },
          error: function(r) {
            console.warn($(r.responseText).text() || r.responseText);
          }
        });

        prestashop.blockcart = prestashop.blockcart || {};

        var showModal =
          prestashop.blockcart.showModal ||
          function(modal) {
            var $body = $("body");
            $body.append(modal);
            $body.one("click", "#blockcart-modal", function(event) {
              if (event.target.id === "blockcart-modal") {
                $(event.target).remove();
              }
            });
          };

        var refreshURL = $(".blockcart").data("refresh-url");

        requestData = {
          id_product_attribute: 0,
          id_product: id_products[0],
          action: "add-to-cart",
          id_customization: id_customization
        };
        // requestData = [{
        //   id_product_attribute: 0,
        //   id_product: 43,
        //   action: "add-to-cart",
        //   id_customization: 932
        // },{
        //   id_product_attribute: 0,
        //   id_product: 48,
        //   action: "add-to-cart",
        //   id_customization: 933
        // }];

        refreshURL = "https:" + refreshURL;

        $.post(refreshURL, requestData)
          .then(function(resp) {
            $(".blockcart").replaceWith($(resp.preview).find(".blockcart"));
            if (resp.modal) {
              $(".quote_order_modal").modal("hide");
              showModal(resp.modal);
              $(this).addClass("disable");

              setTimeout(function() {
                // window.location = getCartUrl;
              }, 500);
            }
          })
          .fail(function(resp) {
            prestashop.emit("handleError", {
              eventType: "updateShoppingCart",
              resp: resp
            });
          });
      })
    });
  </script>


{/block}