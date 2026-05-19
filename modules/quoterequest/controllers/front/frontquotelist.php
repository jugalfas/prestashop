<?php

/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License ( AFL 3.0 )
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2017 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License ( AFL 3.0 )
 *  International Registered Trademark & Property of PrestaShop SA
 */

//require_once dirname( __FILE__ ) . '/../../libraries/krupaludev/Products.php';
//require_once dirname( __FILE__ ) . '/../../libraries/KDQuoteRequest.php';
//require_once dirname( __FILE__ ) . '/../../libraries/KDQuoteProduct.php';
//require_once dirname( __FILE__ ) . '/../../libraries/KDUploadHandler.php';

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContentFinder;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;


// require_once(dirname(__FILE__, 3) . '/libraries/KDProductSetting.php');
// require_once(dirname(__FILE__, 3) . '/libraries/KDVariable.php');
// require_once(dirname(__FILE__, 3) . '/libraries/KDOption.php');

class QuoteRequestFrontQuoteListModuleFrontController extends ModuleFrontController
{

    public $product;

    private $quantity_discounts;

    public function init()
    {
        $this->page_name = 'quote_request';
        parent::init();
    }

    /** Import CSS And JS Module **/

    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS(Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/quoterequest/views/css/font-awesome.css');
        $this->addCSS(Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/quoterequest/views/css/jquery-ui.css');
        $this->addCSS(Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/quoterequest/views/css/front.css');
        $this->addCSS(Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/quoterequest/views/css/styles.css');
        $this->addCSS(Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/quoterequest/views/css/file-upload-with-preview.min.css');
        $this->addJS(Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/quoterequest/views/js/file-upload-with-preview.min.js');
    }

    /** Init Function Controller **/

    public function initContent()
    {
        $priceFormatter = new PriceFormatter();
        $this->display_column_left = false;
        $this->display_column_right = false;

        parent::initContent();

        $context = Context::getContext();
        $customer = $context->customer;

        if (Tools::getValue('action')) {
            if (Tools::getValue('action') === 'get_single_quote_data') {
                $quote_id = Tools::getValue('quote_id');
                $quote_product_id = Tools::getValue('quote_product_id');
                $quote = KDQuoteProduct::getSingleQuoteProductsDetailsByQuoteId($quote_id, $quote_product_id);

                $response = [
                    'status' => true,
                    'data' => $quote,
                    'redirect_url' => $this->context->link->getModuleLink('quoterequest', 'frontquoterequest'),
                ];
                die(json_encode($response));
            } else if (Tools::getValue('action') === 'get_quote_data') {
                $quote_id = Tools::getValue('quote_id');
                $quote = KDQuoteProduct::getQuoteProductsDetailsByQuoteId($quote_id);

                $response = [
                    'status' => true,
                    'data' => $quote,
                    'redirect_url' => $this->context->link->getModuleLink('quoterequest', 'frontquoterequest'),
                ];
                die(json_encode($response));
            } else if (Tools::getValue('action') === 'get_quote_data_for_order') {
                $quote_id = Tools::getValue('quote_id');
                $quote = KDQuote::getQuoteDetailsByQuoteId($quote_id);

                $html = "
                    <div class='quote-top-section quote-top-section1'>
                        <div class='row'>
                            <div class='col-lg-3 col-md-3 col-sm-12 col-xs-12'>
                                <div class='title-popup-area'><spa > Print Order </span></div>
                            </div>
                            <div class='col-lg-9 col-md-9 col-sm-12 col-xs-12'>
                                <div class='quote-top-text-1'>
                                    <span class='quote-top-number'>Quote :</span>
                                    <span class='quote-pending quote_title'>" . $quote['title'] . "</span>
                                    <span class='quote-top-date'>[ <span class='quote-red quote_status'>". $quote['status_text'] ."</span> ]</span>
                                </div>
                                <div class='quote-note-area'>
                                    Please note : products may require to upload files on cart page or order details page.
                                </div>
                            </div>
                        </div>
                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </div>
                    <div class='main_content'>";

                foreach ($quote['products'] as $key => $product) {
                    $html .= "<div class='quoted-box-section'>
                            <div class='quoted-box-area'>
                                <h3 class='fusion-responsive-typography-calculated'>
                                    <div class='product_popup-heading'>
                                        <input type='checkbox' class='product_check' data-product_id='" . $product['id_product'] . "' value='" . $product['id_quote_product'] . "' data-price='" . number_format($product['price'], 2) . "'> " . $product['product_title'] . " x " . $product['quantity'] . "
                                    </div>
                                    <div class='product_price_section'>
                                        <div class='product_price'>
                                        " . $priceFormatter->format($product['price']) . "
                                        </div>
                                    </div>
                                </h3>
                            </div>
                            <div class='quoted-box-option-area'>
                                <img class='product_image' src='" . $product['image_link'] . "'>
                            </div>
                        </div>";
                }

                $html .= "
                        <div class='quoted-amount-area'>
                            <span class='quote-heading'>Quoted Amount : </span> <span class='quote-price'>--</span>
                        </div>
                        <div class='ammount-area'>
                            <button class='btn add-to-cart-amm' data-quote-id='" . $quote_id . "'><i class='fa fa-shopping-cart'></i> Add to cart</button>
                        </div>
                        <div class='configure-area--infobox-wrapper'>
                            <div class='trustedshops-insurance'>
                                <p>
                                    <img src='https://dev.onlyprint.nl/img/cms/trustedshops_badge_insurance_1.png' width='24' height='24' alt='trustedshops_badge_insurance_1.png'>
                                        Uw bestelling wordt tot € 100 gratis beschermd door Trusted Shops.
                                    </p>
                                    <p>
                                        <strong>
                                            Geen verzendkosten binnen Nederland. Productietijd: 2-3 werkdagen*
                                        </strong>
                                    </p>
                                    <p>
                                    
                                        i Files can be uploaded after ordering* Delivery times for countries can be found 
                                        <a href='/content/huidige-status-van-je-bestelling'> here </a>
                                    
                                </p>
                            </div>
                        </div>
                    </div>
                ";
                $response = [
                    'status' => true,
                    'html' => $html,
                ];
                die(json_encode($response));
            } elseif (Tools::getValue('action') === 'add_to_cart') {
                $id_quote_products = explode(',', Tools::getValue('id_quote_product'));
                
                $response = [];
                foreach ($id_quote_products as $key => $id_quote_product) {
                    $quote = KDQuoteProduct::getSingleQuoteProductsDetailsByQuoteId(Tools::getValue('quote_id'), $id_quote_product);
                    $configurations = $quote['configuration'];
    
                    $product_setting = KDQuote::getProductSettingByProductId($quote['product_id']);
                    $data['id_product'] = $quote['product_id'];
                    $data['id_quote_product'] = $id_quote_product;
                    $data['id_product_setting'] = $product_setting['id_product_setting'];
                    $data['id_product_attribute'] = 0;
                    $data['baned_comb'] = $product_setting['baned_comb'];
                    $data['quote_id'] = Tools::getValue('quote_id');
    
                    foreach ($configurations as $key => $configuration) {
                        $id_product_variable = KDQuote::get_product_variable_id_from_variable_id($configuration['variable_id'], $quote['product_id']);
                        $data['variable_' . $id_product_variable] = $configuration['value'];
                    }
                    $data['quote_price'] = number_format($quote['price'], 2);
                    $response[$key] = $this->ajaxAddToCart($data);
                }

                die(json_encode($response));
            }
        }




        $quotes = [];
        if ($customer->isLogged()) {
            $quotes = KDQuote::getQuotesDetailsByCustomerId($customer->id);
        } elseif (Tools::getValue('email')) {
            $quotes = KDQuote::getQuotesDetailsByCustomerEmail(Tools::getValue('email'));
        }

        $this->context->smarty->assign('quotes', $quotes);

        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $this->setTemplate('module:quoterequest/views/templates/front/quote_list.tpl');
        } else {
            $this->setTemplate('quote_list.tpl');
        }
    }

    public function ajaxAddToCart($params)
    {
        $priceFormatter = new PriceFormatter();

        $product_setting = new KDProductSetting($params['id_product_setting']);
        $product = new Product($params['id_product'], false, (int)$this->context->language->id);

        $id_product_attribute = 0;

        if (!$this->context->cart->id && isset($_COOKIE[$this->context->cookie->getName()])) {
            $this->context->cart->add();
            $this->context->cookie->id_cart = (int) $this->context->cart->id;
        }

        $quote = new KDQuote($params['quote_id']);
        $quote->id_cart = $this->context->cart->id;
        $quote->save();


        $id_customization = $this->context->cart->saveCustomization($product->id, $id_product_attribute);
        $params['id_customization'] = $id_customization;
        $price_weight = $this->getCalculatedProductPriceWeight($params);
        $price_wot = $params['quote_price'];

        $total_weight = $price_weight['weight'];
        $total_thickness = $price_weight['thickness'];


        $available_product_variables = Db::getInstance()->executeS('
            SELECT p.*, pl.name 
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
            WHERE p.id_product = ' . (int)$params['id_product'] . '
            ORDER BY p.`id_product_variable`
        ');
        $variable_position = json_decode($product_setting->variable_position, true);

        if ($variable_position) {
            usort($available_product_variables, function ($a, $b) use ($variable_position) {
                $pos_a = array_search($a['id_product_variable'], $variable_position);
                $pos_b = array_search($b['id_product_variable'], $variable_position);
                return $pos_a - $pos_b;
            });
        }
        $send['error'] = false;
        $count = 1;
        foreach ($available_product_variables as $data) {
            // if(isset($params['variable_'.$data['id_product_variable']]) AND !empty($params['variable_'.$data['id_product_variable']])){
            if (isset($params['variable_' . $data['id_product_variable']])) {
                $count++;
                $value_price = '';
                $varObj = new KDVariable($data['id_variable'], (int)$this->context->language->id);
                if ($varObj->type == 2) {
                    $id_option = $params['variable_' . $data['id_product_variable']];
                    $option = new KDOption($id_option, (int)$this->context->language->id);
                    $value_price = $option->price;
                    $value_weight = $option->weight;
                    $value = $option->label;

                    $options = Db::getInstance()->getValue('
                        SELECT p.options
                        FROM ' . _DB_PREFIX_ . 'product_variable p
                        WHERE p.id_product_variable = ' . (int)$data['id_product_variable'] . '
                    ');

                    $options = json_decode($options, true);
                    if (in_array($id_option, $options)) {
                        $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                    } else {
                        $send['error'] = "Please select only from avalible options";
                    }
                } elseif ($varObj->type == 1) {
                    $value = $params['variable_' . $data['id_product_variable']];
                    $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value, (float)$price_wot, $total_weight);
                } elseif ($varObj->type == 3) {
                    $value = $varObj->fixed_price;
                    $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                } elseif ($varObj->type == 4) {
                    $value = $params['variable_' . $data['id_product_variable']];
                    $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                } elseif ($varObj->type == 5) { // type 5 is for custom text input
                    $value = $params['variable_' . $data['id_product_variable']];
                    $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                } elseif ($varObj->type == 6) { // type 6 is for thickness text input
                    $value = $total_thickness;
                    $this->context->cart->addCustomizationData($id_customization, $data['id_product_variable'], Product::CUSTOMIZE_TEXTFIELD, $value);
                }
            } else {
                $send['error'] = "Please select all options";
            }
        }
        $send['id_customization'] = $id_customization;
        return $send;
    }

    public function getCalculatedProductPriceWeight($params)
    {
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);
        require_once(dirname(__FILE__, 3) . "/expression.php");
        $m = new expression;
        $price_data = array();
        $formula_width = $formula_height = $thickness = 0;
        $priceFormatter = new PriceFormatter();

        $autosize_formula_string   = '#customsize#';
        $autothickness_formula_string   = '#wire#';
        
        $width_price_factor = $height_price_factor = 0;


        $product_setting = new KDProductSetting($params['id_product_setting']);
        // $product = new Product($params['id_product'], true, (int)$this->context->language->id);

        $tiered_price = json_decode($product_setting->tiered, true);
        $formula_price = $product_setting->formula_price;
        $formula_weight = $product_setting->formula_weight;
        $formula_thickness = $product_setting->formula_thickness;


        $available_product_variables = Db::getInstance()->executeS('
            SELECT p.*, pl.name 
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_variable_lang` pl ON (pl.`id_product_variable`= p.`id_product_variable` AND pl.`id_lang` = ' . (int)$this->context->language->id . ' )
            WHERE p.id_product = ' . (int)$params['id_product'] . '
        ');

        $sql = 'SELECT *  FROM ' . _DB_PREFIX_ . 'price_for_odd_quantities WHERE product_id = ' . (int) $params['id_product'];
        $price_for_odd_quantities = Db::getInstance()->executeS($sql);

        foreach ($available_product_variables as $data) {


            if (isset($params['variable_' . $data['id_product_variable']])) {
                $value_price = '';
                $varObj = new KDVariable($data['id_variable'], (int)$this->context->language->id);
                if ($varObj->type == 1) {
                    $qty = $params['variable_' . $data['id_product_variable']];
                    $value_weight = $value_price = $qty;
                    if ($price_for_odd_quantities && $qty % 2 == 1) {
                        $percentage_for_odd_quantity = (int) $price_for_odd_quantities[0]['percentage'];
                        $value_price_with_odd = $value_price * $percentage_for_odd_quantity / 100;
                        $value_price += $value_price_with_odd;
                    }
                } elseif ($varObj->type == 2) {
                    $id_option = $params['variable_' . $data['id_product_variable']];
                    $option = new KDOption($id_option, (int)$this->context->language->id);
                    $value_price = $option->price;
                    $value_weight = $option->weight;
                    $value_thickness = $option->thickness;
                } elseif ($varObj->type == 4) {
                    $custom_input = $params['variable_' . $data['id_product_variable']];
                    $value_weight = $value_price = $value_thickness = $custom_input;

                    if ($data['formula_name'] == 'breedte') {
                        // $width_price_factor = $this->getPriceFactor($custom_input, 'width');
                        $formula_width = $custom_input;
                    }

                    if ($data['formula_name'] == 'hoogte') {
                        // $height_price_factor = $this->getPriceFactor($custom_input, 'height');
                        $formula_height = $custom_input;
                    }
                } elseif ($varObj->type == 3) {
                    $value_weight = $value_price = $varObj->fixed_price;
                }

                //$name = '[variable_'.$data['id_product_variable'].']';

                $name =  '[' . $data['formula_name'] . ']';

                if ($value_price) {
                    $formula_price = str_replace($name, $value_price, $formula_price);
                }

                if ($value_weight) {
                    $formula_weight = str_replace($name, $value_weight, $formula_weight);
                }

                if ($formula_thickness and $value_thickness) {
                    $formula_thickness = str_replace($name, $value_thickness, $formula_thickness);
                }
            }
        }

        if ($formula_width && $formula_height) {
            if ($formula_width > $formula_height) {
                $width_price_factor = $this->getPriceFactor($formula_height, 'width');
                $height_price_factor = $this->getPriceFactor($formula_width, 'height');
            } else {
                $width_price_factor = $this->getPriceFactor($formula_width, 'width');
                $height_price_factor = $this->getPriceFactor($formula_height, 'height');
            }
        }
        //echo  $formula_width.'/'.$formula_height;
        if ($width_price_factor || $height_price_factor) {
            $final_price_factor = max($width_price_factor, $height_price_factor);
            if (strpos($formula_price, $autosize_formula_string) !== false) {
                $formula_price = str_replace($autosize_formula_string, $final_price_factor, $formula_price);
            }
        }

        $formula_price = str_replace("[", "", $formula_price);
        $formula_price = str_replace("]", "", $formula_price);
        $formula_price = preg_replace('/[A-Z][a-z]+/', '0', $formula_price);

       
        $price_wot = $m->evaluate($formula_price);

        $discount = 1;

        if (count($tiered_price)) {

            foreach ($tiered_price as $tired) {
                if ($qty >= $tired['from_quantity']) {
                    $discount = $tired['price'];
                    $discount = $discount / 100;
                }
            }
        }

        $customerGroupId = $this->context->customer->id_default_group;
        $customerGroup = new Group($customerGroupId);
        $discounted_price = $price_wot * $customerGroup->reduction / 100;
        $price_wot = $price_wot - $discounted_price;

        $price_wot = $discount * $price_wot;

        $weight = $m->evaluate($formula_weight);

        if($formula_thickness){
            if (strpos($formula_thickness, $autothickness_formula_string) !== false) {
                $formula_thickness = str_replace($autothickness_formula_string, '', $formula_thickness);
                $thickness = $m->evaluate($formula_thickness);
                $thickness = $this->getWireThicknessByRange($thickness);
    
            }else{
                $thickness = $m->evaluate($formula_thickness);
                $thickness = Tools::ps_round($thickness, 1); 
                $thickness = $thickness.' mm';
            }
        }
       
        return array('price' => $price_wot, 'weight' => $weight, 'thickness' => $thickness);

    }
    public function getWireThicknessByRange($value)
    {

        $value = Tools::ps_round($value, 1);
        $auto_thickness_data = array(
            '6.9 mm (3:1)'         => '0-4.5',
            '8.0 mm (3:1)'         => '4.6-6',
            '9.5 mm (3:1)'         => '6.1-7.5',
            '11.0 mm (3:1)'        => '7.6-9',
            '12.7 mm (3:1)'        => '9.1-10.5',
            '14.3 mm (2:1)'        => '10.6-12',
            '16.0 mm (2:1)'        => '12.1-13.5',
            '19 mm (2:1)'          => '13.6-16',
            '22 mm (2:1)'          => '16.1-19',
            '25.4 mm (2:1)'        => '19.1-22',
            '32 mm (2:1)'          => '22.1-25',
            '38 mm (2:1)'          => '25.1-34'
        );
        foreach ($auto_thickness_data as $key => $thickness) {
            $thickness_array = explode('-', $thickness);

            if (($value >= $thickness_array[0] && $value <= $thickness_array[1])) {
                return $key;
            }
        }

        return 0;
    }
    public function getPriceFactor($value, $type)
    {

        $auto_size_data = array(
            '191'         => array('width' => '842-1188', 'height' => '14001-20000'),
            '134'         => array('width' => '842-1188', 'height' => '9901-14000'),
            '95'          => array('width' => '842-1188', 'height' => '7001-9900'),
            '67'          => array('width' => '842-1188', 'height' => '4951-7000'),
            '48'          => array('width' => '842-1188', 'height' => '3501-4950'),
            '34'          => array('width' => '842-1188', 'height' => '2476-3500'),
            '24'          => array('width' => '842-1188', 'height' => '1751-2475'),
            '17'          => array('width' => '842-1188', 'height' => '1189-1750'),
            '8'           => array('width' => '595-841', 'height' => '842-1188'),
            '4'           => array('width' => '421-594', 'height' => '595-841'),
            '2'           => array('width' => '298-420', 'height' => '421-594'),
            '1'           => array('width' => '211-297', 'height' => '298-420'),
            '0.525'       => array('width' => '149-210', 'height' => '211-297'),
            '0.275625'      => array('width' => '106-148', 'height' => '149-210'),
            '0.144703125'     => array('width' => '75-105', 'height' => '106-148'),
            '0.075969141'    => array('width' => '53-74', 'height' => '75-105'),
            '0.039883799'   => array('width' => '38-52', 'height' => '53-74'),
            '0.020938994'  => array('width' => '27-37', 'height' => '38-52'),
            '0.010992972' => array('width' => '10-26', 'height' => '10-37')
        );
        foreach ($auto_size_data as $key => $size) {
            $size_array = explode('-', $size[$type]);

            if (($value >= $size_array[0] && $value <= $size_array[1])) {
                return $key;
            }
        }

        return 0;
    }

    public function initContent2()
    {
        parent::initContent();
        $id_product = (int)Tools::getValue('id_product');
        $id_product_attribute = (int)Tools::getValue('id_product_attribute');

        $priceDisplay = Product::getTaxCalculationMethod((int) $this->context->cookie->id_customer);
        $productPrice = 0;
        $productPriceWithoutReduction = 0;

        if (!$priceDisplay || $priceDisplay == 2) {
            $productPrice = $this->product->getPrice(true, null, 6);
            $productPriceWithoutReduction = $this->product->getPriceWithoutReduct(false, null);
        } elseif ($priceDisplay == 1) {
            $productPrice = $this->product->getPrice(false, null, 6);
            $productPriceWithoutReduction = $this->product->getPriceWithoutReduct(true, null);
        }

        $assembler = new ProductAssembler($this->context);
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->getTranslator()
        );

        $id_variable_set = KDVariableSet::getVariableSetByProductID($this->product->id);

        $presentationSettings = $this->getProductPresentationSettings();

        $product_for_template = $this->getTemplateVarProduct();

        $variables = array();
        if ($id_variable_set) {
            $variables = KDVariableSet::getVariables($id_variable_set, false);
            foreach ($variables as &$variable) {
                $variable['option_ids'] = Tools::jsonDecode($variable['selected_options']);
                if (count($variable['option_ids']) and ($variable['type'] == 3 || $variable['type'] == 4)) {
                    foreach ($variable['option_ids'] as $id_option) {
                        $option = new KDOption($id_option, $this->context->language->id);
                        $variable['options'][$id_option]['id'] = $option->id;
                        $variable['options'][$id_option]['label'] = $option->label;
                        $variable['options'][$id_option]['image'] = $option->image;
                        $variable['options'][$id_option]['price'] = $option->price;
                    }
                }
            }
        }

        if ($id_variable_set) {
            $customization_datas = $this->context->cart->getProductCustomization($this->product->id, null, true);
        }

        if (Tools::isSubmit('submitCustomizedData')) {

            if (!$this->context->cart->id && isset($_COOKIE[$this->context->cookie->getName()])) {
                $this->context->cart->add();
                $this->context->cookie->id_cart = (int) $this->context->cart->id;
            }
            $this->saveCustomization($variables);

            if (!count($this->errors)) {
                Tools::redirect('index.php?controller=cart&action=show');
            }
        } elseif (Tools::getIsset('deletePicture') && !$this->context->cart->deleteCustomizationToProduct($this->product->id, Tools::getValue('deletePicture'), $id_product_attribute)) {
            $this->errors[] = $this->trans('An error occurred while deleting the selected picture.', array(), 'Shop.Notifications.Error');
        }

        $this->product->customization_required = false;
        $customization_fields = $id_variable_set ? $this->product->getCustomizationFieldsForProduct($this->context->language->id) : false;
        if (is_array($customization_fields)) {
            foreach ($customization_fields as &$customization_field) {
                if ($customization_field['type'] == 0) {
                    $customization_field['key'] = 'pictures_' . $this->product->id . '_' . $customization_field['id_variable'];
                } elseif ($customization_field['type'] == 1) {
                    $customization_field['key'] = 'textFields_' . $this->product->id . '_' . $customization_field['id_variable'];
                }
            }
            unset($customization_field);
        }

        $this->context->smarty->assign(array(
            'priceDisplay' => $priceDisplay,
            'productPriceWithoutReduction' => $productPriceWithoutReduction,
            'id_customization' => empty($customization_datas) ? null : $customization_datas[0]['id_customization'],
            'product' => $product_for_template,
            'displayUnitPrice' => (!empty($this->product->unity) && $this->product->unit_price_ratio > 0.000000) ? true : false,
            'id_product' => $id_product,
            'variables' => $variables,
            'id_product_attribute' => $id_product_attribute,
            'urls_site' => Tools::getHttpHost(true) . __PS_BASE_URI__,
            'img_path'  => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/quoterequest/views/img/upload/',
        ));

        $this->setTemplate('module:quoterequest/views/templates/front/product_customizer.tpl');
    }

    public function getTemplateVarProduct()
    {
        $productSettings = $this->getProductPresentationSettings();
        // Hook displayProductExtraContent
        //$extraContentFinder = new ProductExtraContentFinder();

        $product = $this->objectPresenter->present($this->product);
        $product['id_product'] = (int) $this->product->id;
        $product['out_of_stock'] = (int) $this->product->out_of_stock;
        $product['new'] = (int) $this->product->new;
        $product['id_product_attribute'] = $this->getIdProductAttribute();
        //$product[ 'extraContent' ] = $extraContentFinder->addParams( array( 'product' => $this->product ) )->present();
        $product['ecotax'] = Tools::convertPrice((float) $product['ecotax'], $this->context->currency, true, $this->context);

        $product_full = Product::getProductProperties($this->context->language->id, $product, $this->context);

        $product_full = $this->addProductCustomizationData($product_full);

        $product_full['show_quantities'] = (bool) (
            Configuration::get('PS_DISPLAY_QTIES')
            && Configuration::get('PS_STOCK_MANAGEMENT')
            && $this->product->quantity > 0
            && $this->product->available_for_order
            && !Configuration::isCatalogMode()
        );
        $product_full['quantity_label'] = ($this->product->quantity > 1) ? $this->trans('Items', array(), 'Shop.Theme.Catalog') : $this->trans('Item', array(), 'Shop.Theme.Catalog');
        $product_full['quantity_discounts'] = $this->quantity_discounts;

        if ($product_full['unit_price_ratio'] > 0) {
            $unitPrice = ($productSettings->include_taxes) ? $product_full['price'] : $product_full['price_tax_exc'];
            $product_full['unit_price'] = $unitPrice / $product_full['unit_price_ratio'];
        }

        $group_reduction = GroupReduction::getValueForProduct($this->product->id, (int) Group::getCurrent()->id);
        if ($group_reduction === false) {
            $group_reduction = Group::getReduction((int) $this->context->cookie->id_customer) / 100;
        }
        $product_full['customer_group_discount'] = $group_reduction;

        $presenter = $this->getProductPresenter();

        return $presenter->present(
            $productSettings,
            $product_full,
            $this->context->language
        );
    }

    protected function saveCustomization($variables)
    {
        if (!$field_ids = $variables) {
            return false;
        }

        $authorized_text_fields = array();
        foreach ($field_ids as $field_id) {
            if ($field_id['type'] == 1) {
                $authorized_text_fields[(int) $field_id['id_variable']] = 'textarea_' . (int) $field_id['id_variable'];
            } elseif (in_array($field_id['type'], array(3, 4, 5))) {
                $authorized_text_fields[(int) $field_id['id_variable']] = 'selector_' . (int) $field_id['id_variable'];
            } elseif ($field_id['type'] == 2) {
                $authorized_text_fields[(int) $field_id['id_variable']] = 'file_' . (int) $field_id['id_variable'];
            }
        }

        $indexes = array_flip($authorized_text_fields);

        $total_customize_price = Tools::getValue('total_customize_price', 0);
        $total_setup_price = Tools::getValue('total_setup_price', 0);
        $id_product_attribute = Tools::getValue('id_product_attribute', 0);
        $id_customization = $this->context->cart->saveCustomization($this->product->id, $id_product_attribute, $total_customize_price, $total_setup_price);

        foreach ($_POST as $field_name => $value) {

            foreach ($field_ids as $field_id) {
                if (isset($_POST[$authorized_text_fields[(int)$field_id['id_variable']]]) and $field_id['required'] == 1) {
                    if (empty($_POST[$authorized_text_fields[(int)$field_id['id_variable']]])) {
                        $this->errors[0] = $this->trans('Required field empty', array(), 'Shop.Notifications.Error');
                        echo 'ds';
                    }
                } else if (!isset($_POST[$authorized_text_fields[(int)$field_id['id_variable']]]) and $field_id['required'] == 1) {
                    $this->errors[0] = $this->trans('Required field empty', array(), 'Shop.Notifications.Error');
                }
            }
            if (in_array($field_name, $authorized_text_fields) && $value != '') {
                if (strpos($field_name, 'textarea') and !Validate::isMessage($value)) {
                    $this->errors[] = $this->trans('Invalid message', array(), 'Shop.Notifications.Error');
                }
                if (!count($this->errors)) {
                    if (is_array($value)) {
                        $value = serialize($value);
                    }

                    $this->context->cart->addCustomizationData($id_customization, $indexes[$field_name], Product::CUSTOMIZE_TEXTFIELD, $value);
                }
            } elseif (in_array($field_name, $authorized_text_fields) && $value == '') {
                $this->context->cart->deleteCustomizationToProduct((int) $this->product->id, $indexes[$field_name], $id_product_attribute);
            }
        }

        foreach ($_FILES as $field_name => $file) {
            if (in_array($field_name, $authorized_text_fields) && isset($file['tmp_name']) && !empty($file['tmp_name'])) {
                $file_name = md5(uniqid(rand(), true));

                $allowedExts = array('gif', 'jpeg', 'jpg', 'png', 'bmp');
                $ext = explode('.', $file['name']);
                $extension = end($ext);

                if (in_array($extension, $allowedExts)) {
                    $this->AddImage($file, $file_name);
                    $type = Product::CUSTOMIZE_FILE;
                } else {
                    $this->AddAttachment($file, $file_name);
                    $type = Product::CUSTOMIZE_ATTACHMENT;
                    $file_name = $file_name . '.' . $extension;
                }

                if (empty($this->errors)) {
                    $this->context->cart->addCustomizationData($id_customization, $indexes[$field_name], $type, $file_name);
                }
            }
        }
    }

    public function AddImage($file, $file_name)
    {
        if ($error = ImageManager::validateUpload($file, (int) Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE'))) {
            $this->errors[] = $error;
        }

        $product_picture_width = (int) Configuration::get('PS_PRODUCT_PICTURE_WIDTH');
        $product_picture_height = (int) Configuration::get('PS_PRODUCT_PICTURE_HEIGHT');
        $tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
        if ($error || (!$tmp_name || !move_uploaded_file($file['tmp_name'], $tmp_name))) {
            return false;
        }
        /* Original file */
        if (!ImageManager::resize($tmp_name, _PS_UPLOAD_DIR_ . $file_name)) {
            $this->errors[] = $this->trans('An error occurred during the image upload process.', array(), 'Shop.Notifications.Error');
        } elseif (!ImageManager::resize($tmp_name, _PS_UPLOAD_DIR_ . $file_name . '_small', $product_picture_width, $product_picture_height)) {
            $this->errors[] = $this->trans('An error occurred during the image upload process.', array(), 'Shop.Notifications.Error');
        } elseif (!chmod(_PS_UPLOAD_DIR_ . $file_name, 0777) || !chmod(_PS_UPLOAD_DIR_ . $file_name . '_small', 0777)) {
            $this->errors[] = $this->trans('An error occurred during the image upload process.', array(), 'Shop.Notifications.Error');
        }

        unlink($tmp_name);
    }

    public function AddAttachment($file, $file_name)
    {

        if (isset($file)) {
            if ((int)$file['error'] === 1) {
                $file['error'] = array();

                $max_upload = (int)ini_get('upload_max_filesize');
                $max_post = (int)ini_get('post_max_size');
                $upload_mb = min($max_upload, $max_post);
                $this->errors[] = sprintf(
                    $this->trans('File %1$s exceeds the size allowed by the server. The limit is set to %2$d MB.'),
                    '<b>' . $file['name'] . '</b> ',
                    '<b>' . $upload_mb . '</b>'
                );
            }

            if (empty($file['error'])) {
                if (is_uploaded_file($file['tmp_name'])) {
                    if ($file['size'] > (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024)) {
                        $this->errors[] = sprintf(
                            $this->trans('The file is too large. Maximum size allowed is: %1$d kB. The file you are trying to upload is %2$d kB.'),
                            (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024),
                            number_format(($file['size'] / 1024), 2, '.', '')
                        );
                    } else {
                        do {
                            //$uniqid = sha1( microtime() );
                            $uniqid = $file_name;
                            $ext = explode('.', $file['name']);
                            $extension = end($ext);
                        } while (file_exists(_PS_UPLOAD_DIR_ . $uniqid));
                        if (!copy($file['tmp_name'], _PS_UPLOAD_DIR_ . $uniqid . '.' . $extension)) {
                            $this->errors[] = $this->trans('File copy failed');
                        }
                        @unlink($file['tmp_name']);
                    }
                } else {
                    $this->errors[] = Tools::displayError('The file is missing.');
                }
            }

            return $file;
        }
    }

    protected function addProductCustomizationData(array $product_full)
    {
        $id_variable_set = KDVariableSet::getVariableSetByProductID($product_full['id_product']);

        if ($id_variable_set) {
            $customizationData = array(
                'fields' => array(),
            );

            $customized_data = array();

            $id_product_attribute = $this->getIdProductAttribute();

            $already_customized = $this->context->cart->getProductCustomizationData(
                $product_full['id_product'],
                $id_product_attribute
            );

            $id_customization = 0;
            foreach ($already_customized as $customization) {
                $id_customization = $customization['id_customization'];
                $customized_data[$customization['index']] = $customization;
            }

            $cartProductQuantity = $this->context->cart->getProductQuantity($product_full['id_product'], $product_full['id_product_attribute'], (int)$id_customization);

            $customization_fields = $this->product->getCustomizationFieldsForProduct($this->context->language->id);
            if (is_array($customization_fields)) {
                foreach ($customization_fields as $customization_field) {
                    // 'id_variable' maps to what is called 'index'
                    // in what Product::getProductCustomization() returns
                    $key = $customization_field['id_variable'];

                    $field = array();

                    $field['label'] = $customization_field['variable'];
                    $field['id_variable'] = $customization_field['id_variable'];
                    $field['required'] = $customization_field['required'];
                    $field['detail'] = $customization_field['detail'];
                    $field['note'] = $customization_field['note'];

                    switch ($customization_field['type']) {
                        case 1:
                            $field['type'] = 'text';
                            $field['text'] = '';
                            $field['input_name'] = 'textarea_' . $customization_field['id_variable'];
                            break;
                        case 2:
                            $field['type'] = 'image';
                            $field['image'] = null;
                            $field['remove_image_url'] = null;
                            $field['attachment'] = null;
                            $field['input_name'] = 'file_' . $customization_field['id_variable'];
                            break;
                        case 3:
                            $field['type'] = 'radio';
                            $field['selected'] = '';
                            $field['input_name'] = 'selector_' . $customization_field['id_variable'];
                            $field['option_ids'] = Tools::jsonDecode($customization_field['selected_options']);
                            if (count($field['option_ids'])) {
                                foreach ($field['option_ids'] as $id_option) {
                                    $option = new KDOption($id_option, $this->context->language->id);
                                    $field['options'][$id_option]['id'] = $option->id;
                                    $field['options'][$id_option]['label'] = $option->label;
                                    $field['options'][$id_option]['image'] = $option->image;
                                    if (!empty($cartProductQuantity['quantity']) and $cartProductQuantity['quantity'] > 1) {
                                        $field['options'][$id_option]['price'] = $option->getPriceByQty($cartProductQuantity['quantity']);
                                    } else {
                                        $field['options'][$id_option]['price'] = $option->price;
                                    }
                                }
                            }
                            break;
                        case 4:
                            $field['type'] = 'checkbox';
                            $field['selected'] = array();
                            $field['input_name'] = 'selector_' . $customization_field['id_variable'];
                            $field['option_ids'] = Tools::jsonDecode($customization_field['selected_options']);
                            if (count($field['option_ids'])) {
                                foreach ($field['option_ids'] as $id_option) {
                                    $option = new KDOption($id_option, $this->context->language->id);
                                    $field['options'][$id_option]['id'] = $option->id;
                                    $field['options'][$id_option]['label'] = $option->label;
                                    $field['options'][$id_option]['image'] = $option->image;
                                    if (!empty($cartProductQuantity['quantity']) and $cartProductQuantity['quantity'] > 1) {
                                        $field['options'][$id_option]['price'] = $option->getPriceByQty($cartProductQuantity['quantity']);
                                    } else {
                                        $field['options'][$id_option]['price'] = $option->price;
                                    }
                                }
                            }
                            break;
                        case 5:
                            $field['type'] = 'setup_price';
                            $field['selected'] = '';
                            $field['input_name'] = 'selector_' . $customization_field['id_variable'];
                            $field['fee_amount'] = $customization_field['fee_amount'];
                            break;
                        default:
                            $field['type'] = null;
                    }

                    if (array_key_exists($key, $customized_data)) {
                        $data = $customized_data[$key];
                        $field['is_customized'] = true;
                        switch ($customization_field['type']) {
                            case 1:
                                $field['text'] = $data['value'];
                                break;

                            case 2:
                                if ($data['type'] == Product::CUSTOMIZE_FILE) {
                                    $imageRetriever = new ImageRetriever($this->context->link);
                                    $field['image'] = $imageRetriever->getCustomizationImage(
                                        $data['value']
                                    );
                                } elseif ($data['type'] == Product::CUSTOMIZE_ATTACHMENT) {
                                    $imageRetriever = new ImageRetriever($this->context->link);
                                    $field['attachment'] = $imageRetriever->getCustomizationAttachment(
                                        $data['value']
                                    );
                                }
                                $field['remove_image_url'] = $this->getProductDeletePictureLink(
                                    $product_full['id_product'],
                                    $id_product_attribute,
                                    $id_customization,
                                    $customization_field['id_variable']
                                );
                                break;
                            case 3:
                                $field['selected'] = $data['value'];
                                break;
                            case 4:
                                $selected = unserialize($data['value']);
                                $field['selected'] = $selected;
                                break;
                            case 5:
                                $field['selected'] = $data['value'];
                                break;
                        }
                    } else {
                        $field['is_customized'] = false;
                    }

                    $customizationData['fields'][] = $field;
                }
            }

            $product_full['customizations'] = $customizationData;
            $product_full['id_customization'] = $id_customization;
            $product_full['setup_price'] = $this->context->cart->getSetUpPrice();
            $product_full['customize_price'] = Customization::getCustomizationPrice($id_customization);
            $product_full['is_customizable'] = true;
        } else {
            $product_full['customizations'] = array(
                'fields' => array(),
            );
            $product_full['id_customization'] = 0;
            $product_full['setup_price'] = 0;
            $product_full['customize_price'] = 0;
            $product_full['is_customizable'] = false;
        }

        return $product_full;
    }

    private function getCartSummaryURL()
    {
        return $this->context->link->getPageLink(
            'cart',
            null,
            $this->context->language->id,
            array(
                'action' => 'show'
            ),
            false,
            null,
            true
        );
    }

    private function getIdProductAttribute()
    {
        $requestedIdProductAttribute = (int)Tools::getValue('id_product_attribute');

        if (!Configuration::get('PS_DISP_UNAVAILABLE_ATTR')) {
            $productAttributes = array_filter(
                $this->product->getAttributeCombinations(),

                function ($elem) {
                    return $elem['quantity'] > 0;
                }
            );
            $productAttribute = array_filter(
                $productAttributes,

                function ($elem) use ($requestedIdProductAttribute) {
                    return $elem['id_product_attribute'] == $requestedIdProductAttribute;
                }
            );
            if (empty($productAttribute) && !empty($productAttributes)) {
                return (int)array_shift($productAttributes)['id_product_attribute'];
            }
        }
        return $requestedIdProductAttribute;
    }

    public function getProductDeletePictureLink($id_product, $id_product_attribute, $id_customization, $idPicture)
    {
        $urls_site = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $url = $urls_site . 'index.php?fc=module&module=quoterequest&controller=frontquoterequest';
        $url .= '&id_product=' . $id_product;
        $url .= '&id_product_attribute=' . $id_product_attribute;
        $url .= '&id_customization=' . $id_customization;

        return $url . ((strpos($url, '?')) ? '&' : '?') . 'deletePicture=' . $idPicture;
    }

    public function getProduct()
    {
        return $this->product;
    }

    private function getFactory()
    {
        return new ProductPresenterFactory($this->context, new TaxConfiguration());
    }

    protected function getProductPresentationSettings()
    {
        return $this->getFactory()->getPresentationSettings();
    }

    protected function getProductPresenter()
    {
        return $this->getFactory()->getPresenter();
    }
}
