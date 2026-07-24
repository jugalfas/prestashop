<?php
/**
 * OfferDetail - Front controller for the customer offer detail page.
 *
 * Shows offer header, products with configuration/artwork, history timeline,
 * and action buttons (accept, reject, add to cart, duplicate, print).
 *
 * URL: /module/productpriceconfig/offerdetail?id_offer=123
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductPriceConfigOfferDetailModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $id_offer = (int) Tools::getValue('id_offer');
        if (!$id_offer) {
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'offerlist', [], true));
        }

        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'offerlist', [], true));
        }

        // Ownership check
        $is_logged = $this->context->customer->isLogged();
        $guest_email = '';

        if ($is_logged) {
            if (!$offer->belongsTo((int) $this->context->customer->id)) {
                Tools::redirect($this->context->link->getModuleLink($this->module->name, 'offerlist', [], true));
            }
        } else {
            $guest_email = $this->context->cookie->pc_guest_email ?: Tools::getValue('guest_email', '');
            if (!$offer->belongsTo(0, $guest_email)) {
                Tools::redirect($this->context->link->getModuleLink($this->module->name, 'offerlist', [], true));
            }
        }

        $service = new OfferService($this->module);
        $detail = $service->getOfferDetail($id_offer);

        // Format products for display
        $formattedProducts = [];
        foreach ($detail['products'] as $p) {
            $image = '';
            if (!empty($p['image_id'])) {
                $image = $this->context->link->getImageLink($p['link_rewrite'], $p['image_id'], 'home_default');
            }

            $formattedProducts[] = [
                'id_offer_product' => (int) $p['id_offer_product'],
                'id_product' => (int) $p['id_product'],
                'product_name' => $p['product_name'],
                'product_reference' => $p['product_reference'],
                'quantity' => (int) $p['quantity'],
                'image' => $image,
                'configuration_json' => $p['configuration_json'],
                'configuration_summary' => $p['configuration_summary'],
                'offered_price' => $p['offered_price'] ? Tools::displayPrice((float) $p['offered_price']) : null,
                'weight' => $p['weight'],
                'production_time' => $p['production_time'],
                'product_status' => $p['product_status'],
                'product_status_label' => ucfirst(str_replace('_', ' ', $p['product_status'])),
                'admin_note' => $p['admin_note'],
                'customer_note' => $p['customer_note'],
                'attachments' => $p['attachments'],
            ];
        }

        // Format history timeline
        $formattedHistory = [];
        foreach ($detail['history'] as $h) {
            $actor = '';
            if ($h['id_employee']) {
                $actor = $h['employee_firstname'] . ' ' . $h['employee_lastname'];
            } elseif ($h['id_customer']) {
                $actor = $h['customer_firstname'] . ' ' . $h['customer_lastname'];
            }

            $formattedHistory[] = [
                'action' => $h['action'],
                'action_label' => ucfirst(str_replace('_', ' ', $h['action'])),
                'actor' => $actor,
                'description' => $h['description'],
                'previous_value' => $h['previous_value'],
                'new_value' => $h['new_value'],
                'date_add' => $h['date_add'],
                'product_name' => $h['product_name'],
            ];
        }

        $ajax_url = $this->context->link->getModuleLink($this->module->name, 'offerajax', [], true);
        $my_offers_url = $this->context->link->getModuleLink($this->module->name, 'offerlist', [], true);
        $create_url = $this->context->link->getModuleLink($this->module->name, 'offercreate', [], true);

        $this->context->smarty->assign([
            'offer' => $detail['offer'],
            'products' => $formattedProducts,
            'history' => $formattedHistory,
            'is_logged' => $is_logged,
            'guest_email' => $guest_email,
            'ajax_url' => $ajax_url,
            'my_offers_url' => $my_offers_url,
            'create_url' => $create_url,
            'is_expired' => $offer->isExpired(),
            'has_accepted' => !empty(OfferProduct::getAcceptedProducts($id_offer)),
        ]);

        $this->setTemplate('module:productpriceconfig/views/templates/front/offer/detail.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet(
            'offer-detail-css',
            'modules/productpriceconfig/views/css/offer/create.css',
            ['priority' => 200]
        );

        $this->registerJavascript(
            'offer-detail-js',
            'modules/productpriceconfig/views/js/offer/create.js',
            ['priority' => 200, 'position' => 'bottom']
        );
    }
}
