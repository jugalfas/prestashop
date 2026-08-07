<?php
/**
 * OfferCreate - Front controller for the quotation creation page.
 *
 * Displays the large product search + configurator + quote cart interface.
 *
 * URL: /module/productpriceconfig/offercreate
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductPriceConfigOfferCreateModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $is_logged = $this->context->customer->isLogged();
        $guest_email = '';
        $guest_name = '';

        if (!$is_logged) {
            $guest_email = Tools::getValue('guest_email', '');
            $guest_name = Tools::getValue('guest_name', '');

            if ($this->context->cookie->pc_guest_email) {
                $guest_email = $this->context->cookie->pc_guest_email;
            }
            if ($this->context->cookie->pc_guest_name) {
                $guest_name = $this->context->cookie->pc_guest_name;
            }
        }

        $ajax_url = $this->context->link->getModuleLink($this->module->name, 'offerajax', [], true);
        $my_offers_url = $this->context->link->getModuleLink($this->module->name, 'offerlist', [], true);

        $custom_quote_product_id = (int) Configuration::get('PPC_CUSTOM_QUOTE_PRODUCT_ID', 0);
        $custom_quote_tooltip = Configuration::get('PPC_CUSTOM_QUOTE_TOOLTIP', '');
        $custom_quote_product = null;
        if ($custom_quote_product_id) {
            $product = new Product($custom_quote_product_id, false, $this->context->language->id);
            if (Validate::isLoadedObject($product)) {
                $id_image = Db::getInstance()->getValue(
                    'SELECT id_image FROM ' . _DB_PREFIX_ . 'image WHERE id_product = ' . (int) $custom_quote_product_id . ' AND cover = 1'
                );
                $image_url = '';
                if ($id_image) {
                    $image_url = $this->context->link->getImageLink($product->link_rewrite, $id_image, 'small_default');
                }
                $custom_quote_product = [
                    'id_product' => (int) $custom_quote_product_id,
                    'name' => $product->name,
                    'image' => $image_url,
                ];
            }
        }

        $this->context->smarty->assign([
            'is_logged' => $is_logged,
            'id_customer' => $is_logged ? (int) $this->context->customer->id : 0,
            'guest_email' => $guest_email,
            'guest_name' => $guest_name,
            'ajax_url' => $ajax_url,
            'my_offers_url' => $my_offers_url,
            'default_validity_days' => (int) Configuration::get('PPC_OFFER_VALIDITY_DAYS', 15),
            'custom_quote_product' => $custom_quote_product,
            'custom_quote_tooltip' => $custom_quote_tooltip,
        ]);

        $this->setTemplate('module:productpriceconfig/views/templates/front/offer/create.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet(
            'offer-create-css',
            'modules/productpriceconfig/views/css/offer/create.css',
            ['priority' => 200]
        );

        $this->registerJavascript(
            'offer-create-js',
            'modules/productpriceconfig/views/js/offer/create.js',
            ['priority' => 200, 'position' => 'bottom']
        );
    }
}
