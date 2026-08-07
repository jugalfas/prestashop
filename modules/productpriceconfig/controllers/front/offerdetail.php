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
        $id_lang = (int) $this->context->language->id;
        $formattedProducts = [];
        foreach ($detail['products'] as $p) {
            $image = '';
            if (!empty($p['image_id'])) {
                $image = $this->context->link->getImageLink($p['link_rewrite'], $p['image_id'], 'home_default');
            }

            // Render configuration summary as rich HTML (no raw JSON)
            $configHtml = ConfigurationSummaryRenderer::renderHtml($p['configuration_json'], $id_lang);

            // Calculate production time in days
            $productionDays = self::parseProductionDays($p['production_time']);

            // Format attachments with preview + size + download
            $formattedAttachments = [];
            if (!empty($p['attachments'])) {
                foreach ($p['attachments'] as $att) {
                    $isImage = self::isImageFile($att['original_name'] ?: $att['saved_name']);
                    $thumbUrl = '';
                    if ($isImage && !empty($att['saved_name'])) {
                        $thumbUrl = $this->context->link->getMediaLink(_PS_UPLOAD_DIR_ . 'offer_attachments/' . $att['saved_name']);
                    }
                    $formattedAttachments[] = [
                        'id_attachment' => (int) $att['id_attachment'],
                        'original_name' => $att['original_name'] ?: $att['saved_name'],
                        'formatted_size' => self::formatFileSize((int) ($att['size'] ?? 0)),
                        'is_image' => $isImage,
                        'thumb_url' => $thumbUrl,
                    ];
                }
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
                'configuration_summary_html' => $configHtml,
                'offered_price' => $p['offered_price'] !== null && $p['offered_price'] !== '' ? (float) $p['offered_price'] : null,
                'estimated_price' => isset($p['estimated_price']) ? (float) $p['estimated_price'] : null,
                'discounted_amount' => isset($p['discounted_amount']) ? (float) $p['discounted_amount'] : null,
                'weight' => $p['weight'],
                'production_time' => $p['production_time'],
                'production_days' => $productionDays,
                'product_status' => $p['product_status'],
                'product_type' => isset($p['product_type']) ? $p['product_type'] : 'normal',
                'product_status_label' => ucfirst(str_replace('_', ' ', $p['product_status'])),
                'admin_note' => $p['admin_note'],
                'customer_note' => $p['customer_note'],
                'attachments' => $formattedAttachments,
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
        $cart_url = $this->context->link->getPageLink('cart', null, $this->context->language->id, ['action' => 'show'], false, null, true);

        // Add status_label to the offer array for the template
        $offerData = $detail['offer'];
        $offerData['status_label'] = ucfirst(str_replace('_', ' ', $offerData['status']));

        $this->context->smarty->assign([
            'offer' => $offerData,
            'products' => $formattedProducts,
            'history' => $formattedHistory,
            'is_logged' => $is_logged,
            'guest_email' => $guest_email,
            'ajax_url' => $ajax_url,
            'my_offers_url' => $my_offers_url,
            'create_url' => $create_url,
            'cart_url' => $cart_url,
            'is_expired' => $offer->isExpired(),
            'has_accepted' => !empty(OfferProduct::getAcceptedProducts($id_offer)),
        ]);

        $this->setTemplate('module:productpriceconfig/views/templates/front/offer/detail.tpl');
    }

    /**
     * Parse a production_time string into a number of days.
     * Handles "1 Week", "7", "2 Weeks", "3 Days", etc.
     *
     * @param string|null $productionTime
     * @return int|null
     */
    private static function parseProductionDays($productionTime)
    {
        if (!$productionTime) {
            return null;
        }
        $lower = strtolower(trim($productionTime));
        $num = (int) preg_replace('/[^0-9]/', '', $lower);
        if (!$num) {
            return null;
        }
        if (strpos($lower, 'week') !== false) {
            return $num * 7;
        }
        if (strpos($lower, 'month') !== false) {
            return $num * 30;
        }
        return $num;
    }

    /**
     * Check if a file is an image based on its extension.
     *
     * @param string $filename
     * @return bool
     */
    private static function isImageFile($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }

    /**
     * Format a file size in bytes to a human-readable string.
     *
     * @param int $bytes
     * @return string
     */
    private static function formatFileSize($bytes)
    {
        if (!$bytes) {
            return '0 B';
        }
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1048576, 1) . ' MB';
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
