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
* @author Prestaeg <infos@presta.com>
    * @copyright Prestaeg
    * @version 1.0.0
    * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
    *}
{extends 'page.tpl'}

{block name='_partials/notifications'}{/block}

{block name='page_title'}
    {l s='Request New Quote' mod='requestquote'}
{/block}

{block name='page_content'}



    <div class="frm-container" id="add-another-product-top">

        <div class="frm-container-1">

            <div class="frm-left-area">

                <div class="frm-products-sec-area">
                    <label> {l s='Please add product' mod='requestquote'}. <span class="note-text">(how it work ?)</span>
                        <div class="tooltip"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <span class="tooltiptext">
                                <p><strong>
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Basic file check:</font>
                                        </font>
                                    </strong></p>
                                <p>
                                    <font style="vertical-align: inherit;">
                                        <font style="vertical-align: inherit;">Included: Adjusting correct size, checking
                                            full color or black and white, feedback on details.</font>
                                    </font>
                                </p>
                                <p><strong>
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Professional file check:</font>
                                        </font>
                                    </strong></p>
                                <p>
                                    <font style="vertical-align: inherit;">
                                        <font style="vertical-align: inherit;">Included: Adjusting the correct format,
                                            checking full colour or black and white, checking bleed, checking CMYK colour
                                            management (colour profiles), checking embedded fonts, feedback on details
                                        </font>
                                    </font>
                                </p>
                                <p>
                                    <font style="vertical-align: inherit;">
                                        <font style="vertical-align: inherit;">Want to know more about the different
                                            document checks? Click </font>
                                    </font><a href="/content/faqs" target="_blank" rel="noreferrer noopener">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">here.</font>
                                        </font>
                                    </a>
                                </p>
                            </span>
                        </div>

                    </label>


                    <div class="dropdown">
                        <button onclick="myFunction()" class="dropbtn">Select Product <i class="fa fa-angle-down"
                                aria-hidden="true"></i></button>
                        <div id="products" class="dropdown-content">
                            <input type="text" placeholder="Search.." id="myInput" onkeyup="filterFunction()">

                            {foreach $products as $category => $product}
                                <div class="category-area">
                                    <h4>{$category}</h4>
                                    {foreach $product as $prod}
                                        <a href="#" data-id_product="{$prod['id_product']}"
                                            class="get_product_details">{$prod['product_name']}</a>
                                    {/foreach}
                                </div>
                            {/foreach}

                        </div>
                    </div>


                </div>


                <div class="step-section-area">
                    <ul class="StepProgress">
                        <li class="StepProgress-item is-done">
                            <strong>Product kiezen</strong>
                            Selecteer het gewenste product uit ons brede assortiment.
                        </li>
                        <li class="StepProgress-item is-done"><strong>Configureren</strong>
                            Configureer het product zoals gewenst; oplage, omvang, kleur of zwart/wit, een- of tweezijdig en
                            afwerking.
                        </li>
                        <li class="StepProgress-item is-done"><strong>Betalen</strong>
                            Betaal snel en veilig met de door je gewenste betaalmethode.
                        </li>
                        <li class="StepProgress-item is-done"><strong>Uploaden</strong>
                            Je kunt dan eenvoudig het bestand uploaden via de uploadbutton.
                        </li>
                    </ul>
                </div>






                <div class="products-section" style="display: none;">

                    <div class="clearfix"></div>

                    <div class="products-section-left">
                        <img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/losbladig-printen.jpg"
                            id="product_image">
                    </div>
                    <div class="products-section-right">
                        <h3 id="product_name">Losbladig printen</h3>
                        <div class="review-area">
                            <div class="star-area">
                                <img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/star.svg">
                                <img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/star.svg">
                                <img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/star.svg">
                                <img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/star.svg">
                                <img src="https://dev.onlyprint.nl/modules/quoterequest/views/img/star.svg">
                            </div>
                            <span id="ratings">4,88</span> (<span id="rating_count"></span>)
                        </div>
                        <p id="product_description">Losbladig printen zonder inbinden? Gratis verzending en snel
                            geregeld bij
                            Onlyprint. Wij printen jouw
                            losbladige collectie in hoge kwaliteit, snel en goedkoop.</p>
                        <a type="button" href="#add-another-product-top" class="table-commentbtn load_configuration">Load
                            configuration <i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
                    </div>




                </div>
                <div class="animation-section" style="display: none;"></div>


                <div class="products-list-section" style="display: none;">

                    <h2>Quote Review</h2>

                    <div class="table-responsive">


                        <table class="table nil-table" id="product_review_table">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Photo</th>
                                    <th style="width: 35%;">Product Details</th>
                                    <th style="width: 40%;">Product configuration</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="product_review_table_body">
                                <tr>
                                    <td>
                                        <div class="product-img">
                                            <img
                                                src="https://dev.onlyprint.nl/modules/quoterequest/views/img/losbladig-printen.jpg">

                                        </div>
                                    </td>
                                    <td>
                                        <div class="product-row">
                                            <div class="product-heading">Product </div>
                                            <div class="product-details">: Change </div>
                                        </div>
                                        <div class="product-row">
                                            <div class="product-heading">Quantity 1</div>
                                            <div class="product-details">: 1</div>
                                        </div>
                                    </td>
                                    <td class="additional-info">
                                        <div class="product-row">
                                            <div class="product-heading">material-type</div>
                                            <div class="product-details">: Art Paper</div>
                                        </div>
                                        <div class="product-row">
                                            <div class="product-heading">Additional Details</div>
                                            <div class="product-details">: aaa</div>
                                        </div>
                                    </td>

                                    <td>
                                        <a href="#delete_quote" class="btn btn-outline-danger btn-sm delete-button"
                                            data-product_id="0"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                    <div class="table-commentbtn-area">
                        <a type="button" href="#add-another-product-top" class="table-commentbtn">Add Another
                            Product</a>
                    </div>



                </div>






                <div class="comment-area" style="display: none;">
                    <div class="lineandtext">
                        <span>OR</span>
                    </div>


                    <div class="frm-area frm-area-2">
                        <input type="text" class="inputText validation" id="quote_title" name="customer_email" required="">
                        <span class="floating-label" for="customer_email">Quote Title</span>
                    </div>

                    <div class="frm-area frm-area-2">
                        {if isset($customer_email)}
                            <input type="text" class="inputText validation" id="customer_email" name="customer_email"
                                required="" value="{$customer_email}" readonly>
                        {else}
                            <input type="text" class="inputText validation" id="customer_email" name="customer_email"
                                required="" value="">
                        {/if}
                        <span class="floating-label" for="customer_email">E-mail</span>
                        <span class="email_error_msg text-danger"></span>
                    </div>
                    <button type="button" class="commentbtn disabled" id="send_quote" disabled><i class="fa fa-paper-plane"
                            aria-hidden="true"></i> Send Quote</button>
                    <span class="error_msg text-danger"></span>
                </div>





            </div>


            <div class="frm-right-area">
                <div class="frm-select-text" id="product_configuration">
                    <div class="" id="loader" onclick="$(this).hide()" style="display: none;">
                        <div class="modal-backdrop-loader" style="
                        position: absolute;
                        top: 0;
                        right: 0;
                        bottom: 0;
                        left: 0;
                        z-index: 1000;
                        background-color: #000;
                        opacity: 0.5;
                    ">
                            <i class="fa fa-spinner fa-spin text-white font-30"
                                style="position: absolute;right: 50%;top: 50%;font-size: 26px;"></i>
                        </div>
                    </div>Please Select The Product
                </div>
            </div>

        </div>
    </div>

    <div class="modal delete-modal" tabindex="-1" id="delete_quote">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Quote</h5>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this quote?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-close" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="delete_quote_btn">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            get_html_for_product_review();
            $(document).on('click', '.get_product_details', function(e) {
                e.preventDefault();
                $('.step-section-area').hide();
                var id_product = $(this).data('id_product');
                $('#products').removeClass('show');
                $('.dropbtn').html($(this).text() + ' <i class="fa fa-angle-down" aria-hidden="true"></i>');
                $('.animation-section').show();
                $('.products-section').hide();
                $('.load_configuration').attr('data-id_product', id_product);
                var fd = new FormData();
                fd.append('id_product', id_product);
                fd.append('action', 'get_product_details');

                $.ajax({
                    url: '{$ajax_link}',
                    type: 'POST', // Change to POST
                    data: fd,
                    processData: false,
                    contentType: false, // Add this line when using FormData
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            $('#product_name').text(response.product_name);
                            $('#product_description').html(response.description);
                            $('#product_image').attr("src", response.image_url);

                            $('#ratings').text(response.ratings);
                            $('#rating_count').text(response.rating_count);
                            $('.products-section').show();
                            $('.animation-section').hide();
                        } else {
                            console.error('Error:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error: ' + error);
                    }
                });
            });
            $(document).on('click', '.load_configuration', function(e) {
                e.preventDefault();
                var id_product = $(this).attr('data-id_product');

                var fd = new FormData();
                fd.append('id_product', id_product);
                fd.append('action', 'load_configuration');

                $("#loader").show();
                $.ajax({
                    url: '{$ajax_link}',
                    type: 'POST', // Change to POST
                    data: fd,
                    processData: false,
                    contentType: false, // Add this line when using FormData
                    dataType: 'json',
                    success: function(response) {
                        $("#loader").hide();
                        if (response.status) {
                            $('#product_configuration').html(response.content);
                        } else {
                            console.error('Error:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error: ' + error);
                    }
                });
            });

            $(document).on('click', '.add_to_quote_list', function(e) {
                e.preventDefault();

                // Select all relevant elements
                var elements = Array.from(document.querySelectorAll('.input_for_url, .btn-select-value'))
                    .filter(element => element.type !== "hidden");

                var storedData = JSON.parse(sessionStorage.getItem('quotes')) || [];
                var currentData = {};

                currentData['configuration'] = [];
                elements.forEach(function(element, index) {
                    var variable_id = element.dataset.variable_id;
                    var variable_name = element.dataset.variableName;
                    var variable_type = element.dataset.type;
                    
                    var product_variable_id = element.dataset.product_variable_id;
                    var formula_name = element.dataset.formulaName;
                    var value, valueText;

                    if (element.classList.contains('input_for_url')) {
                        value = element.value;
                        valueText = value;
                    } else if (element.classList.contains('btn-select-value')) {
                        value = element.value;
                        valueText = element.options[element.selectedIndex].text;
                    }

                    // Add the data to the currentData object using the variable_id as a key
                    currentData['configuration'][index] = {
                        value: value,
                        variable_id: variable_id,
                        variable_type: variable_type,
                        product_variable_id: product_variable_id,
                        variable_name: variable_name,
                        formula_name: formula_name,
                        valueText: valueText
                    };
                });

                currentData['product_name'] = $("#product_name").text();
                currentData['product_image'] = $("#product_image").attr('src');
                currentData['product_id'] = $(".load_configuration").attr('data-id_product');
                currentData['additional_information'] = $(".additional_information").val();

                var files = $(".attachments_images")[0].files;
                currentData['images'] = []; // Initialize as an array to store image IDs

                // Open (or create) IndexedDB database
                const request = indexedDB.open("imageDB", 1);

                request.onupgradeneeded = function(event) {
                    const db = event.target.result;
                    db.createObjectStore("images", { keyPath: "id", autoIncrement: true });
                };

                request.onsuccess = function(event) {
                    const db = event.target.result;

                    // Process each file and store it in IndexedDB
                    var filePromises = Array.from(files).map(file => {
                        return new Promise((resolve, reject) => {
                            const reader = new FileReader();
                            reader.readAsDataURL(file);
                            reader.onload = function(e) {
                                const imageData = e.target.result;
                                const transaction = db.transaction("images",
                                    "readwrite");
                                const store = transaction.objectStore("images");

                                // Store image data in IndexedDB
                                store.add({
                                    fileData: imageData,
                                }).onsuccess = function(event) {
                                    resolve(event.target
                                        .result); // Resolve with the image ID
                                };
                                store.add({
                                    fileData: imageData,
                                }).onerror = function(error) {
                                    reject(error);
                                };
                            };
                            reader.onerror = function(error) {
                                reject(error);
                            };
                        });
                    });

                    // Wait for all images to be processed
                    Promise.all(filePromises).then(function(imageIDs) {
                        // Store image IDs in the currentData object
                        currentData['images'] = imageIDs;

                        // Add the currentData object to the storedData array
                        storedData.push(currentData);

                        // Store the updated storedData array back in sessionStorage
                        sessionStorage.setItem('quotes', JSON.stringify(storedData));

                        get_html_for_product_review();

                        $("#product_configuration").html(
                            '<div class="" id="loader" onclick="$(this).hide()" style="display: none;"><div class="modal-backdrop-loader" style="position: absolute;top: 0;right: 0;bottom: 0;left: 0;z-index: 1000;background-color: #000;opacity: 0.5;"><i class="fa fa-spinner fa-spin text-white font-30" style="position: absolute;right: 50%;top: 50%;font-size: 26px;"></i></div></div>Please Select The Product'
                        );
                        $(".products-section").hide();

                        $('.dropbtn').html(
                            'Select Product <i class="fa fa-angle-down" aria-hidden="true"></i>'
                        );
                    }).catch(function(error) {
                        console.log("Error processing images: ", error);
                    });
                };

                request.onerror = function(event) {
                    console.error("Error opening IndexedDB:", event.target.errorCode);
                };
            });




            $(document).on('click', '.delete-button', function(e) {
                e.preventDefault();
                $('#delete_quote').modal('show');
                var quote_index_id = $(this).data('quote_index_id');
                $('#delete_quote_btn').attr('data-quote_index_id', quote_index_id);
            })

            $(document).on('click', '#delete_quote .btn-close', function(e) {
                e.preventDefault();
                $('#delete_quote').modal('hide');
            })

            $(document).on('click', '#delete_quote_btn', function(e) {
                e.preventDefault();
                var quote_index_id = $(this).data('quote_index_id');
                var quotes = JSON.parse(sessionStorage.getItem('quotes')) || [];
                quotes.splice(quote_index_id, 1);
                sessionStorage.setItem('quotes', JSON.stringify(quotes));
                get_html_for_product_review();
                $('#delete_quote').modal('hide');
            });

            {literal}
                $(document).on('input', '.inputText', function() {
                    var quote_title = $("#quote_title").val();
                    var customer_email = $("#customer_email").val();
                    const emailRegex = /^[A-Za-z0-9\._%+\-]+@[A-Za-z0-9\.\-]+\.[A-Za-z]{2,}$/;
                    const invalidEmail = $(this).attr('id') === 'customer_email' && customer_email && !
                        emailRegex.test(customer_email);
                    const emptyEmail = $(this).attr('id') === 'customer_email' && customer_email === '';

                    if (invalidEmail) {
                        $('.email_error_msg').text('Please enter a valid email address');
                        return false;
                    } else if (emptyEmail) {
                        $('.email_error_msg').text('Please enter email address');
                        return false;
                    } else {
                        $('.email_error_msg').text('');
                    }

                    if (quote_title && customer_email) {
                        $('button#send_quote').prop('disabled', false);
                        $('button#send_quote').removeClass('disabled');
                    } else {
                        $('button#send_quote').prop('disabled', true);
                        $('button#send_quote').addClass('disabled');
                    }
                });
            {/literal}

            $(document).on('click', '#send_quote', function(e) {
                e.preventDefault();
                //validation for email validation
                var customer_email = $("#customer_email").val();
                var quote_title = $("#quote_title").val();
                var quotes = JSON.parse(sessionStorage.getItem('quotes')) || [];

                if (!customer_email || !quote_title) {
                    return false;
                }

                var elem = $(this);


                var fd = new FormData();
                fd.append('quotes', JSON.stringify(quotes));
                fd.append('quote_title', quote_title);
                fd.append('customer_email', customer_email);
                fd.append('action', 'send_quote');

                async function appendImagesToFormData() {
                    for (let i = 0; i < quotes.length; i++) {
                        const quote = quotes[i];

                        try {
                            // Fetch images for the current quote
                            const images = await fetch_images(quote.images);
                            quote['images'] = images; // Store fetched images back in the quote

                            images.forEach((file, index) => {
                                let fileBlob, fileName, mimeType;

                                if (typeof file === 'string' && file.startsWith('data:application/pdf')) {
                                    // It's a PDF in base64
                                    fileBlob = base64ToBlob(file, 'application/pdf');
                                    mimeType = 'application/pdf';
                                    fileName = 'file_'+Date.now()+'_'+index+'.pdf';
                                } else if (typeof file === 'string' && file.startsWith('data:image/')) {
                                    // It's an image in base64
                                    // You may want to extract the actual image type (png, jpg, etc.)
                                    const match = file.match(/^data:(image\/[a-zA-Z]+);/);
                                    mimeType = match ? match[1] : 'image/png';
                                    fileBlob = base64ToBlob(file, mimeType);
                                    const ext = mimeType.split('/')[1];
                                    fileName = 'image_'+Date.now()+'_'+index+'.'+ext;
                                } else {
                                    // fallback: treat as PNG
                                    fileBlob = base64ToBlob(file, 'image/png');
                                    mimeType = 'image/png';
                                    fileName = 'image_'+Date.now()+'_'+index+'.png';
                                }

                                fd.append('quote_images['+i+']['+index+']', fileBlob, fileName);
                            });

                        } catch (error) {
                            console.error('Error fetching images: ', error);
                        }
                    }

                    // After appending images, you can proceed with sending the form data
                    // sendFormData(fd);  // Example function to send data

                    $(elem).find('i').toggleClass('fa-spinner fa-spin');
                    $(elem).toggleClass('disabled');

                    $.ajax({
                        url: '{$ajax_link}',
                        type: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,
                        cache: false,
                        dataType: 'json',
                        success: function(response) {
                            $(this).find('i').toggleClass('fa-paper-plane');
                            $(this).toggleClass('disabled');
                            if (response.status) {
                                sessionStorage.removeItem('quotes');
                                get_html_for_product_review();
                                window.location.href = response.redirect_link;
                            } else {
                                $('.error_msg').addClass('alert alert-danger');
                                $('.error_msg').text(response.message);
                            }
                        }
                    });
                }

                // // Call the async function to append images and proceed
                appendImagesToFormData();
                // // Once all the images are processed, append them to FormData
                // // fd.append('images', resolve(quote_images));
                
            })
        });

        function base64ToBlob(base64Data, contentType) {
            const byteCharacters = atob(base64Data.split(',')[1]); // Decode base64
            const byteArrays = [];

            for (let offset = 0; offset < byteCharacters.length; offset += 512) {
                const slice = byteCharacters.slice(offset, offset + 512);

                const byteNumbers = new Array(slice.length);
                for (let i = 0; i < slice.length; i++) {
                    byteNumbers[i] = slice.charCodeAt(i);
                }

                const byteArray = new Uint8Array(byteNumbers);
                byteArrays.push(byteArray);
            }

            return new Blob(byteArrays, { type: contentType });
        }

        async function fetch_images(IDs) {
            try {
                const images = await fetchImagesFromIndexedDB(IDs);

                var images_arr = [];
                images.forEach((imageData, index) => {
                    images_arr[imageData.id] = imageData.fileData;
                })
                return images_arr;
            } catch (error) {
                console.error('Error fetching images: ', error);
            }
        }

        async function fetchImagesAndAppendToFormData(fd, quotes) {
            const imageIDs = quotes.flatMap(quote => quote.images);
            const images = await fetchImagesFromIndexedDB(imageIDs);

            images.forEach((imageData, index) => {
                const byteCharacters = atob(imageData.fileData.split(',')[1]);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: 'image/jpeg' });

                fd.append('images[' + index + ']', blob, 'image_' + index + '.jpg');
            });
        }

        /* When the user clicks on the button, toggle between hiding and showing the dropdown content */
        function myFunction() {
            document.getElementById("products").classList.toggle("show");
        }

        function filterFunction() {
            const input = document.getElementById("myInput");
            const filter = input.value.toUpperCase();
            const div = document.getElementById("products");
            const a = div.getElementsByTagName("a");
            for (let i = 0; i < a.length; i++) {
                txtValue = a[i].textContent || a[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    a[i].style.display = "";
                } else {
                    a[i].style.display = "none";
                }
            }
        }

        // Create an async function to handle the initialization

        async function get_html_for_product_review() {
            var quotes = JSON.parse(sessionStorage.getItem('quotes')) || [];
            if (quotes.length > 0) {
                var html = "";

                for (const [index, quote] of quotes.entries()) { // Use for..of for async-await support
                    // Ensure configuration is an array
                    quote.configuration = quote.configuration || [];

                    html += "<tr><td><div class='product-img'><img src='" + quote.product_image +
                        "'></div></td><td><div class='product-row'><div class='product-heading'>Product </div><div class='product-details'>: " +
                        quote.product_name + " </div></div></td><td class='additional-info'>";

                    // Loop through configuration
                    quote.configuration.forEach(config => {
                        html += "<div class='product-row'><div class='product-heading'>" + config
                            .variable_name +
                            "</div><div class='product-details'>: " + config.valueText + " </div></div>";
                    });

                    html +=
                        "<div class='product-row'><div class='product-heading'>Additional information </div><div class='product-details'>: " +
                        (quote.additional_information ?? '-') + " </div></div>";

                    html +=
                        "<div class='product-row attached_files_main_block' style='flex-direction: column'><div class='product-heading'>Attached files: </div><div class='images_block'>";

                    // Fetch images from IndexedDB and display them
                    try {
                        var images = await fetchImagesFromIndexedDB(quote.images);
                        images.forEach(imageData => {
                            html += "<img class='single_image' src='" + imageData.fileData + "'>";
                        });
                    } catch (error) {
                        console.error("Error fetching images from IndexedDB: ", error);
                    }

                    html += "</div></div>";
                    html +=
                        "</td><td><a href='#delete_quote' class='btn btn-outline-danger btn-sm delete-button' data-product_id='" +
                        quote.product_id + "' data-quote_index_id='" + index +
                        "'><i class='fa fa-trash-o' aria-hidden='true'></i></a></td></tr>";
                }

                // Update the table body after all images are processed
                $("#product_review_table_body").html(html);

                $('.products-list-section').show();
                $('.comment-area').show();
                $('.step-section-area').hide();
            } else {
                $('.products-list-section').hide();
                $('.comment-area').hide();
                $("#product_review_table_body").html('');
                $('.step-section-area').show();
            }
        }

        // Function to fetch images from IndexedDB
        async function fetchImagesFromIndexedDB(imageIDs) {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open("imageDB", 1);

                request.onsuccess = function(event) {
                    const db = event.target.result;
                    const transaction = db.transaction("images", "readonly");
                    const store = transaction.objectStore("images");

                    if (!imageIDs || imageIDs.length === 0) {
                        resolve([]);
                        return;
                    }
                    const imagePromises = imageIDs.map(imageID => {
                        return new Promise((resolve, reject) => {
                            const getRequest = store.get(imageID);
                            getRequest.onsuccess = function() {
                                if (getRequest.result) {
                                    resolve(getRequest.result);
                                } else {
                                    reject(new Error("Image not found"));
                                }
                            };
                            getRequest.onerror = function() {
                                reject(getRequest.error);
                            };
                        });
                    });

                    Promise.all(imagePromises).then(resolvedImages => {
                        resolve(resolvedImages);
                    }).catch(error => {
                        reject(error);
                    });
                };

                request.onerror = function(event) {
                    reject("Error opening IndexedDB: " + event.target.errorCode);
                };
            });
        }
    </script>


    <!-- End Main Bloc -->
{/block}