<?php
/**
 * ConfigurationSummaryRenderer - Reusable renderer for product configuration.
 *
 * Converts raw configuration_json (variable_<id> => value) into a rich,
 * user-friendly display with proper labels, SVG icons, colour swatches,
 * and formatted values. Used across Quote Cart, Front/Admin Offer Details,
 * and PDF generation.
 *
 * Reuses variable/option metadata from the PPC tables — does NOT duplicate
 * the calculation logic from ProductPriceConfig.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ConfigurationSummaryRenderer
{
    /**
     * Render configuration JSON as an HTML table.
     *
     * @param string $json  configuration_json from offer_product
     * @param int    $id_lang
     * @return string  HTML
     */
    public static function renderHtml($json, $id_lang = null)
    {
        $rows = self::buildRows($json, $id_lang);
        if (empty($rows)) {
            return '';
        }

        $maxVisible = 5;
        $total = count($rows);
        $hasToggle = $total > $maxVisible;

        $html = '<div class="ppc-config-summary" data-total-rows="' . $total . '">';
        $html .= '<table class="table table-sm ppc-config-table mb-0">';
        foreach ($rows as $i => $row) {
            $extraClass = ($hasToggle && $i >= $maxVisible) ? ' ppc-cs-row-extra' : '';
            $html .= '<tr class="ppc-cs-row' . $extraClass . '">';
            $html .= '<td class="ppc-cs-label">' . htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td class="ppc-cs-value">' . $row['icon'] . '<span class="ppc-cs-val-text">' . htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8') . '</span></td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        if ($hasToggle) {
            $html .= '<div class="ppc-cs-fade"></div>';
            $html .= '<a href="#" class="ppc-cs-toggle" data-expanded="0">';
            $html .= '<span class="ppc-cs-divider"></span>';
            $html .= '<span class="ppc-cs-toggle-label ppc-cs-toggle-show">' . self::t('Show More') . ' <i class="material-icons">expand_more</i></span>';
            $html .= '<span class="ppc-cs-toggle-label ppc-cs-toggle-less">' . self::t('Show Less') . ' <i class="material-icons">expand_less</i></span>';
            $html .= '<span class="ppc-cs-divider"></span>';
            $html .= '</a>';
        }
        $html .= '</div>';

        return $html;
    }

    private static function t($s)
    {
        return Translate::getModuleTranslation('productpriceconfig', $s, 'ConfigurationSummaryRenderer');
    }

    /**
     * Render configuration as a plain-text summary (for PDF fallback, emails, etc).
     *
     * @param string $json
     * @param int    $id_lang
     * @return string
     */
    public static function renderText($json, $id_lang = null)
    {
        $rows = self::buildRows($json, $id_lang);
        if (empty($rows)) {
            return '';
        }

        $parts = [];
        foreach ($rows as $row) {
            $parts[] = $row['label'] . ': ' . $row['value'];
        }

        return implode(' | ', $parts);
    }

    /**
     * Render configuration as an array of rows (for templates that want
     * full control over the HTML, e.g. PDF).
     *
     * @param string $json
     * @param int    $id_lang
     * @return array  [{label, value, icon_html, type}, ...]
     */
    public static function renderArray($json, $id_lang = null)
    {
        return self::buildRows($json, $id_lang);
    }

    /**
     * Build the resolved rows from configuration JSON.
     *
     * @param string $json
     * @param int    $id_lang
     * @return array
     */
    private static function buildRows($json, $id_lang = null)
    {
        $config = json_decode($json, true);
        if (!$config || !is_array($config)) {
            return [];
        }

        if (!$id_lang) {
            $id_lang = (int) Context::getContext()->language->id;
        }

        $rows = [];
        foreach ($config as $key => $value) {
            if (strpos($key, 'variable_') !== 0) {
                continue;
            }
            $id_product_variable = (int) substr($key, strlen('variable_'));
            if (!$id_product_variable) {
                continue;
            }

            $meta = self::loadVariableMeta($id_product_variable, $id_lang);
            if (!$meta) {
                continue;
            }

            // Skip display of hidden variable types (7 = shipping cost, 9 = hidden cost)
            // These are used only for price calculation, not for customer display
            if ((int) $meta['var_type'] === 7 || (int) $meta['var_type'] === 9) {
                continue;
            }

            $row = self::formatRow($meta, $value, $id_lang);
            if ($row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Load variable metadata: label, formula_name, type, id_variable.
     *
     * @param int $id_product_variable
     * @param int $id_lang
     * @return array|false
     */
    private static function loadVariableMeta($id_product_variable, $id_lang)
    {
        $row = Db::getInstance()->getRow('
            SELECT pv.id_product_variable, pv.id_variable, pv.formula_name, pv.active,
                   v.type AS var_type,
                   pvl.name AS display_label
            FROM ' . _DB_PREFIX_ . 'product_variable pv
            LEFT JOIN ' . _DB_PREFIX_ . 'variable v ON v.id_variable = pv.id_variable
            LEFT JOIN ' . _DB_PREFIX_ . 'product_variable_lang pvl
                ON pvl.id_product_variable = pv.id_product_variable AND pvl.id_lang = ' . (int) $id_lang . '
            WHERE pv.id_product_variable = ' . (int) $id_product_variable
        );

        if (!$row) {
            return false;
        }

        return [
            'id_product_variable' => (int) $row['id_product_variable'],
            'id_variable' => (int) $row['id_variable'],
            'formula_name' => $row['formula_name'] ?: '',
            'var_type' => $row['var_type'] ?: '',
            'label' => $row['display_label'] ?: ('Variable ' . $id_product_variable),
        ];
    }

    /**
     * Format a single configuration row with icon and value.
     *
     * @param array $meta
     * @param mixed $value
     * @param int   $id_lang
     * @return array|null
     */
    private static function formatRow($meta, $value, $id_lang)
    {
        $formula = strtolower($meta['formula_name']);
        $type = $meta['var_type'];

        // Resolve option label for dropdown-type variables (type 2)
        $optionLabel = '';
        $optionMeta = null;
        if ($type === '2' || $type === 2) {
            $optionMeta = self::loadOptionMeta((int) $value, $id_lang);
            if ($optionMeta) {
                $optionLabel = $optionMeta['label'];
            }
        }

        $icon = '';
        $displayValue = '';

        // Categorize by formula_name (matching product_config.tpl logic)
        if (stripos($formula, 'drukkleur') !== false) {
            $icon = self::printColourIcon($optionLabel ?: (string) $value);
            $displayValue = $optionLabel ?: (string) $value;
        } elseif (stripos($formula, 'orientation') !== false) {
            $icon = self::orientationIcon($optionLabel ?: (string) $value);
            $displayValue = $optionLabel ?: (string) $value;
        } elseif (stripos($formula, 'uitvoering') !== false) {
            $icon = self::printSidesIcon($optionLabel ?: (string) $value);
            $displayValue = $optionLabel ?: (string) $value;
        } elseif (stripos($formula, 'formaat') !== false) {
            $icon = self::formatIcon();
            $displayValue = $optionLabel ?: (string) $value;
        } elseif (stripos($formula, 'papier') !== false
            || stripos($formula, 'omslag') !== false
            || stripos($formula, 'binnenwerk') !== false) {
            $icon = self::paperIcon();
            $displayValue = $optionLabel ?: (string) $value;
        } elseif (stripos($formula, 'oplage') !== false
            || stripos($formula, 'paginas') !== false
            || $type === '1' || $type === 1) {
            $icon = self::numberIcon();
            $displayValue = self::formatNumber($value);
        } elseif (stripos($formula, 'referentie') !== false
            || stripos($formula, 'kenmerk') !== false
            || $type === '5' || $type === 5) {
            $icon = self::textIcon();
            $displayValue = (string) $value;
        } elseif ($type === '6' || $type === 6) {
            $icon = self::thicknessIcon();
            $displayValue = self::formatNumber($value) . ' mm';
        } elseif ($type === '4' || $type === 4) {
            $icon = self::dimensionIcon();
            $displayValue = self::formatNumber($value) . ' mm';
        } else {
            // Generic: show option label if available, otherwise the raw value
            $displayValue = $optionLabel ?: (string) $value;
            if (is_array($value)) {
                $displayValue = implode(', ', $value);
            }
        }

        if ($displayValue === '' || $displayValue === '0') {
            return null;
        }

        return [
            'label' => $meta['label'],
            'value' => $displayValue,
            'icon' => $icon,
            'type' => $type,
        ];
    }

    /**
     * Load option label and metadata.
     *
     * @param int $id_option
     * @param int $id_lang
     * @return array|false
     */
    private static function loadOptionMeta($id_option, $id_lang)
    {
        if (!$id_option) {
            return false;
        }

        $row = Db::getInstance()->getRow('
            SELECT o.id_option, o.price, o.weight, o.thickness,
                   ol.label
            FROM ' . _DB_PREFIX_ . 'option o
            LEFT JOIN ' . _DB_PREFIX_ . 'option_lang ol
                ON ol.id_option = o.id_option AND ol.id_lang = ' . (int) $id_lang . '
            WHERE o.id_option = ' . (int) $id_option
        );

        if (!$row) {
            return false;
        }

        return [
            'id_option' => (int) $row['id_option'],
            'label' => $row['label'] ?: '',
            'price' => (float) $row['price'],
            'weight' => (float) $row['weight'],
            'thickness' => $row['thickness'],
        ];
    }

    // ── Icon renderers (reuse exact SVGs from product_config.tpl) ──

    private static function printColourIcon($label)
    {
        $lower = strtolower($label);
        if (strpos($lower, 'zw/w') !== false || strpos($lower, 'zwart') !== false || strpos($lower, 'black') !== false) {
            return '<svg class="ppc-chip-color-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">'
                . '<circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="1.3"/>'
                . '<path d="M8 1a7 7 0 0 1 0 14z" fill="currentColor"/>'
                . '</svg>';
        }
        return '<svg class="ppc-chip-color-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">'
            . '<circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="1.3"/>'
            . '<path d="M8 1a7 7 0 0 1 6.06 10.5L8 8z" fill="#00AEEF"/>'
            . '<path d="M14.06 11.5A7 7 0 0 1 8 15l0-7z" fill="#EC008C"/>'
            . '<path d="M8 15a7 7 0 0 1-6.06-10.5L8 8z" fill="#FFF200"/>'
            . '<path d="M1.94 4.5A7 7 0 0 1 8 1l0 7z" fill="#1A1A1A"/>'
            . '</svg>';
    }

    private static function orientationIcon($label)
    {
        $lower = strtolower($label);
        if (strpos($lower, 'liggend') !== false || strpos($lower, 'landscape') !== false) {
            return '<svg class="ppc-chip-color-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">'
                . '<rect x="2" y="6" width="20" height="12" rx="1.5" fill="none" stroke="currentColor" stroke-width="2.5"/>'
                . '</svg>';
        }
        return '<svg class="ppc-chip-color-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">'
            . '<rect x="6" y="2" width="12" height="20" rx="1.5" fill="none" stroke="currentColor" stroke-width="2.5"/>'
            . '</svg>';
    }

    private static function printSidesIcon($label)
    {
        $lower = strtolower($label);
        if (strpos($lower, 'dubbelzijdig') !== false || strpos($lower, 'double') !== false) {
            return '<svg class="ppc-chip-color-icon" viewBox="0 0 24 20" xmlns="http://www.w3.org/2000/svg">'
                . '<rect x="1" y="3" width="14" height="16" rx="1" fill="none" stroke="currentColor" stroke-width="1.4" opacity="0.45"/>'
                . '<rect x="9" y="1" width="14" height="16" rx="1" fill="none" stroke="currentColor" stroke-width="2"/>'
                . '<line x1="12" y1="6" x2="19" y2="6" stroke="currentColor" stroke-width="1.3"/>'
                . '<line x1="12" y1="9.5" x2="19" y2="9.5" stroke="currentColor" stroke-width="1.3"/>'
                . '</svg>';
        }
        return '<svg class="ppc-chip-color-icon" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">'
            . '<rect x="3" y="1" width="14" height="18" rx="1" fill="none" stroke="currentColor" stroke-width="2"/>'
            . '<line x1="6" y1="6" x2="14" y2="6" stroke="currentColor" stroke-width="1.3"/>'
            . '<line x1="6" y1="9.5" x2="14" y2="9.5" stroke="currentColor" stroke-width="1.3"/>'
            . '</svg>';
    }

    private static function formatIcon()
    {
        return '<svg class="ppc-chip-color-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">'
            . '<rect x="4" y="2" width="16" height="20" rx="1.5" fill="none" stroke="currentColor" stroke-width="2"/>'
            . '<line x1="8" y1="7" x2="16" y2="7" stroke="currentColor" stroke-width="1.3"/>'
            . '<line x1="8" y1="11" x2="16" y2="11" stroke="currentColor" stroke-width="1.3"/>'
            . '<line x1="8" y1="15" x2="13" y2="15" stroke="currentColor" stroke-width="1.3"/>'
            . '</svg>';
    }

    private static function paperIcon()
    {
        return '<svg class="ppc-chip-color-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">'
            . '<path d="M6 2h9l5 5v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z" fill="none" stroke="currentColor" stroke-width="2"/>'
            . '<path d="M15 2v5h5" fill="none" stroke="currentColor" stroke-width="1.5"/>'
            . '</svg>';
    }

    private static function numberIcon()
    {
        return '<svg class="ppc-chip-color-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">'
            . '<path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>'
            . '</svg>';
    }

    private static function textIcon()
    {
        return '<svg class="ppc-chip-color-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">'
            . '<path d="M5 5h14M5 5v2M12 5v14M9 19h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>'
            . '</svg>';
    }

    private static function thicknessIcon()
    {
        return '<svg class="ppc-chip-color-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">'
            . '<rect x="4" y="8" width="16" height="8" rx="1" fill="none" stroke="currentColor" stroke-width="2"/>'
            . '<path d="M4 8L8 4M20 8l-4-4M4 16l4 4M20 16l-4 4" stroke="currentColor" stroke-width="1.3" fill="none"/>'
            . '</svg>';
    }

    private static function dimensionIcon()
    {
        return '<svg class="ppc-chip-color-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">'
            . '<path d="M3 8V3h5M21 16v5h-5M3 3l6 6M21 21l-6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>'
            . '</svg>';
    }

    /**
     * Format a number with thousands separators.
     *
     * @param mixed $value
     * @return string
     */
    private static function formatNumber($value)
    {
        if (is_numeric($value)) {
            $num = (float) $value;
            if ($num == (int) $num) {
                return number_format((int) $num);
            }
            return number_format($num, 2, '.', '');
        }
        return (string) $value;
    }
}
