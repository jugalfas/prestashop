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
                if (is_array($value)) {
                    $label = isset($value['label']) ? $value['label'] : $code;
                    $options = isset($value['options']) && is_array($value['options']) ? $value['options'] : [];
                    $normalizedVars[$code] = [
                        'code' => $code,
                        'label' => $label,
                        'options' => array_values($options),
                    ];
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
                    $normalizedTooltips[$t['code']] = isset($t['html']) ? $t['html'] : '';
                }
            }
        }

        // Normalize alerts
        $normalizedAlerts = [];
        foreach ($alerts as $a) {
            if (!is_array($a)) {
                continue;
            }
            if (!isset($a['product_ref']) || !isset($a['variable_code'])) {
                continue;
            }
            $normalizedAlerts[] = [
                'product_ref' => (string) $a['product_ref'],
                'variable_code' => (string) $a['variable_code'],
                'option_value' => isset($a['option_value']) ? (string) $a['option_value'] : '',
                'message' => isset($a['message']) ? $a['message'] : '',
            ];
        }

        // Normalize products
        $normalizedProducts = [];
        if ($this->isAssocArray($products)) {
            foreach ($products as $ref => $cfg) {
                $normalizedProducts[$ref] = is_array($cfg) ? $cfg : [];
            }
        } else {
            foreach ($products as $p) {
                if (is_array($p) && isset($p['product_ref'])) {
                    $ref = $p['product_ref'];
                    $cfg = isset($p['config']) && is_array($p['config']) ? $p['config'] : [];
                    $normalizedProducts[$ref] = $cfg;
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
