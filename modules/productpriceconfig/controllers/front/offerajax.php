<?php
/**
 * OfferAjax - Front controller for all AJAX operations related to offers.
 *
 * Handles: product search, configurator loading, file upload, offer CRUD,
 * accept/reject, duplicate, add-to-cart, and more.
 *
 * URL: /module/productpriceconfig/offerajax
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductPriceConfigOfferAjaxModuleFrontController extends ModuleFrontController
{
    /** @var OfferService */
    private $offerService;

    public function __construct()
    {
        parent::__construct();
        $this->offerService = new OfferService($this->module);
    }

    public function init()
    {
        parent::init();
    }

    public function postProcess()
    {
        $action = Tools::getValue('action');

        switch ($action) {
            case 'search-products':
                $this->actionSearchProducts();
                break;

            case 'get-configurator':
                $this->actionGetConfigurator();
                break;

            case 'create-draft':
                $this->actionCreateDraft();
                break;

            case 'add-product':
                $this->actionAddProduct();
                break;

            case 'remove-product':
                $this->actionRemoveProduct();
                break;

            case 'update-product-config':
                $this->actionUpdateProductConfig();
                break;

            case 'upload-attachment':
                $this->actionUploadAttachment();
                break;

            case 'remove-attachment':
                $this->actionRemoveAttachment();
                break;

            case 'get-offer-detail':
                $this->actionGetOfferDetail();
                break;

            case 'submit-offer':
                $this->actionSubmitOffer();
                break;

            case 'accept-product':
                $this->actionAcceptProduct();
                break;

            case 'reject-product':
                $this->actionRejectProduct();
                break;

            case 'duplicate-offer':
                $this->actionDuplicateOffer();
                break;

            case 'add-to-cart':
                $this->actionAddToCart();
                break;

            case 'get-quote-cart':
                $this->actionGetQuoteCart();
                break;

            case 'validate-configuration':
                $this->actionValidateConfiguration();
                break;

            case 'get-offer-product':
                $this->actionGetOfferProduct();
                break;

            case 'print-pdf':
                $this->actionPrintPdf();
                break;

            case 'download-attachment':
                $this->actionDownloadAttachment();
                break;

            case 'cancel-offer':
                $this->actionCancelOffer();
                break;

            default:
                $this->jsonResponse(['success' => false, 'error' => 'Unknown action.'], 400);
        }
    }

    /**
     * AJAX product search with debouncing support.
     */
    private function actionSearchProducts()
    {
        $query = trim(Tools::getValue('q', ''));
        $limit = min(20, max(1, (int) Tools::getValue('limit', 20)));

        if (strlen($query) < 2) {
            // Return some default products from different categories
            $results = $this->getDefaultProducts($limit);
        } else {
            $results = $this->searchProducts($query, $limit);
        }

        $this->jsonResponse(['success' => true, 'products' => $results]);
    }

    /**
     * Get default products from different categories (shown when search is empty).
     */
    private function getDefaultProducts($limit)
    {
        $id_lang = (int) $this->context->language->id;

        $sql = 'SELECT p.id_product, p.reference, p.active, pl.name, pl.description_short,
                    pl.link_rewrite, cl.name AS category_name,
                    (SELECT id_image FROM ' . _DB_PREFIX_ . 'image i WHERE i.id_product = p.id_product AND i.cover = 1 LIMIT 1) AS id_image
                FROM ' . _DB_PREFIX_ . 'product p
                INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON pl.id_product = p.id_product AND pl.id_lang = ' . $id_lang . '
                INNER JOIN ' . _DB_PREFIX_ . 'product_setting ps ON ps.id_product = p.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'category_product cp ON cp.id_product = p.id_product AND cp.id_category != ' . (int) Configuration::get('PS_HOME_CATEGORY') . '
                LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON cl.id_category = cp.id_category AND cl.id_lang = ' . $id_lang . '
                WHERE p.active = 1
                GROUP BY p.id_product
                ORDER BY p.date_add DESC
                LIMIT ' . (int) $limit;

        $rows = Db::getInstance()->executeS($sql) ?: [];

        return array_map(function ($row) use ($id_lang) {
            return $this->formatProductResult($row, $id_lang);
        }, $rows);
    }

    /**
     * Search products by name, reference, SKU, EAN.
     */
    private function searchProducts($query, $limit)
    {
        $id_lang = (int) $this->context->language->id;
        $search = pSQL($query);

        $sql = 'SELECT DISTINCT p.id_product, p.reference, p.ean13, p.active,
                    pl.name, pl.description_short, pl.link_rewrite,
                    cl.name AS category_name,
                    (SELECT id_image FROM ' . _DB_PREFIX_ . 'image i WHERE i.id_product = p.id_product AND i.cover = 1 LIMIT 1) AS id_image
                FROM ' . _DB_PREFIX_ . 'product p
                INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON pl.id_product = p.id_product AND pl.id_lang = ' . $id_lang . '
                INNER JOIN ' . _DB_PREFIX_ . 'product_setting ps ON ps.id_product = p.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'category_product cp ON cp.id_product = p.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON cl.id_category = cp.id_category AND cl.id_lang = ' . $id_lang . '
                WHERE p.active = 1
                AND (
                    pl.name LIKE "%' . $search . '%"
                    OR p.reference LIKE "%' . $search . '%"
                    OR p.ean13 LIKE "%' . $search . '%"
                    OR pl.description_short LIKE "%' . $search . '%"
                )
                GROUP BY p.id_product
                ORDER BY pl.name ASC
                LIMIT ' . (int) $limit;

        $rows = Db::getInstance()->executeS($sql) ?: [];

        return array_map(function ($row) use ($id_lang) {
            return $this->formatProductResult($row, $id_lang);
        }, $rows);
    }

    /**
     * Format a product row for the search dropdown.
     */
    private function formatProductResult($row, $id_lang)
    {
        $image = '';
        if (!empty($row['id_image'])) {
            $image = $this->context->link->getImageLink($row['link_rewrite'], $row['id_image'], 'small_default');
        }

        return [
            'id_product' => (int) $row['id_product'],
            'name' => $row['name'],
            'reference' => $row['reference'] ?: '',
            'description_short' => strip_tags($row['description_short'] ?? ''),
            'category' => $row['category_name'] ?? '',
            'active' => (int) $row['active'],
            'image' => $image,
        ];
    }

    /**
     * Load the product configurator HTML using the dedicated Offer template.
     *
     * Reuses the existing ProductPriceConfig data-loading logic (variables,
     * options, rules, banned combinations, tooltips, tiered pricing) but
     * renders product_config.tpl instead of the Product Detail hook template.
     */
    private function actionGetConfigurator()
    {
        $id_product = (int) Tools::getValue('id_product');
        if (!$id_product) {
            $this->jsonResponse(['success' => false, 'error' => 'Product ID required.'], 400);
        }

        $product = new Product($id_product, false, $this->context->language->id);
        if (!Validate::isLoadedObject($product) || !$product->active) {
            $this->jsonResponse(['success' => false, 'error' => 'Product not found or inactive.'], 404);
        }

        // Reuse the existing module rendering by calling the hook logic directly
        $html = $this->module->renderOfferConfigurator($id_product);
        //$html = '';
        if (!$html) {
            $html = $this->module->hookdisplayQuoteRequest(['id_product' => $id_product]);
        }

        if (!$html) {
            $this->jsonResponse(['success' => false, 'error' => 'This product does not have configuration options.'], 400);
        }

        $this->jsonResponse(['success' => true, 'html' => $html]);
    }

    /**
     * Create a new draft offer.
     */
    private function actionCreateDraft()
    {
        if (!$this->isCustomerOrGuest()) {
            $this->jsonResponse(['success' => false, 'error' => 'Please log in or provide your email.'], 401);
        }

        $data = [
            'title' => Tools::getValue('title', ''),
            'id_customer' => $this->context->customer->isLogged() ? (int) $this->context->customer->id : 0,
            'guest_email' => $this->context->customer->isLogged() ? '' : Tools::getValue('guest_email', ''),
            'guest_name' => $this->context->customer->isLogged() ? '' : Tools::getValue('guest_name', ''),
            'customer_note' => Tools::getValue('customer_note', ''),
        ];

        $offer = $this->offerService->createDraft($data);
        $this->jsonResponse(['success' => true, 'id_offer' => (int) $offer->id, 'reference' => $offer->reference]);
    }

    /**
     * Add a product to the current offer.
     */
    private function actionAddProduct()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_product = (int) Tools::getValue('id_product');
        $id_product_attribute = (int) Tools::getValue('id_product_attribute', 0);
        $configuration_json = Tools::getValue('configuration_json', '{}');
        $quantity = max(1, (int) Tools::getValue('quantity', 1));
        $customer_note = Tools::getValue('customer_note', '');
        $product_type = in_array(Tools::getValue('product_type', 'normal'), ['normal', 'custom']) ? Tools::getValue('product_type', 'normal') : 'normal';

        if (!$id_offer || !$id_product) {
            $this->jsonResponse(['success' => false, 'error' => 'Offer ID and Product ID are required.'], 400);
        }

        $result = $this->offerService->addProduct($id_offer, $id_product, $id_product_attribute, $configuration_json, $quantity, $customer_note, $product_type);
        $this->jsonResponse($result);
    }

    /**
     * Remove a product from an offer.
     */
    private function actionRemoveProduct()
    {
        // Ownership is validated inside OfferService::removeProduct()
        $id_offer = (int) Tools::getValue('id_offer');
        $id_offer_product = (int) Tools::getValue('id_offer_product');

        $result = $this->offerService->removeProduct($id_offer, $id_offer_product);
        $this->jsonResponse($result);
    }

    /**
     * Update product configuration.
     */
    private function actionUpdateProductConfig()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_offer_product = (int) Tools::getValue('id_offer_product');
        $configuration_json = Tools::getValue('configuration_json', '{}');
        $quantity = max(1, (int) Tools::getValue('quantity', 1));

        $result = $this->offerService->updateProductConfig($id_offer, $id_offer_product, $configuration_json, $quantity);
        $this->jsonResponse($result);
    }

    /**
     * Upload an attachment.
     */
    private function actionUploadAttachment()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_offer_product = (int) Tools::getValue('id_offer_product');
        $uploaded_by = $this->context->customer->isLogged() ? (int) $this->context->customer->id : 0;

        if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No file uploaded or invalid upload.'], 400);
        }

        $result = $this->offerService->uploadAttachment($id_offer, $id_offer_product, $_FILES['file'], $uploaded_by);
        $this->jsonResponse($result);
    }

    /**
     * Remove an attachment.
     */
    private function actionRemoveAttachment()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_attachment = (int) Tools::getValue('id_attachment');

        $result = $this->offerService->removeAttachment($id_offer, $id_attachment);
        $this->jsonResponse($result);
    }

    /**
     * Get offer detail (products, attachments, history) as JSON.
     */
    private function actionGetOfferDetail()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $detail = $this->offerService->getOfferDetail($id_offer);

        if (!$detail) {
            $this->jsonResponse(['success' => false, 'error' => 'Offer not found.'], 404);
        }

        $this->jsonResponse(['success' => true, 'detail' => $detail]);
    }

    /**
     * Submit an offer (customer sends to admin).
     */
    private function actionSubmitOffer()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_customer = $this->context->customer->isLogged() ? (int) $this->context->customer->id : 0;
        $title = Tools::getValue('title', '');
        $customer_note = Tools::getValue('customer_note', '');

        $result = $this->offerService->submitOffer($id_offer, $id_customer, $title, $customer_note);

        if ($result['success'] && !$id_customer) {
            $login_url = $this->context->link->getPageLink('authentication', true);
            $back_url = urlencode($this->context->link->getModuleLink('productpriceconfig', 'offerlist') . '?associate_offer=' . $id_offer);
            $result['redirect_url'] = $login_url . '?back=' . $back_url;
        }

        $this->jsonResponse($result);
    }

    /**
     * Customer accepts a product offer.
     */
    private function actionAcceptProduct()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_offer_product = (int) Tools::getValue('id_offer_product');
        $id_customer = $this->context->customer->isLogged() ? (int) $this->context->customer->id : 0;

        $result = $this->offerService->customerAccept($id_offer, $id_offer_product, $id_customer);
        $this->jsonResponse($result);
    }

    /**
     * Customer rejects a product offer.
     */
    private function actionRejectProduct()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_offer_product = (int) Tools::getValue('id_offer_product');
        $reason = Tools::getValue('reason', '');
        $id_customer = $this->context->customer->isLogged() ? (int) $this->context->customer->id : 0;

        $result = $this->offerService->customerReject($id_offer, $id_offer_product, $id_customer, $reason);
        $this->jsonResponse($result);
    }

    /**
     * Duplicate an offer.
     */
    private function actionDuplicateOffer()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_customer = $this->context->customer->isLogged() ? (int) $this->context->customer->id : 0;

        $result = $this->offerService->duplicateOffer($id_offer, $id_customer);
        $this->jsonResponse($result);
    }

    /**
     * Add accepted products to cart.
     */
    private function actionAddToCart()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_customer = $this->context->customer->isLogged() ? (int) $this->context->customer->id : 0;

        $result = $this->offerService->convertToCart($id_offer, $id_customer);
        $this->jsonResponse($result);
    }

    /**
     * Get the current quote cart contents.
     */
    private function actionGetQuoteCart()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        if (!$id_offer) {
            $this->jsonResponse(['success' => false, 'error' => 'No active offer.']);
        }

        $products = OfferProduct::getByOffer($id_offer);
        $formatted = [];

        foreach ($products as $p) {
            $attachments = OfferAttachment::getByOfferProduct($p['id_offer_product']);
            $image = '';
            if (!empty($p['image_id'])) {
                $image = $this->context->link->getImageLink($p['link_rewrite'], $p['image_id'], 'small_default');
            }

            $attachments = OfferAttachment::getByOfferProduct($p['id_offer_product']);

            $formatted[] = [
                'id_offer_product' => (int) $p['id_offer_product'],
                'id_product' => (int) $p['id_product'],
                'product_name' => $p['product_name'],
                'product_reference' => $p['product_reference'],
                'quantity' => (int) $p['quantity'],
                'image' => $image,
                'offered_price' => $p['offered_price'],
                'estimated_price' => $p['estimated_price'] ?? null,
                'weight' => $p['weight'] ?? null,
                'production_time' => $p['production_time'] ?? '',
                'product_status' => $p['product_status'],
                'product_type' => $p['product_type'] ?? 'normal',
                'attachment_count' => count($attachments),
                'attachments' => $attachments,
                'configuration_summary' => ConfigurationSummaryRenderer::renderHtml($p['configuration_json']),
                'customer_note' => $p['customer_note'] ?? '',
            ];
        }

        $this->jsonResponse(['success' => true, 'products' => $formatted]);
    }

    /**
     * Get a single offer product with its configuration for editing.
     */
    private function actionGetOfferProduct()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        $id_offer_product = (int) Tools::getValue('id_offer_product');
        if (!$id_offer || !$id_offer_product) {
            $this->jsonResponse(['success' => false, 'error' => 'Missing parameters.'], 400);
        }

        $row = OfferProduct::getOfferProduct($id_offer_product);
        if (!$row || (int) $row['id_offer'] !== $id_offer) {
            $this->jsonResponse(['success' => false, 'error' => 'Product not found.'], 404);
        }

        $image = '';
        $id_image = Db::getInstance()->getValue(
            'SELECT id_image FROM ' . _DB_PREFIX_ . 'image WHERE id_product = ' . (int) $row['id_product'] . ' AND cover = 1'
        );
        if ($id_image) {
            $image = $this->context->link->getImageLink($row['link_rewrite'], $id_image, 'small_default');
        }

        $this->jsonResponse([
            'success' => true,
            'product' => [
                'id_offer_product' => (int) $row['id_offer_product'],
                'id_product' => (int) $row['id_product'],
                'product_name' => $row['product_name'],
                'product_reference' => $row['product_reference'],
                'image' => $image,
                'configuration_json' => $row['configuration_json'],
                'customer_note' => $row['customer_note'] ?? '',
                'quantity' => (int) $row['quantity'],
            ],
        ]);
    }

    /**
     * Validate a configuration: required variables, required options, quantity,
     * and banned combination check. Reuses existing PPC validation methods.
     */
    private function actionValidateConfiguration()
    {
        $id_product = (int) Tools::getValue('id_product');
        if (!$id_product) {
            $this->jsonResponse(['success' => false, 'error' => 'Product ID required.'], 400);
        }

        $params = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'variable_') === 0) {
                $params[$key] = $value;
            }
        }

        $errors = [];

        // 1. Required variable/option validation — reuse PPC data loading
        $id_lang = (int) $this->context->language->id;
        $variables = Db::getInstance()->executeS('
            SELECT p.*, pv.*, pvl.*, pl.name, p.maximum AS p_maximum, p.minimum AS p_minimum, p.active
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN ' . _DB_PREFIX_ . 'product_variable_lang pl
                ON (pl.id_product_variable = p.id_product_variable AND pl.id_lang = ' . $id_lang . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'variable pv
                ON (pv.id_variable = p.id_variable)
            LEFT JOIN ' . _DB_PREFIX_ . 'variable_lang pvl
                ON (pvl.id_variable = p.id_variable AND pvl.id_lang = ' . $id_lang . ')
            WHERE p.id_product = ' . (int) $id_product . '
            ORDER BY p.id_product_variable
        ');

        if ($variables) {
            foreach ($variables as $pv) {
                $var_key = 'variable_' . $pv['id_product_variable'];
                $value = isset($params[$var_key]) ? trim($params[$var_key]) : '';

                // Check required variables (active and required)
                if ($pv['active'] && $pv['required'] && $value === '') {
                    $errors[] = sprintf('%s is required.', $pv['name']);
                }

                // Check min/max for numeric quantity-type variables
                if ($value !== '' && $pv['type'] == 1) {
                    $numValue = (float) $value;
                    if ($pv['p_minimum'] !== null && $pv['p_minimum'] > 0 && $numValue < $pv['p_minimum']) {
                        $errors[] = sprintf('%s must be at least %d.', $pv['name'], $pv['p_minimum']);
                    }
                    if ($pv['p_maximum'] !== null && $pv['p_maximum'] > 0 && $numValue > $pv['p_maximum']) {
                        $errors[] = sprintf('%s must be at most %d.', $pv['name'], $pv['p_maximum']);
                    }
                }
            }
        }

        // 2. Product availability
        $product = new Product($id_product, false, $id_lang);
        if (!Validate::isLoadedObject($product) || !$product->active) {
            $errors[] = 'Product is not available.';
        }

        // 3. Banned combination validation
        $bannedResult = $this->module->validateBannedCombinations($id_product, $params);
        if (is_array($bannedResult) && !empty($bannedResult['banned'])) {
            $errors[] = $bannedResult['reason'] ?: 'This combination is not allowed.';
        }

        if (!empty($errors)) {
            $this->jsonResponse([
                'success' => true,
                'validation' => [
                    'valid' => false,
                    'errors' => $errors,
                ],
            ]);
        }

        $this->jsonResponse([
            'success' => true,
            'validation' => [
                'valid' => true,
                'errors' => [],
            ],
        ]);
    }

    // ── Helpers ──

    /**
     * Check if the current user is a logged-in customer or a guest with email.
     */
    private function isCustomerOrGuest()
    {
        if ($this->context->customer->isLogged()) {
            return true;
        }

        $guestEmail = Tools::getValue('guest_email', '');
        return !empty($guestEmail) && Validate::isEmail($guestEmail);
    }

    /**
     * Build a configuration summary from JSON (text version).
     */
    private function buildConfigSummary($json)
    {
        return ConfigurationSummaryRenderer::renderText($json);
    }

    /**
     * Output JSON response and die.
     */
    private function jsonResponse($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        die(json_encode($data));
    }

    /**
     * Generate and stream the offer PDF (AJAX download, no new tab).
     */
    private function actionPrintPdf()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        if (!$id_offer) {
            $this->jsonResponse(['success' => false, 'error' => 'Offer ID required.'], 400);
        }

        $offer = Offer::getOffer($id_offer);
        if (!$offer) {
            $this->jsonResponse(['success' => false, 'error' => 'Offer not found.'], 404);
        }

        try {
            $pdfGenerator = new OfferPdfGenerator($this->module);
            $pdfContent = $pdfGenerator->generate($id_offer);

            if (!$pdfContent) {
                $this->jsonResponse(['success' => false, 'error' => 'Failed to generate PDF.'], 500);
            }

            // Clean any accidental output buffer before sending binary PDF
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $filename = 'Offer_' . $offer['reference'] . '.pdf';
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($pdfContent));
            header('Cache-Control: private, no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo $pdfContent;
            exit;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('OfferPdfGenerator (front) error: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 3);
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download an attachment file (stream, no new tab).
     */
    private function actionDownloadAttachment()
    {
        $id_attachment = (int) Tools::getValue('id_attachment');
        if (!$id_attachment) {
            $this->jsonResponse(['success' => false, 'error' => 'Attachment ID required.'], 400);
        }

        $attachment = OfferAttachment::getById($id_attachment);
        if (!$attachment) {
            $this->jsonResponse(['success' => false, 'error' => 'Attachment not found.'], 404);
        }

        $uploadDir = _PS_UPLOAD_DIR_ . 'offer_attachments/';
        $filePath = $uploadDir . $attachment['saved_name'];

        if (!file_exists($filePath)) {
            $this->jsonResponse(['success' => false, 'error' => 'File not found on disk.'], 404);
        }

        $filename = $attachment['original_name'] ?: $attachment['saved_name'];
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Cancel an offer (AJAX).
     */
    private function actionCancelOffer()
    {
        $id_offer = (int) Tools::getValue('id_offer');
        if (!$id_offer) {
            $this->jsonResponse(['success' => false, 'error' => 'Offer ID required.'], 400);
        }

        $offer = new Offer($id_offer);
        if (!Validate::isLoadedObject($offer)) {
            $this->jsonResponse(['success' => false, 'error' => 'Offer not found.'], 404);
        }

        Offer::updateStatus($id_offer, Offer::STATUS_CANCELLED);
        OfferHistoryLogger::log($id_offer, OfferHistory::ACTION_STATUS_CHANGED, [
            'description' => 'Offer cancelled.',
        ]);

        $this->jsonResponse(['success' => true, 'status' => Offer::STATUS_CANCELLED]);
    }
}
