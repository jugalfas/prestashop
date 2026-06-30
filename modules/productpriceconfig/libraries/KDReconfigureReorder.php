<?php
/**
 * KDReconfigureReorder - Service class for the Reconfigure & Reorder feature.
 *
 * Loads a single order's customizations (by id_order), validates them against
 * current ProductPriceConfig rules/settings, recalculates pricing, and
 * prepares data for adding to cart.
 *
 * Reuses existing module methods:
 * - ProductPriceConfig::getCalculatedProductPriceWeight() for price/weight
 * - KDBannedCombinationValidator for banned-combination validation (mirrors JS logic server-side)
 * - ProductPriceConfig::processAddToCart() for cart insertion
 * - KDVariable, KDOption, KDProductSetting model classes for data access
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

require_once(dirname(__FILE__) . '/../productpriceconfig.php');
require_once(dirname(__FILE__) . '/KDBannedCombinationValidator.php');

class KDReconfigureReorder
{
    /** @var ProductPriceConfig */
    private $module;

    /** @var Db */
    private $db;

    /** @var int */
    private $id_lang;

    /** @var int */
    private $id_shop;

    public function __construct(ProductPriceConfig $module)
    {
        $this->module = $module;
        $this->db = Db::getInstance();
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
    }

    // ──────────────────────────────────────────────
    //  PUBLIC API
    // ──────────────────────────────────────────────

    /**
     * Check if an order contains any ProductPriceConfig products.
     * Lightweight check used by hooks to decide whether to show the reorder button.
     *
     * @param int $id_order
     * @return bool
     */
    public function orderHasPPCProducts($id_order)
    {
        $sql = '
            SELECT COUNT(*) AS cnt
            FROM ' . _DB_PREFIX_ . 'order_detail od
            INNER JOIN ' . _DB_PREFIX_ . 'product_setting ps
                ON (ps.id_product = od.product_id)
            WHERE od.id_order = ' . (int) $id_order . '
            AND ps.id_product_setting > 0';

        return (int) $this->db->getValue($sql) > 0;
    }

    /**
     * Load a single order with all its PPC product details, validated and priced.
     *
     * This is the main entry point for the reconfigure page.
     * Loads only the order specified by id_order, validates every product block,
     * and returns all data needed for the page.
     *
     * @param int $id_order
     * @return array
     */
    public function loadOrder($id_order)
    {
        $order = new Order((int) $id_order);
        if (!Validate::isLoadedObject($order)) {
            return $this->errorResult('Order not found');
        }

        $details = $this->getOrderDetails($id_order);

        $product_blocks = [];
        foreach ($details as $detail) {
            $product_blocks[] = $this->buildProductBlock($detail);
        }

        return [
            'success' => true,
            'id_order' => (int) $id_order,
            'order_reference' => $order->reference,
            'order_date' => $order->date_add,
            'products' => $product_blocks,
        ];
    }

    /**
     * Validate a new quantity value for a product's quantity variable.
     *
     * Returns whether the quantity is valid along with min/max/step info.
     *
     * @param int $id_product
     * @param int $id_order_detail
     * @param int $quantity
     * @return array
     */
    public function validateQuantity($id_product, $id_order_detail, $quantity)
    {
        $product = new Product((int) $id_product, true, $this->id_lang);
        if (!Validate::isLoadedObject($product)) {
            return $this->errorResult('Product not found');
        }

        $qty_var = $this->findQuantityVariable($id_product);
        if (!$qty_var) {
            return ['success' => true, 'valid' => true, 'min' => 1, 'max' => null, 'step' => 1];
        }

        $min = $qty_var['p_minimum'] !== null ? (float) $qty_var['p_minimum'] : (float) $qty_var['minimum'];
        $max = $qty_var['p_maximum'] !== null ? (float) $qty_var['p_maximum'] : (float) $qty_var['maximum'];
        $step = (float) ($qty_var['multiplier'] ?: 1);

        $valid = true;
        $message = '';

        if ($min > 0 && $quantity < $min) {
            $valid = false;
            $message = 'Quantity ' . $quantity . ' is below minimum (' . $min . ')';
        } elseif ($max > 0 && $quantity > $max) {
            $valid = false;
            $message = 'Quantity ' . $quantity . ' exceeds maximum (' . $max . ')';
        } elseif ($step > 1 && $quantity % $step !== 0) {
            $valid = false;
            $message = 'Quantity must be a multiple of ' . $step;
        }

        // Also check odd quantity surcharge rule
        $price_for_odd = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'price_for_odd_quantities WHERE product_id = ' . (int) $id_product
        );
        $odd_surcharge = false;
        if ($price_for_odd && $quantity % 2 == 1) {
            $odd_surcharge = (int) $price_for_odd[0]['percentage'];
        }

        return [
            'success' => true,
            'valid' => $valid,
            'message' => $message,
            'min' => $min ?: 1,
            'max' => $max ?: null,
            'step' => $step,
            'odd_surcharge' => $odd_surcharge,
        ];
    }

    /**
     * Recalculate price for a product with updated variable values.
     *
     * @param array $params Variable selections (variable_X => value format)
     * @return array
     */
    public function recalculatePrice($params)
    {
        if (empty($params['id_product']) || empty($params['id_product_setting'])) {
            return $this->errorResult('Missing product identification');
        }

        $id_product = (int) $params['id_product'];
        $product = new Product($id_product, true, $this->id_lang);
        if (!Validate::isLoadedObject($product)) {
            return $this->errorResult('Product not found');
        }

        $setting_row = KDProductSetting::getByProductId($id_product);
        $product_setting = new KDProductSetting($setting_row['id_product_setting']);
        $available_product_variables = $this->getProductVariables($id_product);
        $rules_result = $this->validateRules($params, $available_product_variables);

        $price_result = null;
        $price_error = null;
        try {
            $price_result = $this->module->getCalculatedProductPriceWeight($params);
        } catch (Exception $e) {
            $price_error = $e->getMessage();
        }

        $price_data = [];
        if ($price_result) {
            $price_wot = Tools::ps_round($price_result['price'], 2);
            $tax = $price_wot * ($product->tax_rate / 100);
            $tax = Tools::ps_round($tax, 2);
            $total = $price_wot + $tax;
            $qty = $price_result['qty'];
            $price_per_qty = $qty > 0 ? Tools::ps_round($total / $qty, 2) : 0;

            $priceFormatter = new PriceFormatter();
            $price_data = [
                'price_wot' => $priceFormatter->format($price_wot),
                'tax' => $priceFormatter->format($tax),
                'total' => $priceFormatter->format($total),
                'price_per_qty' => $priceFormatter->format($price_per_qty),
                'total_weight' => $price_result['weight'],
                'total_thickness' => $price_result['thickness'],
                'total_package' => $price_result['package'],
                'qty' => $qty,
                'price_raw' => $price_wot,
                'total_raw' => $total,
            ];
        }

        return [
            'success' => true,
            'id_product' => $id_product,
            'rules_valid' => $rules_result['valid'],
            'rules_reason' => $rules_result['reason'],
            'price_data' => $price_data,
            'price_error' => $price_error,
        ];
    }

    /**
     * Add multiple reconfigured products to the cart.
     *
     * Iterates through the list, validates each, adds only valid products.
     *
     * @param array $products Array of [id_product, id_product_setting, id_product_attribute, variables => [variable_X => value]]
     * @return array
     */
    public function addProductsToCart($products)
    {
        $added = [];
        $skipped = [];

        foreach ($products as $product_data) {
            $id_product = (int) $product_data['id_product'];
            $id_product_setting = (int) $product_data['id_product_setting'];
            $id_product_attribute = (int) ($product_data['id_product_attribute'] ?? 0);

            // Collect variable params
            $params = [
                'id_product' => $id_product,
                'id_product_setting' => $id_product_setting,
                'id_product_attribute' => $id_product_attribute,
            ];
            foreach ($product_data as $key => $value) {
                if (strpos($key, 'variable_') === 0) {
                    $params[$key] = $value;
                }
            }

            // Validate before adding
            $available_product_variables = $this->getProductVariables($id_product);
            

            // Check required variables
            $missing = false;
            foreach ($available_product_variables as $data) {
                if (!$data['active']) {
                    continue;
                }
                $var_key = 'variable_' . $data['id_product_variable'];
                if (!isset($params[$var_key])) {
                    $missing = true;
                    break;
                }
                $varObj = new KDVariable($data['id_variable'], $this->id_lang);
                if ($varObj->type == 2) {
                    $options = json_decode($data['options'], true);
                    if (is_array($options) && !in_array($params[$var_key], $options)) {
                        $missing = true;
                        break;
                    }
                }
            }

            if ($missing) {
                $skipped[] = ['id_product' => $id_product, 'reason' => 'Missing or invalid options'];
                continue;
            }

            // Validate rules
            $rules_result = $this->validateRules($params, $available_product_variables);
            if (!$rules_result['valid']) {
                $skipped[] = ['id_product' => $id_product, 'reason' => $rules_result['reason'] ?: 'Banned combination'];
                continue;
            }

            // Delegate to existing processAddToCart
            $result = $this->module->processAddToCart($params);

            if (isset($result['error']) && $result['error']) {
                $skipped[] = ['id_product' => $id_product, 'reason' => $result['error']];
            } else {
                $added[] = [
                    'id_product' => $id_product,
                    'id_customization' => $result['id_customization'] ?? null,
                ];
            }
        }

        return [
            'success' => true,
            'added_count' => count($added),
            'skipped_count' => count($skipped),
            'added' => $added,
            'skipped' => $skipped,
        ];
    }

    // ──────────────────────────────────────────────
    //  PRODUCT BLOCK BUILDER
    // ──────────────────────────────────────────────

    /**
     * Build a complete product block for display on the reconfigure page.
     *
     * @param array $detail Order detail with customization_data
     * @return array
     */
    private function buildProductBlock($detail)
    {
        $id_product = (int) $detail['product_id'];
        $product = new Product($id_product, true, $this->id_lang);
        $setting_row = KDProductSetting::getByProductId($id_product);
        $product_setting = new KDProductSetting($setting_row['id_product_setting']);

        // Validate configuration
        $validation = $this->validateConfiguration($detail, $product, $product_setting);

        // Build params from old customization for price calculation
        $params = $this->buildParamsFromCustomization($detail, $product_setting);
        
        // Recalculate current price
        $price_result = null;
        $price_error = null;
        $current_price_formatted = null;
        $current_total_formatted = null;

        if (!empty($params)) {
            try {
                $price_result = $this->module->getCalculatedProductPriceWeight($params);
                
                $price_wot = Tools::ps_round($price_result['price'], 2);
                $tax = $price_wot * ($product->tax_rate / 100);
                $tax = Tools::ps_round($tax, 2);
                $total = $price_wot + $tax;

                $priceFormatter = new PriceFormatter();
                $current_price_formatted = $priceFormatter->format($price_wot);
                $current_total_formatted = $priceFormatter->format($total);
            } catch (Exception $e) {
                $price_error = $e->getMessage();
            }
        }

        // Get product variables with display information
        $display_variables = $this->getDisplayVariables($detail, $product_setting);

        // Find the quantity variable (type 1) for this product
        $qty_variable = $this->findQuantityVariableInfo($id_product, $detail);

        // Product image
        $image_url = '';
        $images = $product->getImages($this->id_lang);
        if (!empty($images)) {
            $image_url = Context::getContext()->link->getImageLink(
                $product->link_rewrite,
                $images[0]['id_image'],
                'home_default'
            );
        }

        return [
            'id_order_detail' => (int) $detail['id_order_detail'],
            'id_product' => $id_product,
            'id_product_setting' => (int) $setting_row['id_product_setting'],
            'id_product_attribute' => (int) $detail['product_attribute_id'],
            'product_name' => $detail['product_name'],
            'image_url' => $image_url,
            'original_quantity' => (int) $detail['product_quantity'],
            'original_price' => (float) $detail['unit_price_tax_incl'],
            'original_price_formatted' => Tools::displayPrice($detail['unit_price_tax_incl']),
            'current_price_formatted' => $current_price_formatted,
            'current_total_formatted' => $current_total_formatted,
            'price_result' => $price_result,
            'price_error' => $price_error,
            'params' => $params,
            'validation' => $validation,
            'is_valid' => $validation['all_valid'],
            'display_variables' => $display_variables,
            'qty_variable' => $qty_variable,
        ];
    }

    /**
     * Get display-friendly variable information for read-only display.
     *
     * @param array $detail
     * @param KDProductSetting $product_setting
     * @return array
     */
    private function getDisplayVariables($detail, $product_setting)
    {
        $id_product = (int) $detail['product_id'];
        $available_product_variables = $this->getProductVariables($id_product);
        $variable_position = json_decode($product_setting->variable_position, true);

        if ($variable_position) {
            usort($available_product_variables, function ($a, $b) use ($variable_position) {
                $pos_a = array_search($a['id_product_variable'], $variable_position);
                $pos_b = array_search($b['id_product_variable'], $variable_position);
                return $pos_a - $pos_b;
            });
        }

        $display = [];
        $customization_data = $detail['customization_data'];

        foreach ($available_product_variables as $pv) {
            $id_pv = (int) $pv['id_product_variable'];
            $varObj = new KDVariable($pv['id_variable'], $this->id_lang);
            $var_label = $varObj->label ?: $varObj->name;

            // Skip quantity variable - it's handled separately as editable
            if ($varObj->type == 1) {
                continue;
            }

            if (!isset($customization_data[$id_pv])) {
                continue;
            }

            $value = $customization_data[$id_pv];
            $display_value = $value;

            // For select-type variables, show the option label instead of ID
            if ($varObj->type == 2) {
                $id_option = $this->getOptionIdFromLabel($value, $varObj->id);
                $option = new KDOption((int) $id_option, $this->id_lang);
                if (Validate::isLoadedObject($option)) {
                    $display_value = $option->label;
                }
            } elseif ($varObj->type == 3) {
                $display_value = $varObj->fixed_price;
            }

            // Check if this option is still valid
            $is_valid = true;
            $validity_message = '';

            if (!$pv['active']) {
                $is_valid = false;
                $validity_message = 'Variable is currently inactive';
            } elseif ($varObj->type == 2) {
                $id_option = $this->getOptionIdFromLabel($value, $varObj->id);
                $option = new KDOption((int) $id_option, $this->id_lang);
                if (!Validate::isLoadedObject($option) || !$option->active) {
                    $is_valid = false;
                    $validity_message = 'Option is no longer available';
                } else {
                    $allowed = json_decode($pv['options'], true);
                    if (is_array($allowed) && !in_array((int) $id_option, array_map('intval', $allowed))) {
                        $is_valid = false;
                        $validity_message = 'Option is no longer available for this product';
                    }
                }
            }

            $display[] = [
                'id_product_variable' => $id_pv,
                'label' => $var_label,
                'value' => $value,
                'display_value' => $display_value,
                'type' => $varObj->type,
                'is_valid' => $is_valid,
                'validity_message' => $validity_message,
            ];
        }

        return $display;
    }

    /**
     * Find the quantity variable (type 1) for a product and return its info.
     *
     * @param int $id_product
     * @param array $detail
     * @return array|null
     */
    private function findQuantityVariableInfo($id_product, $detail)
    {
        $qty_var = $this->findQuantityVariable($id_product);
        if (!$qty_var) {
            return null;
        }

        $id_pv = (int) $qty_var['id_product_variable'];
        $customization_data = $detail['customization_data'];
        $current_qty = isset($customization_data[$id_pv]) ? (int) $customization_data[$id_pv] : 0;

        $min = $qty_var['p_minimum'] !== null ? (float) $qty_var['p_minimum'] : (float) $qty_var['minimum'];
        $max = $qty_var['p_maximum'] !== null ? (float) $qty_var['p_maximum'] : (float) $qty_var['maximum'];
        $step = (float) ($qty_var['multiplier'] ?: 1);

        return [
            'id_product_variable' => $id_pv,
            'current_quantity' => $current_qty,
            'min' => $min ?: 1,
            'max' => $max ?: null,
            'step' => $step,
            'label' => $qty_var['name'] ?: 'Quantity',
        ];
    }

    /**
     * Find the quantity variable (type 1) for a product.
     *
     * @param int $id_product
     * @return array|null
     */
    private function findQuantityVariable($id_product)
    {
        $variables = $this->getProductVariables($id_product);
        foreach ($variables as $pv) {
            $varObj = new KDVariable($pv['id_variable'], $this->id_lang);
            if ($varObj->type == 1) {
                $pv['minimum'] = $varObj->minimum;
                $pv['maximum'] = $varObj->maximum;
                return $pv;
            }
        }
        return null;
    }

    // ──────────────────────────────────────────────
    //  ORDER DATA LOADING
    // ──────────────────────────────────────────────

    /**
     * Get order details (line items) that have ProductPriceConfig customizations.
     *
     * @param int $id_order
     * @return array
     */
    private function getOrderDetails($id_order)
    {
        $sql = '
            SELECT od.id_order_detail, od.product_id, od.product_attribute_id,
                   od.product_name, od.product_quantity, od.product_price,
                   od.unit_price_tax_excl, od.unit_price_tax_incl,
                   p.id_product AS id_product,
                   cu.id_customization
            FROM ' . _DB_PREFIX_ . 'order_detail od
            INNER JOIN ' . _DB_PREFIX_ . 'product p
                ON (p.id_product = od.product_id)
            LEFT JOIN ' . _DB_PREFIX_ . 'customization cu
                ON (cu.id_cart = (SELECT id_cart FROM ' . _DB_PREFIX_ . 'orders WHERE id_order = ' . (int) $id_order . ')
                    AND cu.id_product = od.product_id
                    AND cu.id_product_attribute = od.product_attribute_id)
            WHERE od.id_order = ' . (int) $id_order . '
            ORDER BY od.id_order_detail';

        $details = $this->db->executeS($sql) ?: [];

        foreach ($details as $key => &$detail) {
            $setting = KDProductSetting::getByProductId((int) $detail['product_id']);
            if (!$setting || !isset($setting['id_product_setting']) || !$setting['id_product_setting']) {
                unset($details[$key]);
                continue;
            }
            $detail['id_product_setting'] = (int) $setting['id_product_setting'];
            $detail['has_ppc'] = true;
            $detail['customization_data'] = $this->loadCustomizationData($detail);
        }

        return array_values($details);
    }

    // ──────────────────────────────────────────────
    //  VALIDATION ENGINE
    // ──────────────────────────────────────────────

    /**
     * Run all validation checks against an order detail's old configuration.
     */
    private function validateConfiguration($detail, $product, $product_setting)
    {
        $checks = [];
        $all_valid = true;
        $id_product = (int) $detail['product_id'];
        $customization_data = $detail['customization_data'];

        // 1. Product exists and is active
        $check = $this->checkProductActive($product);
        $checks[] = $check;
        if (!$check['valid']) { $all_valid = false; }

        // 2. Product has ProductPriceConfig settings
        $check = $this->checkProductHasSettings($id_product, $product_setting);
        $checks[] = $check;
        if (!$check['valid']) { $all_valid = false; }

        // 3-7: Validate each variable
        $available_product_variables = $this->getProductVariables($id_product);
        $variable_checks = $this->validateVariables($customization_data, $available_product_variables, $id_product);
        foreach ($variable_checks as $check) {
            $checks[] = $check;
            if (!$check['valid']) { $all_valid = false; }
        }

        // 8. Rule/banned combination validation
        if ($all_valid) {
            $params = $this->buildParamsFromCustomization($detail, $product_setting);
            if (!empty($params)) {
                $check = $this->checkRules($params, $available_product_variables);
                $checks[] = $check;
                if (!$check['valid']) { $all_valid = false; }
            }
        }

        // 9. Formula evaluation
        if ($all_valid) {
            $check = $this->checkFormulaEvaluation($detail, $product_setting);
            $checks[] = $check;
            if (!$check['valid']) { $all_valid = false; }
        }

        return [
            'checks' => $checks,
            'all_valid' => $all_valid,
            'valid_count' => count(array_filter($checks, function ($c) { return $c['valid']; })),
            'invalid_count' => count(array_filter($checks, function ($c) { return !$c['valid']; })),
        ];
    }

    private function checkProductActive($product)
    {
        $valid = Validate::isLoadedObject($product) && $product->active;
        return [
            'check' => 'product_active',
            'valid' => $valid,
            'message' => $valid ? 'Product is available' : 'Product is no longer available',
            'severity' => $valid ? 'success' : 'error',
        ];
    }

    private function checkProductHasSettings($id_product, $product_setting)
    {
        $valid = Validate::isLoadedObject($product_setting) && $product_setting->id;
        return [
            'check' => 'product_settings',
            'valid' => $valid,
            'message' => $valid ? 'Configuration settings found' : 'No ProductPriceConfig settings',
            'severity' => $valid ? 'success' : 'error',
        ];
    }

    private function validateVariables($customization_data, $available_product_variables, $id_product)
    {
        $checks = [];
       
        foreach ($customization_data as $id_product_variable => $value) {
            $pv_row = null;
            foreach ($available_product_variables as $pv) {
                if ((int) $pv['id_product_variable'] === (int) $id_product_variable) {
                    $pv_row = $pv;
                    break;
                }
            }

            if (!$pv_row) {
                $checks[] = ['check' => 'variable_exists', 'valid' => false,
                    'message' => 'Variable no longer exists', 'severity' => 'error'];
                continue;
            }

            $varObj = new KDVariable($pv_row['id_variable'], $this->id_lang);
            $var_label = $varObj->label ?: $varObj->name;

            if (!$pv_row['active']) {
                $checks[] = ['check' => 'variable_active', 'valid' => false,
                    'message' => $var_label . ' is inactive', 'severity' => 'warning'];
                continue;
            }

            if ($varObj->type == 2) {
                $checks = array_merge($checks, $this->validateSelectVariable($value, $pv_row, $varObj, $var_label));
            } elseif ($varObj->type == 1) {
                $checks[] = $this->validateQuantityVariable($value, $pv_row, $var_label);
            } elseif ($varObj->type == 4) {
                $checks[] = $this->validateCustomNumberVariable($value, $pv_row, $var_label);
            }
        }
        return $checks;
    }

    private function validateSelectVariable($option_name, $pv_row, $varObj, $var_label)
    {
        $checks = [];

        

        $id_option = $this->getOptionIdFromLabel($option_name, $varObj->id);
        $option = new KDOption((int) $id_option, $this->id_lang);

        if (!Validate::isLoadedObject($option)) {
            $checks[] = ['check' => 'option_exists', 'valid' => false,
                'message' => $var_label . ': option no longer exists', 'severity' => 'error'];
            return $checks;
        }

        if (!$option->active) {
            $checks[] = ['check' => 'option_active', 'valid' => false,
                'message' => $var_label . ': "' . $option->label . '" is inactive', 'severity' => 'error'];
            return $checks;
        }

        $allowed = json_decode($pv_row['options'], true);
       
        if (is_array($allowed) && !in_array((int) $id_option, array_map('intval', $allowed))) {
             print_r( $allowed);
             echo $option_name;
        echo $id_option;
            $checks[] = ['check' => 'option_allowed', 'valid' => false,
                'message' => $var_label . ': "' . $option->label . '" no longer available', 'severity' => 'error'];
            return $checks;
        }

        $checks[] = ['check' => 'option_valid', 'valid' => true,
            'message' => $var_label . ': valid', 'severity' => 'success'];
        return $checks;
    }

    private function validateQuantityVariable($value, $pv_row, $var_label)
    {
        $valid = true;
        $message = $var_label . ': valid';
        $severity = 'success';

        $min = $pv_row['p_minimum'] !== null ? $pv_row['p_minimum'] : $pv_row['minimum'];
        $max = $pv_row['p_maximum'] !== null ? $pv_row['p_maximum'] : $pv_row['maximum'];

        if ($min !== null && $min > 0 && $value < $min) {
            $valid = false;
            $message = $var_label . ': below minimum (' . $min . ')';
            $severity = 'warning';
        } elseif ($max !== null && $max > 0 && $value > $max) {
            $valid = false;
            $message = $var_label . ': exceeds maximum (' . $max . ')';
            $severity = 'warning';
        }

        return ['check' => 'quantity_valid', 'valid' => $valid,
            'message' => $message, 'severity' => $severity];
    }

    private function validateCustomNumberVariable($value, $pv_row, $var_label)
    {
        $valid = is_numeric($value);
        return ['check' => 'custom_number_valid', 'valid' => $valid,
            'message' => $valid ? $var_label . ': valid' : $var_label . ': invalid number',
            'severity' => $valid ? 'success' : 'error'];
    }

    private function checkRules($params, $available_product_variables)
    {
        $result = $this->validateRules($params, $available_product_variables);
        $valid = $result['valid'];
        $message = $valid ? 'No rule violations' : ($result['reason'] ?: 'Current rules prohibit this combination');
        return ['check' => 'rules_valid', 'valid' => $valid,
            'message' => $message, 'severity' => $valid ? 'success' : 'error'];
    }

    private function checkFormulaEvaluation($detail, $product_setting)
    {
        $params = $this->buildParamsFromCustomization($detail, $product_setting);
        if (empty($params)) {
            return ['check' => 'formula_evaluation', 'valid' => false,
                'message' => 'Unable to calculate price', 'severity' => 'error'];
        }
        try {
            $params['id_product'] = (int) $detail['product_id'];
            $params['id_product_setting'] = (int) $product_setting->id;
            $result = $this->module->getCalculatedProductPriceWeight($params);
            if ($result['price'] <= 0) {
                return ['check' => 'formula_evaluation', 'valid' => false,
                    'message' => 'Price is zero or negative', 'severity' => 'warning'];
            }
            return ['check' => 'formula_evaluation', 'valid' => true,
                'message' => 'Price calculated successfully', 'severity' => 'success'];
        } catch (Exception $e) {
            return ['check' => 'formula_evaluation', 'valid' => false,
                'message' => 'Formula error', 'severity' => 'error'];
        }
    }

    // ──────────────────────────────────────────────
    //  RULE VALIDATION (uses KDBannedCombinationValidator)
    // ──────────────────────────────────────────────

    /**
     * Validate current params against all banned combination rules for a product.
     *
     * Uses KDBannedCombinationValidator which mirrors the exact JS logic
     * from getBannedCombinationJs(), evaluating conditions AND disallow actions.
     *
     * @param array $params variable_X => value format (from form/AJAX)
     * @param array $available_product_variables
     * @return array ['valid' => bool, 'reason' => string|null]
     */
    private function validateRules($params, $available_product_variables)
    {
        $id_product = (int) $params['id_product'];

        // Convert params from variable_X format to id_variable => value format
        // which is what KDBannedCombinationValidator expects
        $customization_values = $this->paramsToVariableValues($params, $available_product_variables);

        $validator = new KDBannedCombinationValidator();
        $result = $validator->validate($id_product, $customization_values);

        return [
            'valid' => !$result['banned'],
            'reason' => $result['reason'],
        ];
    }

    /**
     * Convert params (variable_X => value) to (id_variable => value) format.
     *
     * KDBannedCombinationValidator works with id_variable keys because
     * that's how rules reference variables (rule['variable'] = id_variable).
     *
     * @param array $params
     * @param array $available_product_variables
     * @return array id_variable => value
     */
    private function paramsToVariableValues($params, $available_product_variables)
    {
        $values = [];
        foreach ($available_product_variables as $pv) {
            $var_key = 'variable_' . $pv['id_product_variable'];
            $id_variable = (int) $pv['id_variable'];

            if (isset($params[$var_key])) {
                $values[$id_variable] = $params[$var_key];
            }
        }
        return $values;
    }

    // ──────────────────────────────────────────────
    //  DATA LOADING HELPERS
    // ──────────────────────────────────────────────

    private function loadCustomizationData($detail)
    {
        $data = [];
        if (empty($detail['id_customization'])) {
            return $data;
        }
        $id_customization = (int) $detail['id_customization'];
        $sql = '
            SELECT cd.index AS id_product_variable, cd.value
            FROM ' . _DB_PREFIX_ . 'customized_data cd
            WHERE cd.id_customization = ' . $id_customization . '
            AND cd.type = ' . Product::CUSTOMIZE_TEXTFIELD;
        $rows = $this->db->executeS($sql);
        if ($rows) {
            foreach ($rows as $row) {
                $data[(int) $row['id_product_variable']] = $row['value'];
            }
        }
        return $data;
    }

    private function buildParamsFromCustomization($detail, $product_setting)
    {
        $params = [];
        $customization_data = $detail['customization_data'];
        if (empty($customization_data)) {
            return $params;
        }
        $id_product = (int) $detail['product_id'];
        $available_product_variables = $this->getProductVariables($id_product);
       
        foreach ($available_product_variables as $pv) {
            $id_pv = (int) $pv['id_product_variable'];
            $active = (int) $pv['p_active'];
            if(!$active) {
                if($pv['options']){
                     $id_option = json_decode($pv['options'], true)[0];
                     $customization_data[$id_pv] =  (int) $id_option;
                }
                
            }
            if (isset($customization_data[$id_pv])) {
                $varObj = new KDVariable($pv['id_variable'], $this->id_lang);
                if ($varObj->type == 2 and $active) {
                    $label = $customization_data[$id_pv];
                    $option_id = $this->getOptionIdFromLabel($label, $varObj->id);
                    $params['variable_' . $id_pv] = (int) $option_id;
                } elseif ($varObj->type == 1) {
                    $params['variable_' . $id_pv] = (int) $customization_data[$id_pv];
                } elseif ($varObj->type == 4) {
                    $params['variable_' . $id_pv] = (float) $customization_data[$id_pv];
                } elseif ($varObj->type == 5) {
                    $params['variable_' . $id_pv] = $customization_data[$id_pv];
                } elseif ($varObj->type == 3) {
                    $params['variable_' . $id_pv] = $varObj->fixed_price;
                } else {
                    $params['variable_' . $id_pv] = $customization_data[$id_pv];
                }
            }
        }

        $params['id_product'] = $id_product;
        $params['id_product_setting'] = (int) $product_setting->id;
        $params['id_product_attribute'] = (int) $detail['product_attribute_id'];
        return $params;
    }

    private function getOptionIdFromLabel($label, $id_variable)
  {
    $sql = '
        SELECT ol.id_option
        FROM '._DB_PREFIX_.'option_lang ol
        INNER JOIN '._DB_PREFIX_.'option o ON o.id_option = ol.id_option
        WHERE o.id_variable = '.(int)$id_variable.'
        AND ol.label = "'.pSQL($label).'"
        AND ol.id_lang = '.(int)$this->id_lang;

    return (int) Db::getInstance()->getValue($sql);
   }
    /**
     * Get product variables with full option data for display.
     *
     * @param int $id_product
     * @param KDProductSetting $product_setting
     * @return array
     */
    private function getAvailableVariablesWithOptions($id_product, $product_setting)
    {
        $available_product_variables = $this->getProductVariables($id_product);
        $variable_position = json_decode($product_setting->variable_position, true);

        if ($variable_position) {
            usort($available_product_variables, function ($a, $b) use ($variable_position) {
                $pos_a = array_search($a['id_product_variable'], $variable_position);
                $pos_b = array_search($b['id_product_variable'], $variable_position);
                return $pos_a - $pos_b;
            });
        }

        foreach ($available_product_variables as &$data) {
            $varObj = new KDVariable($data['id_variable'], $this->id_lang);
            $data['variable_name'] = $varObj->name;
            $data['variable_label'] = $varObj->label;
            if (!$data['formula_name']) {
                $data['formula_name'] = $varObj->name;
            }
            $data['type'] = $varObj->type;
            $data['fixed_price'] = $varObj->fixed_price;

            if ($varObj->type == 7) {
                $obj_option = new KDOption($data['default_option']);
                $data['fixed_price'] = $obj_option->price;
            }

            $tip = new KDToolTip($data['id_variable_tooltip'], $this->id_lang);
            $data['tooltip_text'] = $tip->text;

            // Load options for select-type variables
            $options = json_decode($data['options'], true);
            if (is_array($options) && count($options)) {
                $options_implode = implode(',', $options);
                $option_sql = '
                    SELECT a.id_option
                    FROM ' . _DB_PREFIX_ . 'option a
                    WHERE a.id_option IN (' . $options_implode . ') AND a.active = 1
                    ORDER BY a.position';
                $option_res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($option_sql);

                if (count($option_res)) {
                    $sortingFunction = function ($a, $b) use ($options) {
                        $pos_a = array_search($a['id_option'], $options);
                        $pos_b = array_search($b['id_option'], $options);
                        return $pos_a - $pos_b;
                    };
                    usort($option_res, $sortingFunction);

                    foreach ($option_res as $option_data) {
                        $option = new KDOption($option_data['id_option'], $this->id_lang);
                        $data['options_data'][$option->id]['id'] = $option->id;
                        $data['options_data'][$option->id]['name'] = $option->label;
                        $data['options_data'][$option->id]['price'] = $option->price;
                        $data['options_data'][$option->id]['weight'] = $option->weight;
                        $data['options_data'][$option->id]['thickness'] = $option->thickness;
                    }
                }
            }
        }

        return $available_product_variables;
    }

    /**
     * Get product variables from database (basic query used in multiple places).
     *
     * @param int $id_product
     * @return array
     */
    private function getProductVariables($id_product)
    {
       
        
        return $this->db->executeS('
            SELECT p.*, pv.*, pvl.*, pl.name, p.maximum AS p_maximum, p.minimum AS p_minimum, p.active as p_active
            FROM ' . _DB_PREFIX_ . 'product_variable p
            LEFT JOIN ' . _DB_PREFIX_ . 'product_variable_lang pl
                ON (pl.id_product_variable = p.id_product_variable AND pl.id_lang = ' . $this->id_lang . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'variable pv
                ON (pv.id_variable = p.id_variable)
            LEFT JOIN ' . _DB_PREFIX_ . 'variable_lang pvl
                ON (pvl.id_variable = p.id_variable AND pvl.id_lang = ' . $this->id_lang . ')
            WHERE p.id_product = ' . (int) $id_product . '
            ORDER BY p.id_product_variable
        ') ?: [];
    }

    private function errorResult($message)
    {
        return ['success' => false, 'error' => $message];
    }
}
