<?php
/**
 * AdminOffers - Admin controller for managing quotations.
 *
 * Provides listing, detail view, price offering, cancellation,
 * expiry extension, PDF generation, and order conversion.
 *
 * URL: /admin/index.php?controller=AdminOffers
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../offer/classes/Offer.php';
require_once dirname(__FILE__) . '/../../offer/classes/OfferProduct.php';
require_once dirname(__FILE__) . '/../../offer/classes/OfferHistory.php';
require_once dirname(__FILE__) . '/../../offer/classes/OfferAttachment.php';
require_once dirname(__FILE__) . '/../../offer/services/OfferService.php';
require_once dirname(__FILE__) . '/../../offer/services/OfferPdfGenerator.php';
require_once dirname(__FILE__) . '/../../offer/services/ConfigurationSummaryRenderer.php';

class AdminOffersController extends ModuleAdminController
{
    public $bootstrap = true;

    /** @var OfferService */
    private $offerService;

    public function __construct()
    {
        $this->table = 'offer';
        $this->className = 'Offer';
        $this->identifier = 'id_offer';
        $this->list_simple_header = false;
        $this->allow_export = false;
        $this->display = 'view';

        parent::__construct();

        $this->offerService = new OfferService($this->module);
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS(__PS_BASE_URI__ . 'modules/productpriceconfig/views/css/offer/admin.css');
        $this->addJS(__PS_BASE_URI__ . 'modules/productpriceconfig/views/js/offer/admin.js');
    }

    public function initContent()
    {
        $action = Tools::getValue('action');

        if ($action === 'ajax') {
            $this->handleAjax();
            return;
        }

        if ($action === 'pdf') {
            $id_offer = (int) Tools::getValue('id_offer');
            
            $pdfContent = OfferPdfGenerator::generate($id_offer);
            if ($pdfContent) {
                $offer = Offer::getOffer($id_offer);
                $filename = 'Offer_' . ($offer['reference'] ?? $id_offer) . '.pdf';
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($pdfContent));
                header('Cache-Control: private, no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                echo $pdfContent;
            }
            exit;
        }

        $id_offer = (int) Tools::getValue('id_offer');

        if ($id_offer && Tools::getValue('view') !== 'list') {
            $this->renderDetail($id_offer);
        } else {
            $this->renderList();
        }
    }

    /**
     * Render the offer listing page.
     */
    public function renderList()
    {
        $page = max(1, (int) Tools::getValue('page', 1));
        $limit = max(10, min(100, (int) Tools::getValue('limit', 25)));

        $filters = [
            'search' => Tools::getValue('search', ''),
            'status' => Tools::getValue('status', ''),
            'id_customer' => (int) Tools::getValue('id_customer', 0),
            'date_from' => Tools::getValue('date_from', ''),
            'date_to' => Tools::getValue('date_to', ''),
            'expired_only' => (int) Tools::getValue('expired_only', 0),
        ];

        $order_by = Tools::getValue('orderby', 'date_add');
        $order_way = Tools::getValue('orderway', 'DESC');

        $total = Offer::countListing($filters);
        $offset = ($page - 1) * $limit;
        $offers = Offer::getListing($filters, $limit, $offset, $order_by, $order_way);

        // Format offers
        $formattedOffers = [];
        foreach ($offers as $offer) {
            $detail_url = $this->context->link->getAdminLink('AdminOffers') . '&id_offer=' . (int) $offer['id_offer'];

            $products = OfferProduct::getByOffer((int) $offer['id_offer']);
            $thumbnails = [];
            foreach (array_slice($products, 0, 4) as $p) {
                if (!empty($p['image_id'])) {
                    $thumbnails[] = $this->context->link->getImageLink($p['link_rewrite'], $p['image_id'], 'small_default');
                }
            }

            $customerName = $offer['firstname'] ? $offer['firstname'] . ' ' . $offer['lastname'] : ($offer['guest_name'] ?: 'Guest');
            $customerEmail = $offer['email'] ?: $offer['guest_email'];

            $isExpired = !empty($offer['expire_date']) && strtotime($offer['expire_date']) < time()
                && !in_array($offer['status'], [Offer::STATUS_EXPIRED, Offer::STATUS_CANCELLED, Offer::STATUS_CONVERTED_TO_ORDER]);

            $allAccepted = $offer['product_count'] > 0;
            foreach ($products as $p) {
                if ($p['product_status'] !== OfferProduct::STATUS_ACCEPTED) {
                    $allAccepted = false;
                    break;
                }
            }

            $formattedOffers[] = [
                'id_offer' => (int) $offer['id_offer'],
                'reference' => $offer['reference'],
                'title' => $offer['title'],
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'product_count' => (int) $offer['product_count'],
                'thumbnails' => $thumbnails,
                'status' => $offer['status'],
                'status_label' => ucfirst(str_replace('_', ' ', $offer['status'])),
                'expire_date' => $offer['expire_date'],
                'date_add' => $offer['date_add'],
                'date_upd' => $offer['date_upd'],
                'detail_url' => $detail_url,
                'is_expired' => $isExpired,
                'all_accepted' => $allAccepted,
            ];
        }

        $totalPages = max(1, ceil($total / $limit));

        $this->context->smarty->assign([
            'offers' => $formattedOffers,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $totalPages,
            'filters' => $filters,
            'statuses' => Offer::getStatuses(),
            'order_by' => $order_by,
            'order_way' => $order_way,
            'ajax_url' => $this->context->link->getAdminLink('AdminOffers') . '&action=ajax',
            'list_url' => $this->context->link->getAdminLink('AdminOffers'),
        ]);

        $this->content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'productpriceconfig/views/templates/admin/offers/list.tpl'
        );

        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    }

    /**
     * Render the offer detail page.
     */
    private function renderDetail($id_offer)
    {
        $service = new OfferService($this->module);
        $detail = $service->getOfferDetail($id_offer);

        if (!$detail) {
            $this->errors[] = $this->trans('Offer not found.', [], 'Modules.Productpriceconfig.Admin');
            return;
        }

        $offer = $detail['offer'];

        // Log admin viewed
        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_ADMIN_VIEWED, [
            'id_employee' => (int) $this->context->employee->id,
            'description' => 'Admin viewed the quotation.',
        ]);

        // Format products
        $id_lang = (int) $this->context->language->id;
        $formattedProducts = [];
        foreach ($detail['products'] as $p) {
            $image = '';
            if (!empty($p['image_id'])) {
                $image = $this->context->link->getImageLink($p['link_rewrite'], $p['image_id'], 'home_default');
            }

            // Render configuration summary as rich HTML (no raw JSON in the UI)
            $configHtml = ConfigurationSummaryRenderer::renderHtml($p['configuration_json'], $id_lang);

            // Format attachments with file size
            $formattedAttachments = [];
            if (!empty($p['attachments'])) {
                foreach ($p['attachments'] as $att) {
                    $formattedAttachments[] = [
                        'id_attachment' => (int) $att['id_offer_attachment'],
                        'original_name' => $att['original_name'] ?: $att['saved_name'],
                        'formatted_size' => self::formatFileSize((int) ($att['size'] ?? 0)),
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
                'offered_price' => $p['offered_price'],
                'estimated_price' => isset($p['estimated_price']) ? $p['estimated_price'] : null,
                'discounted_amount' => $p['discounted_amount'],
                'weight' => $p['weight'],
                'production_time' => $p['production_time'],
                'production_days' => OfferService::parseProductionTimeDays($p['production_time']),
                'product_status' => $p['product_status'],
                'product_type' => isset($p['product_type']) ? $p['product_type'] : 'normal',
                'product_status_label' => ucfirst(str_replace('_', ' ', $p['product_status'])),
                'admin_note' => $p['admin_note'],
                'customer_note' => $p['customer_note'],
                'attachments' => $formattedAttachments,
            ];
        }

        // Format history
        $formattedHistory = [];
        foreach ($detail['history'] as $h) {
            $actor = 'System';
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

        // Get customer info
        $customerInfo = null;
        if ($offer['id_customer']) {
            $customer = new Customer($offer['id_customer']);
            if (Validate::isLoadedObject($customer)) {
                $customerInfo = [
                    'id_customer' => (int) $customer->id,
                    'name' => $customer->firstname . ' ' . $customer->lastname,
                    'email' => $customer->email,
                    'admin_url' => $this->context->link->getAdminLink('AdminCustomers') . '&id_customer=' . (int) $customer->id . '&viewcustomer',
                ];

                $addresses = $customer->getAddresses($this->context->language->id);
                $customerInfo['addresses'] = $addresses;
            }
        }

        // Get carriers and payment modules for order conversion
        $carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
        $paymentModules = [];
        $modules = Module::getPaymentModules();
        foreach ($modules as $mod) {
            $paymentModules[] = ['id' => $mod['name'], 'name' => $mod['display_name'] ?: $mod['name']];
        }
        if (empty($paymentModules)) {
            $paymentModules[] = ['id' => 'cheque', 'name' => 'Cheque'];
        }

        $hasAccepted = !empty(OfferProduct::getAcceptedProducts($id_offer));

        $this->context->smarty->assign([
            'offer' => $offer,
            'products' => $formattedProducts,
            'history' => $formattedHistory,
            'customer_info' => $customerInfo,
            'carriers' => $carriers,
            'payment_modules' => $paymentModules,
            'has_accepted' => $hasAccepted,
            'ajax_url' => $this->context->link->getAdminLink('AdminOffers') . '&action=ajax',
            'list_url' => $this->context->link->getAdminLink('AdminOffers'),
            'pdf_url' => $this->context->link->getAdminLink('AdminOffers') . '&id_offer=' . (int) $id_offer . '&action=pdf',
            'default_validity_days' => (int) Configuration::get('PPC_OFFER_VALIDITY_DAYS', 15),
            'id_employee' => (int) $this->context->employee->id,
        ]);

        $this->content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'productpriceconfig/views/templates/admin/offers/detail.tpl'
        );

        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    }

    /**
     * Handle AJAX requests from the admin interface.
     */
    private function handleAjax()
    {
        $subAction = Tools::getValue('sub_action');

        // PDF and attachment downloads need binary headers, not JSON
        $isBinaryOutput = in_array($subAction, ['print-pdf', 'download-attachment']);

        if (!$isBinaryOutput) {
            header('Content-Type: application/json');
        }

        switch ($subAction) {
            case 'offer-price':
                $this->ajaxOfferPrice();
                break;

            case 'cancel':
                $this->ajaxCancel();
                break;

            case 'extend-expiry':
                $this->ajaxExtendExpiry();
                break;

            case 'convert-to-order':
                $this->ajaxConvertToOrder();
                break;

            case 'delete-offer':
                $this->ajaxDeleteOffer();
                break;

            case 'delete-product':
                $this->ajaxDeleteProduct();
                break;

            case 'expire-outdated':
                $this->ajaxExpireOutdated();
                break;

            case 'upload-attachment':
                $this->ajaxUploadAttachment();
                break;

            case 'remove-attachment':
                $this->ajaxRemoveAttachment();
                break;

            case 'print-pdf':
                $this->ajaxPrintPdf();
                break;

            case 'download-attachment':
                $this->ajaxDownloadAttachment();
                break;

            case 'recalculate-price':
                $this->ajaxRecalculatePrice();
                break;

            case 'apply-recalc':
                $this->ajaxApplyRecalc();
                break;

            case 'duplicate-offer':
                $this->ajaxDuplicateOffer();
                break;

            default:
                die(json_encode(['success' => false, 'error' => 'Unknown sub-action.']));
        }
    }

    private function ajaxOfferPrice()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_offer_product = (int) Tools::getValue('id_offer_product');
        $id_employee = (int) $this->context->employee->id;

        $data = [
            'offered_price' => (float) Tools::getValue('offered_price', 0),
            'estimated_price' => (float) Tools::getValue('estimated_price', 0),
            'discounted_amount' => (float) Tools::getValue('discounted_amount', 0),
            'weight' => (float) Tools::getValue('weight', 0),
            'production_time' => Tools::getValue('production_time', ''),
            'admin_note' => Tools::getValue('admin_note', ''),
            'expire_days' => (int) Tools::getValue('expire_days', (int) Configuration::get('PPC_OFFER_VALIDITY_DAYS', 15)),
        ];

        $result = $this->offerService->adminOfferPrice($id_offer, $id_offer_product, $data, $id_employee);
        die(json_encode($result));
    }

    private function ajaxCancel()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_employee = (int) $this->context->employee->id;
        $result = $this->offerService->adminCancel($id_offer, $id_employee);
        die(json_encode($result));
    }

    private function ajaxExtendExpiry()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $days = (int) Tools::getValue('days', 7);
        $id_employee = (int) $this->context->employee->id;
        $result = $this->offerService->extendExpiry($id_offer, $days, $id_employee);
        die(json_encode($result));
    }

    private function ajaxConvertToOrder()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_employee = (int) $this->context->employee->id;
        $data = [
            'id_address_delivery' => (int) Tools::getValue('id_address_delivery', 0),
            'id_address_invoice' => (int) Tools::getValue('id_address_invoice', 0),
            'id_carrier' => (int) Tools::getValue('id_carrier', 0),
            'payment_module_name' => Tools::getValue('payment_module_name', 'cheque'),
        ];
        $result = $this->offerService->convertToOrder($id_offer, $id_employee, $data);
        die(json_encode($result));
    }

    private function ajaxDeleteOffer()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $products = OfferProduct::getByOffer($id_offer);
        foreach ($products as $p) {
            OfferAttachment::deleteByOfferProduct($p['id_offer_product']);
        }
        OfferProduct::deleteByOffer($id_offer);
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'offer_history WHERE id_offer = ' . $id_offer);
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'offer WHERE id_offer = ' . $id_offer);
        die(json_encode(['success' => true]));
    }

    private function ajaxDeleteProduct()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_offer_product = (int) Tools::getValue('id_offer_product');
        $result = $this->offerService->removeProduct($id_offer, $id_offer_product);
        die(json_encode($result));
    }

    private function ajaxExpireOutdated()
    {
        $count = $this->offerService->expireOutdated();
        die(json_encode(['success' => true, 'expired_count' => $count]));
    }

    private function ajaxUploadAttachment()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_offer_product = (int) Tools::getValue('id_offer_product');
        $id_employee = (int) $this->context->employee->id;

        if (!isset($_FILES['file'])) {
            die(json_encode(['success' => false, 'error' => 'No file uploaded.']));
        }

        $result = $this->offerService->uploadAttachment($id_offer, $id_offer_product, $_FILES['file'], $id_employee);
        die(json_encode($result));
    }

    private function ajaxRemoveAttachment()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_attachment = (int) Tools::getValue('id_attachment');
        $result = $this->offerService->removeAttachment($id_offer, $id_attachment);
        die(json_encode($result));
    }

    private function ajaxPrintPdf()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        if (!$id_offer) {
            die(json_encode(['success' => false, 'error' => 'Offer ID required.']));
        }

        $pdfContent = OfferPdfGenerator::generate($id_offer);
        if (!$pdfContent) {
            die(json_encode(['success' => false, 'error' => 'Failed to generate PDF']));
        }

        $offer = Offer::getOffer($id_offer);
        $filename = 'Offer_' . ($offer['reference'] ?? $id_offer) . '.pdf';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: private, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $pdfContent;
        exit;
    }

    public function displayAjax()
    {
        // Handled in handleAjax()
    }

    /**
     * Download an attachment file — reuses the same OfferAttachment
     * download logic as the front office. Validates file existence and
     * streams the binary with appropriate headers.
     */
    private function ajaxDownloadAttachment()
    {
        $id_attachment = (int) Tools::getValue('id_attachment');
        if (!$id_attachment) {
            die(json_encode(['success' => false, 'error' => 'Attachment ID required.']));
        }

        $attachment = OfferAttachment::getById($id_attachment);
        if (!$attachment) {
            die(json_encode(['success' => false, 'error' => 'Attachment not found.']));
        }

        $uploadDir = _PS_UPLOAD_DIR_ . 'offer_attachments/';
        $filePath = $uploadDir . $attachment['saved_name'];

        if (!file_exists($filePath)) {
            die(json_encode(['success' => false, 'error' => 'File not found on disk.']));
        }

        $originalName = $attachment['original_name'] ?: $attachment['saved_name'];

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $originalName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($filePath);
        exit;
    }

    /**
     * Recalculate the estimated price and weight for an offer product
     * using the SAME ProductPriceConfig calculation as the front office.
     * Returns the live values so the admin can review and apply them.
     */
    private function ajaxRecalculatePrice()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_offer_product = (int) Tools::getValue('id_offer_product');
        $id_product = (int) Tools::getValue('id_product');

        if (!$id_offer_product || !$id_product) {
            die(json_encode(['success' => false, 'error' => 'Missing product or offer product ID.']));
        }

        $op = OfferProduct::getOfferProduct($id_offer_product);
        if (!$op) {
            die(json_encode(['success' => false, 'error' => 'Offer product not found.']));
        }

        $config = json_decode($op['configuration_json'], true) ?: [];
        $quantity = (int) $op['quantity'];

        // Reuse the exact same calculation path as the front office
        $service = new OfferService($this->module);
        $params = $service->buildParamsFromConfig($config, $id_product, $quantity);

        $estimatedPrice = null;
        $estimatedWeight = null;

        if (method_exists($this->module, 'getCalculatedProductPriceWeight')) {
            $calc = $this->module->getCalculatedProductPriceWeight($params);
            if (is_array($calc)) {
                $estimatedPrice = isset($calc['price']) ? (float) $calc['price'] : null;
                $estimatedWeight = isset($calc['weight']) ? (float) $calc['weight'] : null;
            }
        }

        die(json_encode([
            'success' => true,
            'estimated_price' => $estimatedPrice,
            'estimated_weight' => $estimatedWeight,
            'estimated_price_formatted' => $estimatedPrice !== null ? Tools::displayPrice($estimatedPrice) : '-',
            'estimated_weight_formatted' => $estimatedWeight !== null ? $estimatedWeight . ' kg' : '-',
        ]));
    }

    /**
     * Apply the recalculated price and weight as the new estimated
     * values for the offer product. Updates the database record.
     */
    private function ajaxApplyRecalc()
    {
        $id_offer_product = (int) Tools::getValue('id_offer_product');
        $price = Tools::getValue('price');
        $weight = Tools::getValue('weight');

        if (!$id_offer_product) {
            die(json_encode(['success' => false, 'error' => 'Missing offer product ID.']));
        }

        $op = new OfferProduct($id_offer_product);
        if (!Validate::isLoadedObject($op)) {
            die(json_encode(['success' => false, 'error' => 'Offer product not found.']));
        }

        if ($price !== '' && $price !== null) {
            $op->estimated_price = (float) $price;
        }
        if ($weight !== '' && $weight !== null) {
            $op->weight = (float) $weight;
        }
        $op->date_upd = date('Y-m-d H:i:s');
        $op->save();

        Offer::recalculateTotals((int) $op->id_offer);

        die(json_encode([
            'success' => true,
            'estimated_price_formatted' => Tools::displayPrice((float) $price),
            'estimated_weight_formatted' => (float) $weight . ' kg',
        ]));
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

    /**
     * Duplicate an offer (admin action).
     * Resets offer history, generates a new reference, and recalculates expiry.
     */
    private function ajaxDuplicateOffer()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        if (!$id_offer) {
            die(json_encode(['success' => false, 'error' => 'Missing offer ID.']));
        }

        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            die(json_encode(['success' => false, 'error' => 'Offer not found.']));
        }

        $service = new OfferService($this->module);
        $result = $service->duplicateOffer($id_offer, 0, true);

        if (!$result['success']) {
            die(json_encode($result));
        }

        $id_new_offer = (int) $result['id_new_offer'];
        $redirect_url = $this->context->link->getAdminLink('AdminOffers') . '&action=detail&id_offer=' . $id_new_offer;

        die(json_encode([
            'success' => true,
            'id_new_offer' => $id_new_offer,
            'redirect_url' => $redirect_url,
        ]));
    }
}
