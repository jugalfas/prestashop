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

require_once dirname( __FILE__ ) . '/../../libraries/krupaludev/Products.php';
require_once dirname( __FILE__ ) . '/../../libraries/KDVariableSet.php';
require_once dirname( __FILE__ ) . '/../../libraries/KDVariable.php';
require_once dirname( __FILE__ ) . '/../../libraries/KDOption.php';
require_once dirname( __FILE__ ) . '/../../libraries/KDUploadHandler.php';

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContentFinder;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class ProductPriceConfigFrontProductPriceConfigModuleFrontController extends ModuleFrontController {

    public $product;

    private $quantity_discounts;

    public function init()
    {
        $this->page_name = 'product_price_config';
        parent::init();

        $id_product = ( int ) Tools::getValue( 'id_product' );

        if ( $id_product ) {
            $this->product = new Product( $id_product, true, $this->context->language->id, $this->context->shop->id );
        }

        if ( !Validate::isLoadedObject( $this->product ) ) {
            Tools::redirect( 'index.php?controller=404' );
        } else {
            if ( !$this->product->hasCombinations() ) {
                unset( $_GET[ 'id_product_attribute' ] );
            } else if ( !Tools::getValue( 'id_product_attribute' ) ) {
                $_GET[ 'id_product_attribute' ] = Product::getDefaultAttribute( $this->product->id );
            }

            $id_product_attribute = $this->getIdProductAttribute();
        }
    }

    /** Import CSS And JS Module **/

    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS( Tools::getHttpHost( true ) . __PS_BASE_URI__ . 'modules/productpriceconfig/views/css/font-awesome.css' );
        $this->addCSS( Tools::getHttpHost( true ) . __PS_BASE_URI__ . 'modules/productpriceconfig/views/css/jquery-ui.css' );
        $this->addCSS( Tools::getHttpHost( true ) . __PS_BASE_URI__ . 'modules/productpriceconfig/views/css/front.css' );
        $this->addCSS( Tools::getHttpHost( true ) . __PS_BASE_URI__ . 'modules/productpriceconfig/views/css/styles.css' );

    }

    /** Init Function Controller **/

    public function initContent()
    {
        parent::initContent();
        $id_product = ( int )Tools::getValue( 'id_product' );
        $id_product_attribute = ( int )Tools::getValue( 'id_product_attribute' );

        $priceDisplay = Product::getTaxCalculationMethod( ( int ) $this->context->cookie->id_customer );
        $productPrice = 0;
        $productPriceWithoutReduction = 0;

        if ( !$priceDisplay || $priceDisplay == 2 ) {
            $productPrice = $this->product->getPrice( true, null, 6 );
            $productPriceWithoutReduction = $this->product->getPriceWithoutReduct( false, null );
        } elseif ( $priceDisplay == 1 ) {
            $productPrice = $this->product->getPrice( false, null, 6 );
            $productPriceWithoutReduction = $this->product->getPriceWithoutReduct( true, null );
        }

        $assembler = new ProductAssembler( $this->context );
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->getTranslator()
        );

        $id_variable_set = KDVariableSet::getVariableSetByProductID( $this->product->id );

        $presentationSettings = $this->getProductPresentationSettings();

        $product_for_template = $this->getTemplateVarProduct();

        $variables = array();
        if ( $id_variable_set ) {
            $variables = KDVariableSet::getVariables( $id_variable_set, false );
            foreach ( $variables as &$variable ) {
                $variable[ 'option_ids' ] = Tools::jsonDecode( $variable[ 'selected_options' ] );
                if ( count( $variable[ 'option_ids' ] ) and ( $variable[ 'type' ] == 3 || $variable[ 'type' ] == 4 ) ) {
                    foreach ( $variable[ 'option_ids' ] as $id_option ) {
                        $option = new KDOption( $id_option, $this->context->language->id );
                        $variable[ 'options' ][ $id_option ][ 'id' ] = $option->id;
                        $variable[ 'options' ][ $id_option ][ 'label' ] = $option->label;
                        $variable[ 'options' ][ $id_option ][ 'image' ] = $option->image;
                        $variable[ 'options' ][ $id_option ][ 'price' ] = $option->price;
                    }
                }

            }

        }

        if ( $id_variable_set ) {
            $customization_datas = $this->context->cart->getProductCustomization( $this->product->id, null, true );
        }

        if ( Tools::isSubmit( 'submitCustomizedData' ) ) {

            if ( !$this->context->cart->id && isset( $_COOKIE[ $this->context->cookie->getName() ] ) ) {
                $this->context->cart->add();
                $this->context->cookie->id_cart = ( int ) $this->context->cart->id;
            }
            $this->saveCustomization( $variables );

            if ( !count( $this->errors ) ) {
                Tools::redirect( 'index.php?controller=cart&action=show' );
            }

        } elseif ( Tools::getIsset( 'deletePicture' ) && !$this->context->cart->deleteCustomizationToProduct( $this->product->id, Tools::getValue( 'deletePicture' ), $id_product_attribute ) ) {
            $this->errors[] = $this->trans( 'An error occurred while deleting the selected picture.', array(), 'Shop.Notifications.Error' );
        }

        $this->product->customization_required = false;
        $customization_fields = $id_variable_set ? $this->product->getCustomizationFieldsForProduct( $this->context->language->id ) : false;
        if ( is_array( $customization_fields ) ) {
            foreach ( $customization_fields as &$customization_field ) {
                if ( $customization_field[ 'type' ] == 0 ) {
                    $customization_field[ 'key' ] = 'pictures_'.$this->product->id.'_'.$customization_field[ 'id_variable' ];
                } elseif ( $customization_field[ 'type' ] == 1 ) {
                    $customization_field[ 'key' ] = 'textFields_'.$this->product->id.'_'.$customization_field[ 'id_variable' ];
                }
            }
            unset( $customization_field );
        }

        $this->context->smarty->assign( array(
            'priceDisplay' => $priceDisplay,
            'productPriceWithoutReduction' => $productPriceWithoutReduction,
            'id_customization' => empty( $customization_datas ) ? null : $customization_datas[ 0 ][ 'id_customization' ],
            'product' => $product_for_template,
            'displayUnitPrice' => ( !empty( $this->product->unity ) && $this->product->unit_price_ratio > 0.000000 ) ? true : false,
            'id_product' => $id_product,
            'variables' => $variables,
            'id_product_attribute' => $id_product_attribute,
            'urls_site' => Tools::getHttpHost( true ) . __PS_BASE_URI__,
            'img_path'  => Tools::getHttpHost( true ) . __PS_BASE_URI__ . 'modules/productpriceconfig/views/img/upload/',
        ) );

        $this->setTemplate( 'module:productpriceconfig/views/templates/front/product_customizer.tpl' );
    }

    public function getTemplateVarProduct()
    {
        $productSettings = $this->getProductPresentationSettings();
        // Hook displayProductExtraContent
        //$extraContentFinder = new ProductExtraContentFinder();

        $product = $this->objectPresenter->present( $this->product );
        $product[ 'id_product' ] = ( int ) $this->product->id;
        $product[ 'out_of_stock' ] = ( int ) $this->product->out_of_stock;
        $product[ 'new' ] = ( int ) $this->product->new;
        $product[ 'id_product_attribute' ] = $this->getIdProductAttribute();
        //$product[ 'extraContent' ] = $extraContentFinder->addParams( array( 'product' => $this->product ) )->present();
        $product[ 'ecotax' ] = Tools::convertPrice( ( float ) $product[ 'ecotax' ], $this->context->currency, true, $this->context );

        $product_full = Product::getProductProperties( $this->context->language->id, $product, $this->context );

        $product_full = $this->addProductCustomizationData( $product_full );

        $product_full[ 'show_quantities' ] = ( bool ) (
            Configuration::get( 'PS_DISPLAY_QTIES' )
            && Configuration::get( 'PS_STOCK_MANAGEMENT' )
            && $this->product->quantity > 0
            && $this->product->available_for_order
            && !Configuration::isCatalogMode()
        );
        $product_full[ 'quantity_label' ] = ( $this->product->quantity > 1 ) ? $this->trans( 'Items', array(), 'Shop.Theme.Catalog' ) : $this->trans( 'Item', array(), 'Shop.Theme.Catalog' );
        $product_full[ 'quantity_discounts' ] = $this->quantity_discounts;

        if ( $product_full[ 'unit_price_ratio' ] > 0 ) {
            $unitPrice = ( $productSettings->include_taxes ) ? $product_full[ 'price' ] : $product_full[ 'price_tax_exc' ];
            $product_full[ 'unit_price' ] = $unitPrice / $product_full[ 'unit_price_ratio' ];
        }

        $group_reduction = GroupReduction::getValueForProduct( $this->product->id, ( int ) Group::getCurrent()->id );
        if ( $group_reduction === false ) {
            $group_reduction = Group::getReduction( ( int ) $this->context->cookie->id_customer ) / 100;
        }
        $product_full[ 'customer_group_discount' ] = $group_reduction;

        $presenter = $this->getProductPresenter();

        return $presenter->present(
            $productSettings,
            $product_full,
            $this->context->language
        );
    }

    protected function saveCustomization( $variables ) {
        if ( !$field_ids = $variables ) {
            return false;
        }

        $authorized_text_fields = array();
        foreach ( $field_ids as $field_id ) {
            if ( $field_id[ 'type' ] == 1 ) {
                $authorized_text_fields[ ( int ) $field_id[ 'id_variable' ] ] = 'textarea_'.( int ) $field_id[ 'id_variable' ];
            } elseif ( in_array( $field_id[ 'type' ], array( 3, 4, 5 ) ) ) {
                $authorized_text_fields[ ( int ) $field_id[ 'id_variable' ] ] = 'selector_'.( int ) $field_id[ 'id_variable' ];
            } elseif ( $field_id[ 'type' ] == 2 ) {
                $authorized_text_fields[ ( int ) $field_id[ 'id_variable' ] ] = 'file_'.( int ) $field_id[ 'id_variable' ];
            }

        }

        $indexes = array_flip( $authorized_text_fields );

        $total_customize_price = Tools::getValue( 'total_customize_price', 0 );
        $total_setup_price = Tools::getValue( 'total_setup_price', 0 );
        $id_product_attribute = Tools::getValue( 'id_product_attribute', 0 );
        $id_customization = $this->context->cart->saveCustomization( $this->product->id, $id_product_attribute, $total_customize_price, $total_setup_price );

        foreach ( $_POST as $field_name => $value ) {

            foreach ( $field_ids as $field_id ) {
                if ( isset( $_POST[ $authorized_text_fields[ ( int )$field_id[ 'id_variable' ] ] ] ) and $field_id[ 'required' ] == 1 ) {
                    if ( empty( $_POST[ $authorized_text_fields[ ( int )$field_id[ 'id_variable' ] ] ] ) ) {
                        $this->errors[ 0 ] = $this->trans( 'Required field empty', array(), 'Shop.Notifications.Error' );
                        echo 'ds';
                    }

                } else if ( !isset( $_POST[ $authorized_text_fields[ ( int )$field_id[ 'id_variable' ] ] ] ) and $field_id[ 'required' ] == 1 ) {
                    $this->errors[ 0 ] = $this->trans( 'Required field empty', array(), 'Shop.Notifications.Error' );
                }
            }
            if ( in_array( $field_name, $authorized_text_fields ) && $value != '' ) {
                if ( strpos( $field_name, 'textarea' ) and !Validate::isMessage( $value ) ) {
                    $this->errors[] = $this->trans( 'Invalid message', array(), 'Shop.Notifications.Error' );
                }
                if ( !count( $this->errors ) ) {
                    if ( is_array( $value ) ) {
                        $value = serialize( $value );
                    }

                    $this->context->cart->addCustomizationData( $id_customization, $indexes[ $field_name ], Product::CUSTOMIZE_TEXTFIELD, $value );
                }
            } elseif ( in_array( $field_name, $authorized_text_fields ) && $value == '' ) {
                $this->context->cart->deleteCustomizationToProduct( ( int ) $this->product->id, $indexes[ $field_name ], $id_product_attribute );
            }
        }

        foreach ( $_FILES as $field_name => $file ) {
            if ( in_array( $field_name, $authorized_text_fields ) && isset( $file[ 'tmp_name' ] ) && !empty( $file[ 'tmp_name' ] ) ) {
                $file_name = md5( uniqid( rand(), true ) );

                $allowedExts = array( 'gif', 'jpeg', 'jpg', 'png', 'bmp' );
                $ext = explode( '.', $file[ 'name' ] );
                $extension = end( $ext );

                if ( in_array( $extension, $allowedExts ) ) {
                    $this->AddImage( $file, $file_name );
                    $type = Product::CUSTOMIZE_FILE;
                } else {
                    $this->AddAttachment( $file, $file_name );
                    $type = Product::CUSTOMIZE_ATTACHMENT;
                    $file_name = $file_name.'.'.$extension;
                }

                if ( empty( $this->errors ) ) {
                    $this->context->cart->addCustomizationData( $id_customization, $indexes[ $field_name ], $type, $file_name );
                }

            }
        }

    }

    public function AddImage( $file, $file_name )
    {
        if ( $error = ImageManager::validateUpload( $file, ( int ) Configuration::get( 'PS_PRODUCT_PICTURE_MAX_SIZE' ) ) ) {
            $this->errors[] = $error;
        }

        $product_picture_width = ( int ) Configuration::get( 'PS_PRODUCT_PICTURE_WIDTH' );
        $product_picture_height = ( int ) Configuration::get( 'PS_PRODUCT_PICTURE_HEIGHT' );
        $tmp_name = tempnam( _PS_TMP_IMG_DIR_, 'PS' );
        if ( $error || ( !$tmp_name || !move_uploaded_file( $file[ 'tmp_name' ], $tmp_name ) ) ) {
            return false;
        }
        /* Original file */
        if ( !ImageManager::resize( $tmp_name, _PS_UPLOAD_DIR_.$file_name ) ) {
            $this->errors[] = $this->trans( 'An error occurred during the image upload process.', array(), 'Shop.Notifications.Error' );
        } elseif ( !ImageManager::resize( $tmp_name, _PS_UPLOAD_DIR_.$file_name.'_small', $product_picture_width, $product_picture_height ) ) {
            $this->errors[] = $this->trans( 'An error occurred during the image upload process.', array(), 'Shop.Notifications.Error' );
        } elseif ( !chmod( _PS_UPLOAD_DIR_.$file_name, 0777 ) || !chmod( _PS_UPLOAD_DIR_.$file_name.'_small', 0777 ) ) {
            $this->errors[] = $this->trans( 'An error occurred during the image upload process.', array(), 'Shop.Notifications.Error' );
        }

        unlink( $tmp_name );

    }

    public function AddAttachment( $file, $file_name )
    {

        if ( isset( $file ) ) {
            if ( ( int )$file[ 'error' ] === 1 ) {
                $file[ 'error' ] = array();

                $max_upload = ( int )ini_get( 'upload_max_filesize' );
                $max_post = ( int )ini_get( 'post_max_size' );
                $upload_mb = min( $max_upload, $max_post );
                $this->errors[] = sprintf(
                    $this->trans( 'File %1$s exceeds the size allowed by the server. The limit is set to %2$d MB.' ),
                    '<b>'.$file[ 'name' ].'</b> ',
                    '<b>'.$upload_mb.'</b>'
                );
            }

            if ( empty( $file[ 'error' ] ) ) {
                if ( is_uploaded_file( $file[ 'tmp_name' ] ) ) {
                    if ( $file[ 'size' ] > ( Configuration::get( 'PS_ATTACHMENT_MAXIMUM_SIZE' ) * 1024 * 1024 ) ) {
                        $this->errors[] = sprintf(
                            $this->trans( 'The file is too large. Maximum size allowed is: %1$d kB. The file you are trying to upload is %2$d kB.' ),
                            ( Configuration::get( 'PS_ATTACHMENT_MAXIMUM_SIZE' ) * 1024 ),
                            number_format( ( $file[ 'size' ] / 1024 ), 2, '.', '' )
                        );
                    } else {
                        do {
                            //$uniqid = sha1( microtime() );
                            $uniqid = $file_name;
                            $ext = explode( '.', $file[ 'name' ] );
                            $extension = end( $ext );

                        }
                        while ( file_exists( _PS_UPLOAD_DIR_.$uniqid ) );
                        if ( !copy( $file[ 'tmp_name' ], _PS_UPLOAD_DIR_.$uniqid.'.'.$extension ) ) {
                            $this->errors[] = $this->trans( 'File copy failed' );
                        }
                        @unlink( $file[ 'tmp_name' ] );
                    }
                } else {
                    $this->errors[] = Tools::displayError( 'The file is missing.' );
                }

            }

            return $file;
        }
    }

    protected function addProductCustomizationData( array $product_full )
    {
        $id_variable_set = KDVariableSet::getVariableSetByProductID( $product_full[ 'id_product' ] );

        if ( $id_variable_set ) {
            $customizationData = array(
                'fields' => array(),
            );

            $customized_data = array();

            $id_product_attribute = $this->getIdProductAttribute();

            $already_customized = $this->context->cart->getProductCustomizationData(
                $product_full[ 'id_product' ], $id_product_attribute
            );

            $id_customization = 0;
            foreach ( $already_customized as $customization ) {
                $id_customization = $customization[ 'id_customization' ];
                $customized_data[ $customization[ 'index' ] ] = $customization;
            }

            $cartProductQuantity = $this->context->cart->getProductQuantity( $product_full[ 'id_product' ], $product_full[ 'id_product_attribute' ], ( int )$id_customization );

            $customization_fields = $this->product->getCustomizationFieldsForProduct( $this->context->language->id );
            if ( is_array( $customization_fields ) ) {
                foreach ( $customization_fields as $customization_field ) {
                    // 'id_variable' maps to what is called 'index'
                    // in what Product::getProductCustomization() returns
                    $key = $customization_field[ 'id_variable' ];

                    $field = array();

                    $field[ 'label' ] = $customization_field[ 'variable' ];
                    $field[ 'id_variable' ] = $customization_field[ 'id_variable' ];
                    $field[ 'required' ] = $customization_field[ 'required' ];
                    $field[ 'detail' ] = $customization_field[ 'detail' ];
                    $field[ 'note' ] = $customization_field[ 'note' ];

                    switch ( $customization_field[ 'type' ] ) {
                        case 1:
                        $field[ 'type' ] = 'text';
                        $field[ 'text' ] = '';
                        $field[ 'input_name' ] = 'textarea_'.$customization_field[ 'id_variable' ];
                        break;
                        case 2:
                        $field[ 'type' ] = 'image';
                        $field[ 'image' ] = null;
                        $field[ 'remove_image_url' ] = null;
                        $field[ 'attachment' ] = null;
                        $field[ 'input_name' ] = 'file_'.$customization_field[ 'id_variable' ];
                        break;
                        case 3:
                        $field[ 'type' ] = 'radio';
                        $field[ 'selected' ] = '';
                        $field[ 'input_name' ] = 'selector_'.$customization_field[ 'id_variable' ];
                        $field[ 'option_ids' ] = Tools::jsonDecode( $customization_field[ 'selected_options' ] );
                        if ( count( $field[ 'option_ids' ] ) ) {
                            foreach ( $field[ 'option_ids' ] as $id_option ) {
                                $option = new KDOption( $id_option, $this->context->language->id );
                                $field[ 'options' ][ $id_option ][ 'id' ] = $option->id;
                                $field[ 'options' ][ $id_option ][ 'label' ] = $option->label;
                                $field[ 'options' ][ $id_option ][ 'image' ] = $option->image;
                                if ( !empty( $cartProductQuantity[ 'quantity' ] ) and $cartProductQuantity[ 'quantity' ] > 1 ) {
                                    $field[ 'options' ][ $id_option ][ 'price' ] = $option->getPriceByQty( $cartProductQuantity[ 'quantity' ] );
                                } else {
                                    $field[ 'options' ][ $id_option ][ 'price' ] = $option->price;
                                }

                            }
                        }
                        break;
                        case 4:
                        $field[ 'type' ] = 'checkbox';
                        $field[ 'selected' ] = array();
                        $field[ 'input_name' ] = 'selector_'.$customization_field[ 'id_variable' ];
                        $field[ 'option_ids' ] = Tools::jsonDecode( $customization_field[ 'selected_options' ] );
                        if ( count( $field[ 'option_ids' ] ) ) {
                            foreach ( $field[ 'option_ids' ] as $id_option ) {
                                $option = new KDOption( $id_option, $this->context->language->id );
                                $field[ 'options' ][ $id_option ][ 'id' ] = $option->id;
                                $field[ 'options' ][ $id_option ][ 'label' ] = $option->label;
                                $field[ 'options' ][ $id_option ][ 'image' ] = $option->image;
                                if ( !empty( $cartProductQuantity[ 'quantity' ] ) and $cartProductQuantity[ 'quantity' ] > 1 ) {
                                    $field[ 'options' ][ $id_option ][ 'price' ] = $option->getPriceByQty( $cartProductQuantity[ 'quantity' ] );
                                } else {
                                    $field[ 'options' ][ $id_option ][ 'price' ] = $option->price;
                                }
                            }
                        }
                        break;
                        case 5:
                        $field[ 'type' ] = 'setup_price';
                        $field[ 'selected' ] = '';
                        $field[ 'input_name' ] = 'selector_'.$customization_field[ 'id_variable' ];
                        $field[ 'fee_amount' ] = $customization_field[ 'fee_amount' ];
                        break;
                        default:
                        $field[ 'type' ] = null;
                    }

                    if ( array_key_exists( $key, $customized_data ) ) {
                        $data = $customized_data[ $key ];
                        $field[ 'is_customized' ] = true;
                        switch ( $customization_field[ 'type' ] ) {
                            case 1:
                            $field[ 'text' ] = $data[ 'value' ];
                            break;

                            case 2:
                            if ( $data[ 'type' ] == Product::CUSTOMIZE_FILE ) {
                                $imageRetriever = new ImageRetriever( $this->context->link );
                                $field[ 'image' ] = $imageRetriever->getCustomizationImage(
                                    $data[ 'value' ]
                                );

                            } elseif ( $data[ 'type' ] == Product::CUSTOMIZE_ATTACHMENT ) {
                                $imageRetriever = new ImageRetriever( $this->context->link );
                                $field[ 'attachment' ] = $imageRetriever->getCustomizationAttachment(
                                    $data[ 'value' ]
                                );
                            }
                            $field[ 'remove_image_url' ] = $this->getProductDeletePictureLink(
                                $product_full[ 'id_product' ],
                                $id_product_attribute,
                                $id_customization,
                                $customization_field[ 'id_variable' ]
                            );
                            break;
                            case 3:
                            $field[ 'selected' ] = $data[ 'value' ];
                            break;
                            case 4:
                            $selected = unserialize( $data[ 'value' ] );
                            $field[ 'selected' ] = $selected;
                            break;
                            case 5:
                            $field[ 'selected' ] = $data[ 'value' ];
                            break;

                        }
                    } else {
                        $field[ 'is_customized' ] = false;
                    }

                    $customizationData[ 'fields' ][] = $field;
                }
            }

            $product_full[ 'customizations' ] = $customizationData;
            $product_full[ 'id_customization' ] = $id_customization;
            $product_full[ 'setup_price' ] = $this->context->cart->getSetUpPrice();
            $product_full[ 'customize_price' ] = Customization::getCustomizationPrice( $id_customization );
            $product_full[ 'is_customizable' ] = true;
        } else {
            $product_full[ 'customizations' ] = array(
                'fields' => array(),
            );
            $product_full[ 'id_customization' ] = 0;
            $product_full[ 'setup_price' ] = 0;
            $product_full[ 'customize_price' ] = 0;
            $product_full[ 'is_customizable' ] = false;
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
        $requestedIdProductAttribute = ( int )Tools::getValue( 'id_product_attribute' );

        if ( !Configuration::get( 'PS_DISP_UNAVAILABLE_ATTR' ) ) {
            $productAttributes = array_filter(
                $this->product->getAttributeCombinations(),

                function ( $elem ) {
                    return $elem[ 'quantity' ] > 0;
                }
            );
            $productAttribute = array_filter(
                $productAttributes,

                function ( $elem ) use ( $requestedIdProductAttribute ) {
                    return $elem[ 'id_product_attribute' ] == $requestedIdProductAttribute;
                }
            );
            if ( empty( $productAttribute ) && !empty( $productAttributes ) ) {
                return ( int )array_shift( $productAttributes )[ 'id_product_attribute' ];
            }
        }
        return $requestedIdProductAttribute;
    }

    public function getProductDeletePictureLink( $id_product, $id_product_attribute, $id_customization, $idPicture )
    {
        $urls_site = Tools::getHttpHost( true ) . __PS_BASE_URI__;
        $url = $urls_site.'index.php?fc=module&module=productpriceconfig&controller=frontproductpriceconfig';
        $url .= '&id_product='.$id_product;
        $url .= '&id_product_attribute='.$id_product_attribute;
        $url .= '&id_customization='.$id_customization;

        return $url.( ( strpos( $url, '?' ) ) ? '&' : '?' ).'deletePicture='.$idPicture;
    }

    public function getProduct()
    {
        return $this->product;
    }

    private function getFactory()
    {
        return new ProductPresenterFactory( $this->context, new TaxConfiguration() );
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
