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
     * Load the product configurator HTML via the existing module hook.
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
        $html = $this->module->hookDisplayReassurance(['id_product' => $id_product]);

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

        if (!$id_offer || !$id_product) {
            $this->jsonResponse(['success' => false, 'error' => 'Offer ID and Product ID are required.'], 400);
        }

        $result = $this->offerService->addProduct($id_offer, $id_product, $id_product_attribute, $configuration_json, $quantity, $customer_note);
        $this->jsonResponse($result + ['success' => $result['success']]);
    }

    /**
     * Remove a product from an offer.
     */
    private function actionRemoveProduct()
    {
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

        if (!isset($_FILES['file'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No file uploaded.'], 400);
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

        $result = $this->offerService->submitOffer($id_offer, $id_customer);
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

            $formatted[] = [
                'id_offer_product' => (int) $p['id_offer_product'],
                'id_product' => (int) $p['id_product'],
                'product_name' => $p['product_name'],
                'product_reference' => $p['product_reference'],
                'quantity' => (int) $p['quantity'],
                'image' => $image,
                'offered_price' => $p['offered_price'],
                'product_status' => $p['product_status'],
                'attachment_count' => count($attachments),
                'configuration_summary' => $this->buildConfigSummary($p['configuration_json']),
            ];
        }

        $this->jsonResponse(['success' => true, 'products' => $formatted]);
    }

    /**
     * Validate a configuration via banned combination checker.
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

        $result = $this->module->validateBannedCombinations($id_product, $params);
        $this->jsonResponse(['success' => true, 'validation' => $result]);
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
     * Build a configuration summary from JSON.
     */
    private function buildConfigSummary($json)
    {
        $config = json_decode($json, true);
        if (!$config || !is_array($config)) {
            return '';
        }

        $parts = [];
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $parts[] = $key . ': ' . $value;
        }

        return implode(' | ', $parts);
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
}
