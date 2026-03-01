<?php

require_once _PS_MODULE_DIR_ . 'productpriceconfig/src/Service/Export/ExportBuilder.php';

use ProductPriceConfig\Service\Export\ExportBuilder;

class AdminProductPriceExportController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        
        parent::__construct();
        
        $this->meta_title = $this->l('Export Configuration');
        $this->toolbar_title = $this->l('Export Configuration');
    }

    public function initContent()
    {
        parent::initContent();
        $this->content .= $this->renderForm();
        $this->context->smarty->assign('content', $this->content);
    }

    public function renderForm()
    {
        // 1. Fetch Data using ExportBuilder
        $builder = new ExportBuilder();
        
        // Fetch raw data
        $variables = $builder->getVariables();
        $tooltips = $builder->getTooltips();
        $alertsRaw = $builder->getAlerts();
        $products = $builder->getProductConfigs();

        // 2. Process Alerts: Group by Product Reference
        $alerts = [];
        foreach ($alertsRaw as $alert) {
            $ref = $alert['product_reference'] ?: 'Unknown';
            if (!isset($alerts[$ref])) {
                $alerts[$ref] = [];
            }
            $alerts[$ref][] = $alert;
        }

        // 3. Assign to Smarty
        $this->context->smarty->assign(array(
            'variables' => $variables,
            'tooltips' => $tooltips,
            'alerts' => $alerts,
            'products' => $products,
            'current' => $this->context->link->getAdminLink('AdminProductPriceExport', false),
            'token' => Tools::getAdminTokenLite('AdminProductPriceExport'),
            'module_dir' => _MODULE_DIR_ . $this->module->name . '/'
        ));

        // 4. Render Template
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/export_config.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitExportProductPriceConfig')) {
            // Retrieve submitted selections
            $variablesSel = Tools::getValue('variables'); // [code => [opt1,opt2]]
            $tooltipsSel = Tools::getValue('tooltips');   // [id1, id2]
            $alertsSel   = Tools::getValue('alerts');     // ["id|ref|var|opt|msg", ...] or ["ref|var|opt|msg", ...]
            $productsSel = Tools::getValue('products');   // [id_product => [elements...]]

            // Build export using builder
            $productIds = is_array($productsSel) ? array_map('intval', array_keys($productsSel)) : [];
            $builder = new ExportBuilder();
            $export = $builder->buildExport([
                'variables' => !empty($variablesSel),
                'tooltips'  => !empty($tooltipsSel),
                'alerts'    => !empty($alertsSel),
                'products'  => !empty($productsSel),
            ], $productIds);

            // Filter variables to only selected options
            if (is_array($variablesSel) && !empty($export['variables'])) {
                $filteredVars = [];
                foreach ($export['variables'] as $var) {
                    $code = $var['code'];
                    if (!isset($variablesSel[$code])) {
                        continue;
                    }
                    $allowed = $variablesSel[$code];
                    $var['options'] = array_values(array_filter($var['options'], function ($o) use ($allowed) {
                        return in_array($o['value'], $allowed, true);
                    }));
                    $filteredVars[] = $var;
                }
                $export['variables'] = $filteredVars;
            }

            // Keep only selected tooltips (expand with text)
            if (is_array($tooltipsSel)) {
                $allTooltips = $builder->getTooltips(); // id, label(text code), text
                $byId = [];
                foreach ($allTooltips as $t) { $byId[(string)$t['id']] = $t; }
                $export['tooltips'] = [];
                foreach ($tooltipsSel as $id) {
                    $sid = (string)$id;
                    if (isset($byId[$sid])) {
                        $export['tooltips'][] = [
                            'id'    => (int)$byId[$sid]['id'],
                            'label' => $byId[$sid]['label'],
                            'text'  => $byId[$sid]['text'],
                        ];
                    }
                }
            }

            // Build selected alerts from submitted list
            if (is_array($alertsSel)) {
                $export['alerts'] = [];
                foreach ($alertsSel as $alertStr) {
                    $parts = explode('|', $alertStr);
                    if (count($parts) >= 5) {
                        $export['alerts'][] = [
                            'id_product'        => (int)$parts[0],
                            'product_ref'       => $parts[1],
                            'variable_code'     => $parts[2],
                            'option_value'      => $parts[3],
                            'message_text'      => urldecode($parts[4]),
                        ];
                    } elseif (count($parts) >= 3) {
                        $export['alerts'][] = [
                            'product_ref'   => $parts[0],
                            'variable_code' => $parts[1],
                            'option_value'  => $parts[2],
                            'message_text'  => isset($parts[3]) ? urldecode($parts[3]) : null,
                        ];
                    }
                }
            }

            // Output final export JSON
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="productprice_export_' . date('Y-m-d') . '.json"');
            echo json_encode($export, JSON_PRETTY_PRINT);
            die();
        }
    }
}
