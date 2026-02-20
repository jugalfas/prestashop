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
            // Retrieve submitted data
            $variables = Tools::getValue('variables'); // Array: [code => [val1, val2]]
            $tooltips = Tools::getValue('tooltips');   // Array: [code1, code2]
            $alerts = Tools::getValue('alerts');       // Array: ["ref|code|val", ...]
            $products = Tools::getValue('products');   // Array: [ref => [option1, option2]]

            // Construct the expected structure
            $submittedData = [
                'variables' => $variables,
                'tooltips' => $tooltips,
                'alerts' => [],
                'products' => $products
            ];

            // Parse alerts
            if (is_array($alerts)) {
                foreach ($alerts as $alertStr) {
                    $parts = explode('|', $alertStr);
                    if (count($parts) === 3) {
                        $submittedData['alerts'][] = [
                            'product_ref' => $parts[0],
                            'variable_code' => $parts[1],
                            'option_value' => $parts[2]
                        ];
                    }
                }
            }

            // For now, output the received structure as JSON (as per "Example submitted POST payload" requirement)
            // In a real implementation, we would pass $submittedData to a dedicated ExportService method
            // that filters the export based on this granular selection.
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="export_selection_debug_' . date('Y-m-d') . '.json"');
            echo json_encode($submittedData, JSON_PRETTY_PRINT);
            die();
        }
    }
}
