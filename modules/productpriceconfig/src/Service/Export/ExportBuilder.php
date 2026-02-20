<?php

namespace ProductPriceConfig\Service\Export;

use Db;
use Context;
use Configuration;
use KDVariable;
use KDOption;
use KDToolTip;
use KDAlertMessage;
use KDProductVariable;
use KDProductSetting;
use KDRuleList;
use Product;
use Language;
use Shop;

class ExportBuilder
{
    private $context;
    private $db;
    private $id_lang;
    private $id_shop;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->db = Db::getInstance();
        $this->id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $this->id_shop = (int) $this->context->shop->id;
    }

    public function buildExport(array $selections, array $productIds = [])
    {
        $export = [
            'meta' => [
                'module' => 'productpriceconfig',
                'module_version' => '1.0.0', 
                'prestashop_version' => _PS_VERSION_,
                'exported_at' => date('c'),
                'environment' => (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) ? 'staging' : 'production'
            ],
            'variables' => [],
            'tooltips' => [],
            'alerts' => [],
            'products' => []
        ];

        if (!empty($selections['variables'])) {
            $export['variables'] = $this->getVariables();
        }

        if (!empty($selections['tooltips'])) {
            $export['tooltips'] = $this->getTooltips();
        }

        if (!empty($selections['alerts'])) {
            $export['alerts'] = $this->getAlerts($productIds);
        }

        if (!empty($selections['products'])) {
            $export['products'] = $this->getProductConfigs($productIds);
        }

        return $export;
    }

    public function getVariables()
    {
        $sql = "SELECT v.*, vl.label 
                FROM " . _DB_PREFIX_ . "variable v
                LEFT JOIN " . _DB_PREFIX_ . "variable_lang vl ON v.id_variable = vl.id_variable AND vl.id_lang = " . $this->id_lang . "
                ORDER BY v.position";
        $variables = $this->db->executeS($sql);

        $result = [];
        foreach ($variables as $var) {
            $variableData = [
                'code' => $var['name'],
                'label' => $var['label'],
                'type' => $var['type'],
                'position' => (int)$var['position'],
                'active' => (bool)$var['active'],
                'required' => (bool)$var['required'],
                'minimum' => (int)$var['minimum'],
                'maximum' => (int)$var['maximum'],
                'fixed_price' => $var['fixed_price'],
                'options' => []
            ];

            $sqlOptions = "SELECT o.*, ol.label 
                           FROM " . _DB_PREFIX_ . "option o
                           LEFT JOIN " . _DB_PREFIX_ . "option_lang ol ON o.id_option = ol.id_option AND ol.id_lang = " . $this->id_lang . "
                           WHERE o.id_variable = " . (int)$var['id_variable'] . "
                           ORDER BY o.position";
            $options = $this->db->executeS($sqlOptions);

            foreach ($options as $opt) {
                $variableData['options'][] = [
                    'value' => $opt['label'],
                    'price' => (float)$opt['price'],
                    'weight' => (float)$opt['weight'],
                    'thickness' => (float)$opt['thickness'],
                    'position' => (int)$opt['position'],
                    'active' => (bool)$opt['active']
                ];
            }

            $result[] = $variableData;
        }

        return $result;
    }

    public function getTooltips()
    {
        $sql = "SELECT t.id_variable_tooltip, t.label, tl.text 
                FROM " . _DB_PREFIX_ . "variable_tooltip t
                LEFT JOIN " . _DB_PREFIX_ . "variable_tooltip_lang tl ON t.id_variable_tooltip = tl.id_variable_tooltip AND tl.id_lang = " . $this->id_lang;
        $tooltips = $this->db->executeS($sql);

        $result = [];
        foreach ($tooltips as $tooltip) {
            $result[] = [
                'code' => $tooltip['label'], 
                'label' => $tooltip['label'],
                'text' => $tooltip['text']
            ];
        }
        return $result;
    }

    public function getAlerts($productIds = [])
    {
        $sql = "SELECT a.*, p.reference as product_reference, v.name as variable_code, ol.label as option_value
                FROM " . _DB_PREFIX_ . "alert_messages a
                LEFT JOIN " . _DB_PREFIX_ . "product p ON a.product_id = p.id_product
                LEFT JOIN " . _DB_PREFIX_ . "variable v ON a.variable_id = v.id_variable
                LEFT JOIN " . _DB_PREFIX_ . "option_lang ol ON a.option_id = ol.id_option AND ol.id_lang = " . $this->id_lang;
        
        if (!empty($productIds)) {
            $sql .= " WHERE a.product_id IN (" . implode(',', array_map('intval', $productIds)) . ")";
        }

        $alerts = $this->db->executeS($sql);

        $result = [];
        foreach ($alerts as $alert) {
            $result[] = [
                'product_reference' => $alert['product_reference'],
                'variable_code' => $alert['variable_code'],
                'option_value' => $alert['option_value'],
                'message_text' => $alert['message']
            ];
        }
        return $result;
    }

    public function getProductConfigs($productIds = [])
    {
        $sql = "SELECT ps.*, p.reference, pl.name as product_name
                FROM " . _DB_PREFIX_ . "product_setting ps
                LEFT JOIN " . _DB_PREFIX_ . "product p ON ps.id_product = p.id_product
                LEFT JOIN " . _DB_PREFIX_ . "product_lang pl ON ps.id_product = pl.id_product AND pl.id_lang = " . $this->id_lang . " AND pl.id_shop = " . $this->id_shop;
        
        if (!empty($productIds)) {
            $sql .= " WHERE ps.id_product IN (" . implode(',', array_map('intval', $productIds)) . ")";
        }

        $sql .= " ORDER BY p.reference ASC";

        $settings = $this->db->executeS($sql);
        $result = [];

        foreach ($settings as $setting) {
            $idProduct = (int)$setting['id_product'];
            
            $result[] = [
                'product_reference' => $setting['reference'],
                'product_name' => $setting['product_name'],
                'formula_price' => $setting['formula_price'],
                'formula_weight' => $setting['formula_weight'],
                'formula_thickness' => $setting['formula_thickness'],
                'formula_shipping' => $setting['formula_shipping'],
                'odd_quantity_percentage' => $this->getOddQtyPercentage($idProduct),
                'assigned_variables' => $this->getProductVariables($idProduct),
                'tiered_pricing_rules' => $setting['tiered'], 
                'banned_combinations' => $this->getProductRules($idProduct)
            ];
        }

        return $result;
    }

    private function getProductVariables($idProduct)
    {
        $sql = "SELECT pv.*, v.name as variable_code, vt.label as tooltip_code
                FROM " . _DB_PREFIX_ . "product_variable pv
                LEFT JOIN " . _DB_PREFIX_ . "variable v ON pv.id_variable = v.id_variable
                LEFT JOIN " . _DB_PREFIX_ . "variable_tooltip vt ON pv.id_variable_tooltip = vt.id_variable_tooltip
                WHERE pv.id_product = " . (int)$idProduct;
        $vars = $this->db->executeS($sql);

        $result = [];
        foreach ($vars as $var) {
            $optionIds = [];
            if (!empty($var['options'])) {
                 if (strpos($var['options'], '[') === 0) {
                     $optionIds = json_decode($var['options'], true);
                 } else {
                     $optionIds = explode(',', $var['options']);
                 }
            }

            $optionValues = [];
            if (!empty($optionIds) && is_array($optionIds)) {
                $optionIds = array_map('intval', $optionIds);
                if (!empty($optionIds)) {
                    $sqlOpts = "SELECT label FROM " . _DB_PREFIX_ . "option_lang 
                                WHERE id_option IN (" . implode(',', $optionIds) . ") AND id_lang = " . $this->id_lang;
                    $opts = $this->db->executeS($sqlOpts);
                    foreach ($opts as $o) {
                        $optionValues[] = $o['label'];
                    }
                }
            }

            $result[] = [
                'variable_code' => $var['variable_code'],
                'tooltip_code' => $var['tooltip_code'],
                'formula_name' => $var['formula_name'],
                'active' => (bool)$var['active'],
                'minimum' => (float)$var['minimum'],
                'maximum' => (float)$var['maximum'],
                'multiplier' => (float)$var['multiplier'],
                'allowed_options' => $optionValues,
                'default_option' => $this->getOptionLabel((int)$var['default_option'])
            ];
        }
        return $result;
    }

    private function getOptionLabel($idOption)
    {
        if (!$idOption) return null;
        $sql = "SELECT label FROM " . _DB_PREFIX_ . "option_lang WHERE id_option = " . (int)$idOption . " AND id_lang = " . $this->id_lang;
        return $this->db->getValue($sql);
    }

    private function getProductRules($idProduct)
    {
        $sql = "SELECT * FROM " . _DB_PREFIX_ . "rule_list WHERE id_product = " . (int)$idProduct;
        $rules = $this->db->executeS($sql);
        
        $result = [];
        foreach ($rules as $ruleRow) {
            $ruleConditions = json_decode($ruleRow['rule'], true);
            $mappedConditions = [];
            if (is_array($ruleConditions)) {
                foreach ($ruleConditions as $cond) {
                    $mappedConditions[] = [
                        'variable' => $this->getVariableName($cond['variable']),
                        'sign' => $cond['sign'], 
                        'option' => $this->getOptionLabelOrValue($cond['variable'], $cond['option']),
                        'and_or_sign' => $cond['and_or_sign']
                    ];
                }
            }

            $disallowData = json_decode($ruleRow['disallow'], true);
            $mappedDisallow = [];
            if (is_array($disallowData)) {
                foreach ($disallowData as $d) {
                    if (isset($d['disallow_variable']) && is_array($d['disallow_variable'])) {
                        foreach ($d['disallow_variable'] as $k => $varId) {
                            $options = [];
                            if (isset($d['disallow_options']) && is_array($d['disallow_options'])) {
                                $options = array_map([$this, 'getOptionLabel'], $d['disallow_options']);
                            }
                            $mappedDisallow[] = [
                                'variable' => $this->getVariableName($varId),
                                'options' => $options
                            ];
                        }
                    } else {
                         // Fallback or simpler format
                         $mappedDisallow[] = [
                            'variable' => $this->getVariableName(is_array($d) ? ($d['id'] ?? 0) : $d), 
                        ];
                    }
                }
            }

            $result[] = [
                'name' => $ruleRow['name'],
                'active' => (bool)$ruleRow['active'],
                'conditions' => $mappedConditions,
                'disallowed' => $mappedDisallow
            ];
        }
        return $result;
    }

    private function getVariableName($idVariable)
    {
        if (!$idVariable) return null;
        $sql = "SELECT name FROM " . _DB_PREFIX_ . "variable WHERE id_variable = " . (int)$idVariable;
        return $this->db->getValue($sql);
    }

    private function getOptionLabelOrValue($idVariable, $value)
    {
        $sql = "SELECT type FROM " . _DB_PREFIX_ . "variable WHERE id_variable = " . (int)$idVariable;
        $type = $this->db->getValue($sql);
        
        if ($type == 2 || $type == 3 || $type == 4) { 
             $label = $this->getOptionLabel((int)$value);
             if ($label) return $label;
        }
        return $value;
    }

    private function getOddQtyPercentage($idProduct)
    {
        $sql = "SELECT percentage FROM " . _DB_PREFIX_ . "price_for_odd_quantities WHERE product_id = " . (int)$idProduct;
        return $this->db->getValue($sql);
    }
}
