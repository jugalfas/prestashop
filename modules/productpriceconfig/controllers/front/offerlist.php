<?php
/**
 * OfferList - Front controller for the customer's "My Offers" page.
 *
 * Lists all quotations belonging to the current customer (or guest).
 *
 * URL: /module/productpriceconfig/offerlist
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductPriceConfigOfferListModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        $is_logged = $this->context->customer->isLogged();
        $guest_email = '';
        $offers = [];

        if ($is_logged) {
            $offers = Offer::getByCustomer((int) $this->context->customer->id);
        } else {
            $guest_email = Tools::getValue('guest_email', '');
            if ($this->context->cookie->pc_guest_email) {
                $guest_email = $this->context->cookie->pc_guest_email;
            }

            if ($guest_email) {
                $offers = Offer::getByCustomer(0, $guest_email);
            }
        }

        // Format offers for display
        $formattedOffers = [];
        foreach ($offers as $offer) {
            $formattedOffers[] = $this->formatOffer($offer);
        }

        $create_url = $this->context->link->getModuleLink($this->module->name, 'offercreate', [], true);
        $ajax_url = $this->context->link->getModuleLink($this->module->name, 'offerajax', [], true);

        $this->context->smarty->assign([
            'is_logged' => $is_logged,
            'guest_email' => $guest_email,
            'offers' => $formattedOffers,
            'create_url' => $create_url,
            'ajax_url' => $ajax_url,
            'statuses' => Offer::getStatuses(),
        ]);

        $this->setTemplate('module:productpriceconfig/views/templates/front/offer/list.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet(
            'offer-list-css',
            'modules/productpriceconfig/views/css/offer/create.css',
            ['priority' => 200]
        );

        $this->registerJavascript(
            'offer-list-js',
            'modules/productpriceconfig/views/js/offer/create.js',
            ['priority' => 200, 'position' => 'bottom']
        );
    }

    private function formatOffer($offer)
    {
        $detail_url = $this->context->link->getModuleLink(
            $this->module->name,
            'offerdetail',
            ['id_offer' => (int) $offer['id_offer']],
            true
        );

        $product_count = (int) $offer['total_products'];

        // Get product thumbnails
        $products = OfferProduct::getByOffer((int) $offer['id_offer']);
        $thumbnails = [];
        foreach (array_slice($products, 0, 4) as $p) {
            if (!empty($p['image_id'])) {
                $thumbnails[] = $this->context->link->getImageLink(
                    $p['link_rewrite'],
                    $p['image_id'],
                    'small_default'
                );
            }
        }

        return [
            'id_offer' => (int) $offer['id_offer'],
            'reference' => $offer['reference'],
            'title' => $offer['title'],
            'status' => $offer['status'],
            'status_label' => ucfirst(str_replace('_', ' ', $offer['status'])),
            'expire_date' => $offer['expire_date'],
            'total_products' => $product_count,
            'date_add' => $offer['date_add'],
            'date_upd' => $offer['date_upd'],
            'detail_url' => $detail_url,
            'thumbnails' => $thumbnails,
            'is_expired' => !empty($offer['expire_date']) && strtotime($offer['expire_date']) < time(),
        ];
    }
}
