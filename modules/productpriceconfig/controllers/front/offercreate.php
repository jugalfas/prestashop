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

        $this->context->smarty->assign([
            'is_logged' => $is_logged,
            'guest_email' => $guest_email,
            'guest_name' => $guest_name,
            'ajax_url' => $ajax_url,
            'my_offers_url' => $my_offers_url,
            'default_validity_days' => (int) Configuration::get('PPC_OFFER_VALIDITY_DAYS', 15),
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
