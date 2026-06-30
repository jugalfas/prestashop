<?php
/**
 * ReconfigureAjax - Front controller for Reconfigure & Reorder AJAX endpoints.
 *
 * Endpoints:
 * - action=validate-qty   → Validate quantity (min/max/step check)
 * - action=recalculate    → Recalculate price + rules check for one product
 * - action=add-to-cart    → Add multiple valid products to cart
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/../../libraries/KDReconfigureReorder.php');

class ProductPriceConfigReconfigureAjaxModuleFrontController extends ModuleFrontController
{
    /** @var ProductPriceConfig */
    public $module;

    /** @var KDReconfigureReorder */
    private $service;

    public function init()
    {
        parent::init();

        if (!$this->context->customer->isLogged()) {
            $this->jsonResponse(['success' => false, 'error' => 'Not authenticated'], 401);
        }

        $this->service = new KDReconfigureReorder($this->module);
    }

    public function postProcess()
    {
        $action = Tools::getValue('action');

        if (!$action) {
            $this->jsonResponse(['success' => false, 'error' => 'Missing action parameter']);
        }

        switch ($action) {
            case 'validate-qty':
                $this->actionValidateQuantity();
                break;

            case 'recalculate':
                $this->actionRecalculate();
                break;

            case 'add-to-cart':
                $this->actionAddToCart();
                break;

            default:
                $this->jsonResponse(['success' => false, 'error' => 'Unknown action']);
        }
    }

    /**
     * Validate a quantity value against min/max/step rules.
     */
    private function actionValidateQuantity()
    {
        $id_product = (int) Tools::getValue('id_product');
        $id_order_detail = (int) Tools::getValue('id_order_detail');
        $quantity = (int) Tools::getValue('quantity');

        if (!$id_product || !$quantity) {
            $this->jsonResponse(['success' => false, 'error' => 'Missing parameters']);
        }

        $result = $this->service->validateQuantity($id_product, $id_order_detail, $quantity);
        $this->jsonResponse($result);
    }

    /**
     * Recalculate price for a single product block.
     */
    private function actionRecalculate()
    {
        $params = $this->collectVariableParams();
        $result = $this->service->recalculatePrice($params);
        $this->jsonResponse($result);
    }

    /**
     * Add multiple valid products to the cart.
     *
     * Receives a JSON array of product data in the 'products' POST field.
     */
    private function actionAddToCart()
    {
        $products_json = Tools::getValue('products');
        $products = json_decode($products_json, true);

        if (!is_array($products) || empty($products)) {
            $this->jsonResponse(['success' => false, 'error' => 'No products provided']);
        }

        $result = $this->service->addProductsToCart($products);
        $this->jsonResponse($result);
    }

    // ──────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────

    private function collectVariableParams()
    {
        $params = [];
        $params['id_product'] = (int) Tools::getValue('id_product');
        $params['id_product_setting'] = (int) Tools::getValue('id_product_setting');

        foreach ($_POST as $key => $value) {
            if (strpos($key, 'variable_') === 0) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    private function jsonResponse($data, $status_code = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status_code);
        die(json_encode($data));
    }
}
