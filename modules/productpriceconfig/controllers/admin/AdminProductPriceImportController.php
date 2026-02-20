<?php

require_once _PS_MODULE_DIR_ . 'productpriceconfig/src/Service/Import/ImportParser.php';
require_once _PS_MODULE_DIR_ . 'productpriceconfig/src/Service/Import/ImportRunner.php';

use ProductPriceConfig\Service\Import\ImportParser;
use ProductPriceConfig\Service\Import\ImportRunner;

class AdminProductPriceImportController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();

        $this->meta_title = $this->l('Import Configuration');
        $this->toolbar_title = $this->l('Import Configuration');
    }

    public function initContent()
    {
        parent::initContent();
        $this->content .= $this->renderForm();
        $this->context->smarty->assign('content', $this->content);
    }

    public function renderForm()
    {
        // Prefer an import_step value already assigned to Smarty (e.g., by postProcess handlers)
        $existingStep = $this->context->smarty->getTemplateVars('import_step');
        $step = $existingStep ?: Tools::getValue('import_step', 1);

        $this->context->smarty->assign([
            'current' => $this->context->link->getAdminLink('AdminProductPriceImport', false),
            'token' => Tools::getAdminTokenLite('AdminProductPriceImport'),
            'module_dir' => _MODULE_DIR_ . $this->module->name . '/',
            'module' => $this->module,
            'import_step' => $step,
            'import_errors' => $this->errors,
            'import_warnings' => $this->warnings,
        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/import_config.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitUploadImportFile')) {
            $this->processFileUpload();
        } elseif (Tools::isSubmit('submitRunImport')) {
            $this->processImportExecution();
        }
    }

    protected function processFileUpload()
    {
        if (empty($_FILES['import_file']['name'])) {
            $this->errors[] = $this->l('Please select a file to upload.');
            return;
        }

        $file = $_FILES['import_file'];

        // Validate file type
        $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($fileType) !== 'json') {
            $this->errors[] = $this->l('Invalid file type. Only JSON files are allowed.');
            return;
        }

        // Validate JSON structure and parse
        $jsonContent = file_get_contents($file['tmp_name']);
        if ($jsonContent === false) {
            $this->errors[] = $this->l('Could not read the uploaded file.');
            return;
        }

        $parser = new ImportParser();
        try {
            $parsedData = $parser->parse($jsonContent);
            $this->context->smarty->assign('parsed_data', $parsedData);
            $this->context->smarty->assign('import_step', 2); // Move to step 2: Analyze & Preview
            $this->context->smarty->assign('json_content', $jsonContent); // Keep JSON content for later steps
        } catch (Exception $e) {
            $this->errors[] = $this->l('Invalid JSON file or structure: ') . $e->getMessage();
        }
    }

    protected function processImportExecution()
    {
        $jsonContent = Tools::getValue('json_content');
        if (!$jsonContent) {
            $this->errors[] = $this->l('No JSON content found for import.');
            return;
        }

        $parser = new ImportParser();
        try {
            $parsedData = $parser->parse($jsonContent);
        } catch (Exception $e) {
            $this->errors[] = $this->l('Error re-parsing JSON content: ') . $e->getMessage();
            return;
        }

        $importSelections = [
            'overwrite' => (bool) Tools::getValue('overwrite_existing'),
            'dry_run' => (bool) Tools::getValue('dry_run'),
            'variables' => Tools::getValue('variables', []),
            'tooltips' => Tools::getValue('tooltips', []),
            'alerts' => Tools::getValue('alerts', []),
            'products' => Tools::getValue('products', []),
        ];

        $runner = new ImportRunner($this->module);
        try {
            $importResult = $runner->runImport($parsedData, $importSelections);
            $this->context->smarty->assign('import_result', $importResult);
            $this->context->smarty->assign('import_step', 5); // Move to step 5: Show results
        } catch (Exception $e) {
            $this->errors[] = $this->l('Import failed: ') . $e->getMessage();
        }
    }
}
