<?php

namespace ProductPriceConfig\Service\Import;

use Exception;

class ImportParser
{
    /**
     * Parse and validate JSON import content.
     *
     * @param string $jsonContent
     * @return array Normalized parsed data with summary
     * @throws Exception
     */
    public function parse($jsonContent)
    {
        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new Exception('Top-level JSON must be an object.');
        }

        // Normalize expected sections
        $vars = isset($data['variables']) && is_array($data['variables']) ? $data['variables'] : [];
        $tooltips = isset($data['tooltips']) && is_array($data['tooltips']) ? $data['tooltips'] : [];
        $alerts = isset($data['alerts']) && is_array($data['alerts']) ? $data['alerts'] : [];
        $products = isset($data['products']) && is_array($data['products']) ? $data['products'] : [];

        // Normalize variables
        $normalizedVars = [];
        if ($this->isAssocArray($vars)) {
            foreach ($vars as $code => $value) {
                if (!is_array($value)) {
                    continue;
                }
                // Case A: { code: { label: '...', options: [...] } }
                if (isset($value['options'])) {
                    $label = isset($value['label']) ? $value['label'] : $code;
                    $options = is_array($value['options']) ? $value['options'] : [];
                    $normalizedVars[$code] = [
                        'code' => $code,
                        'label' => $label,
                        'options' => array_values($options),
                    ];
                } else {
                    // Case B: { code: [ 'opt1', 'opt2', ... ] }
                    $isList = true;
                    foreach ($value as $k => $v) { if (!is_int($k)) { $isList = false; break; } }
                    if ($isList) {
                        $normalizedVars[$code] = [
                            'code' => $code,
                            'label' => $code,
                            'options' => array_values($value),
                        ];
                    }
                }
            }
        } else {
            foreach ($vars as $v) {
                if (!is_array($v) || !isset($v['code'])) {
                    continue;
                }
                $code = $v['code'];
                $label = isset($v['label']) ? $v['label'] : $code;
                $options = isset($v['options']) && is_array($v['options']) ? $v['options'] : [];
                $normalizedVars[$code] = [
                    'code' => $code,
                    'label' => $label,
                    'options' => array_values($options),
                ];
            }
        }

        // Normalize tooltips
        $normalizedTooltips = [];
        if ($this->isAssocArray($tooltips)) {
            foreach ($tooltips as $code => $html) {
                $normalizedTooltips[$code] = (string) $html;
            }
        } else {
            foreach ($tooltips as $t) {
                if (is_array($t) && isset($t['code'])) {
                    $normalizedTooltips[$t['code']] = isset($t['html']) ? $t['html'] : (isset($t['text']) ? $t['text'] : '');
                } elseif (is_array($t) && isset($t['label'])) {
                    $code = $t['label'];
                    $normalizedTooltips[$code] = isset($t['text']) ? $t['text'] : (isset($t['html']) ? $t['html'] : '');
                } elseif (is_string($t)) {
                    // Case: ["Oplage","Format",...]
                    $normalizedTooltips[$t] = '';
                }
            }
        }

        // Normalize alerts
        $normalizedAlerts = [];
        foreach ($alerts as $a) {
            if (!is_array($a)) {
                continue;
            }
            if (!isset($a['product_ref']) && !isset($a['id_product'])) {
                continue;
            }
            if (!isset($a['variable_code'])) {
                continue;
            }
            $normalizedAlerts[] = [
                'id_product' => isset($a['id_product']) ? (int)$a['id_product'] : null,
                'product_ref' => isset($a['product_ref']) ? (string)$a['product_ref'] : '',
                'variable_code' => (string) $a['variable_code'],
                'option_value' => isset($a['option_value']) ? (string) $a['option_value'] : '',
                'message' => isset($a['message_text']) ? $a['message_text'] : (isset($a['message']) ? $a['message'] : ''),
            ];
        }

        // Normalize products
        $normalizedProducts = [];
        $productLabels = [];
        
        if ($this->isAssocArray($products)) {
            foreach ($products as $ref => $cfg) {
                if (is_array($cfg)) {
                    // Map alternate keys for compatibility
                    if (isset($cfg['banned_combinations']) && !isset($cfg['baned_comb'])) {
                        $cfg['baned_comb'] = $cfg['banned_combinations'];
                    }
                }
                $normalizedProducts[$ref] = is_array($cfg) ? $cfg : [];
            }
        } else {
            foreach ($products as $p) {
                if (is_array($p) && (isset($p['product_ref']) || isset($p['product_reference']))) {
                    $ref = isset($p['product_ref']) ? $p['product_ref'] : $p['product_reference'];
                    // Extract known config fields from flat product object
                    $cfgKeys = [
                        'formula_price',
                        'formula_weight',
                        'formula_thickness',
                        'formula_shipping',
                        'odd_quantity_percentage',
                        'assigned_variables',
                        'tiered_pricing_rules',
                        'banned_combinations',
                        'baned_comb',
                        'tiered'
                    ];
                    $cfg = [];
                    foreach ($cfgKeys as $k) {
                        if (isset($p[$k])) {
                            $cfg[$k] = $p[$k];
                        }
                    }
                    if (empty($cfg) && isset($p['config']) && is_array($p['config'])) {
                        $cfg = $p['config'];
                    }
                    if (isset($cfg['banned_combinations']) && !isset($cfg['baned_comb'])) {
                        $cfg['baned_comb'] = $cfg['banned_combinations'];
                    }
                    $normalizedProducts[$ref] = $cfg;
                    if (isset($p['product_name'])) {
                        $productLabels[$ref] = $p['product_name'];
                    }
                }
            }
        }

        // Build summary
        $countVars = count($normalizedVars);
        $countVarOptions = 0;
        foreach ($normalizedVars as $v) {
            $countVarOptions += count($v['options']);
        }
        $countTooltips = count($normalizedTooltips);
        $countAlerts = count($normalizedAlerts);
        $countProducts = count($normalizedProducts);

        return [
            'variables' => $normalizedVars,
            'tooltips' => $normalizedTooltips,
            'alerts' => $normalizedAlerts,
            'products' => $normalizedProducts,
            'product_labels' => $productLabels,
            'summary' => [
                'variables' => $countVars,
                'variable_options' => $countVarOptions,
                'tooltips' => $countTooltips,
                'alerts' => $countAlerts,
                'products' => $countProducts,
            ],
        ];
    }

    private function isAssocArray($arr)
    {
        if (!is_array($arr)) {
            return false;
        }
        return array_values($arr) !== $arr;
    }
}
