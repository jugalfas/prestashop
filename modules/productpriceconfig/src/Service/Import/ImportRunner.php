<?php

namespace ProductPriceConfig\Service\Import;

use Db;
use Exception;

class ImportRunner
{
    /** @var \Module */
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Run the import using parsed data and admin selections.
     * Returns an array with imported/skipped/errors and a log id.
     *
     * @param array $parsedData
     * @param array $selections
     * @return array
     * @throws Exception
     */
    public function runImport(array $parsedData, array $selections)
    {
        $db = Db::getInstance();
        $results = [
            'imported' => [],
            'skipped' => [],
            'warnings' => [],
            'errors' => [],
        ];

        // Ensure log table exists
        $this->ensureLogTable();

        // Start transaction
        if (!$db->execute('START TRANSACTION')) {
            throw new Exception('Could not start DB transaction.');
        }

        try {
            // 1) Variables and options
            if (!empty($selections['variables']) && is_array($selections['variables'])) {
                foreach ($selections['variables'] as $varCode => $selectedOptions) {
                    if (!isset($parsedData['variables'][$varCode])) {
                        $results['warnings'][] = "Variable not present in file: $varCode";
                        continue;
                    }

                    $var = $parsedData['variables'][$varCode];
                    $this->importVariable($var, $selectedOptions, $selections, $results);
                }
            }

            // 2) Tooltips
            if (!empty($selections['tooltips']) && is_array($selections['tooltips'])) {
                foreach ($selections['tooltips'] as $tooltipCode) {
                    if (!isset($parsedData['tooltips'][$tooltipCode])) {
                        $results['warnings'][] = "Tooltip not present: $tooltipCode";
                        continue;
                    }
                    $this->importTooltip($tooltipCode, $parsedData['tooltips'][$tooltipCode], $selections, $results);
                }
            }

            // 3) Alerts
            if (!empty($selections['alerts']) && is_array($selections['alerts'])) {
                // selections['alerts'] expected to be array of integer indices or full objects
                foreach ($selections['alerts'] as $idx => $sel) {
                    // allow selected by index if payload provided as array
                    $alert = null;
                    if (is_int($idx) && isset($parsedData['alerts'][$idx])) {
                        $alert = $parsedData['alerts'][$idx];
                    } elseif (is_array($sel) && isset($sel['product_ref'])) {
                        $alert = $sel;
                    }
                    if (!$alert) {
                        continue;
                    }
                    $this->importAlert($alert, $selections, $results);
                }
            }

            // 4) Product configurations
            if (!empty($selections['products']) && is_array($selections['products'])) {
                foreach ($selections['products'] as $productRef => $elements) {
                    if (!isset($parsedData['products'][$productRef])) {
                        $results['warnings'][] = "Product config not present in file: $productRef";
                        continue;
                    }
                    $this->importProductConfig($productRef, $parsedData['products'][$productRef], $elements, $selections, $results);
                }
            }

            if ($selections['dry_run']) {
                // Rollback changes on dry-run
                $db->execute('ROLLBACK');
                $logId = $this->writeLog($parsedData, $selections, $results, 'dry_run');
                $results['log_id'] = $logId;
                return $results;
            }

            // Commit
            if (!$db->execute('COMMIT')) {
                throw new Exception('Could not commit DB transaction.');
            }

            $logId = $this->writeLog($parsedData, $selections, $results, 'success');
            $results['log_id'] = $logId;
            return $results;
        } catch (Exception $e) {
            $db->execute('ROLLBACK');
            $results['errors'][] = $e->getMessage();
            $logId = $this->writeLog($parsedData, $selections, $results, 'failed');
            $results['log_id'] = $logId;
            throw $e;
        }
    }

    private function ensureLogTable()
    {
        $db = Db::getInstance();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.pSQL(_DB_PREFIX_).'productpriceconfig_import_log` (
            `id_import` INT(11) NOT NULL AUTO_INCREMENT,
            `date_add` DATETIME NOT NULL,
            `status` VARCHAR(32) NOT NULL,
            `selections` TEXT NULL,
            `results` TEXT NULL,
            PRIMARY KEY (`id_import`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        return $db->execute($sql);
    }

    private function writeLog(array $parsedData, array $selections, array $results, $status)
    {
        $db = Db::getInstance();
        $now = date('Y-m-d H:i:s');
        $selectionsStr = pSQL(json_encode($selections));
        $resultsStr = pSQL(json_encode($results));
        $sql = 'INSERT INTO `'.pSQL(_DB_PREFIX_).'productpriceconfig_import_log` (`date_add`, `status`, `selections`, `results`) VALUES ('
            ."'".$now."', '".pSQL($status)."', '".$selectionsStr."', '".$resultsStr."')";
        $db->execute($sql);
        return $db->Insert_ID();
    }

    private function importVariable($var, $selectedOptions, $selections, &$results)
    {
        $db = Db::getInstance();
        $code = $var['code'];
        $label = isset($var['label']) ? $var['label'] : $code;

        // detect table when available
        $tblVar = pSQL(_DB_PREFIX_).'productpriceconfig_variable';
        $tblOpt = pSQL(_DB_PREFIX_).'productpriceconfig_variable_option';

        // find existing variable by code
        $idVar = $db->getValue("SELECT id_variable FROM $tblVar WHERE code='".pSQL($code)."'");
        $overwrite = !empty($selections['overwrite']);

        if ($idVar) {
            if ($overwrite) {
                $db->execute("UPDATE $tblVar SET label='".pSQL($label)."' WHERE id_variable='".pSQL($idVar)."'");
                $results['imported'][] = "Variable updated: $code";
            } else {
                $results['skipped'][] = "Variable exists and skipped: $code";
            }
        } else {
            // Insert
            $db->execute("INSERT INTO $tblVar (code, label) VALUES ('".pSQL($code)."', '".pSQL($label)."')");
            $idVar = $db->Insert_ID();
            $results['imported'][] = "Variable created: $code";
        }

        // Options
        if (!is_array($selectedOptions)) {
            $selectedOptions = [];
        }
        foreach ($selectedOptions as $optValue) {
            $optValue = (string) $optValue;
            $idOpt = $db->getValue("SELECT id_option FROM $tblOpt WHERE id_variable='".pSQL($idVar)."' AND value='".pSQL($optValue)."'");
            if ($idOpt) {
                if ($overwrite) {
                    $db->execute("UPDATE $tblOpt SET value='".pSQL($optValue)."' WHERE id_option='".pSQL($idOpt)."'");
                    $results['imported'][] = "Option updated: $code => $optValue";
                } else {
                    $results['skipped'][] = "Option exists and skipped: $code => $optValue";
                }
            } else {
                $db->execute("INSERT INTO $tblOpt (id_variable, value) VALUES ('".pSQL($idVar)."', '".pSQL($optValue)."')");
                $results['imported'][] = "Option created: $code => $optValue";
            }
        }
    }

    private function importTooltip($code, $html, $selections, &$results)
    {
        $db = Db::getInstance();
        $tbl = pSQL(_DB_PREFIX_).'productpriceconfig_tooltip';
        $existing = $db->getValue("SELECT id_tooltip FROM $tbl WHERE code='".pSQL($code)."'");
        $overwrite = !empty($selections['overwrite']);
        if ($existing) {
            if ($overwrite) {
                $db->execute("UPDATE $tbl SET html='".pSQL($html)."' WHERE id_tooltip='".pSQL($existing)."'");
                $results['imported'][] = "Tooltip updated: $code";
            } else {
                $results['skipped'][] = "Tooltip exists and skipped: $code";
            }
        } else {
            $db->execute("INSERT INTO $tbl (code, html) VALUES ('".pSQL($code)."', '".pSQL($html)."')");
            $results['imported'][] = "Tooltip created: $code";
        }
    }

    private function importAlert($alert, $selections, &$results)
    {
        $db = Db::getInstance();
        $productRef = $alert['product_ref'];
        $varCode = $alert['variable_code'];
        $optValue = $alert['option_value'];
        $message = isset($alert['message']) ? $alert['message'] : '';

        $tblAlert = pSQL(_DB_PREFIX_).'productpriceconfig_alert';

        // find product by reference
        $idProduct = $db->getValue("SELECT id_product FROM `"._DB_PREFIX_."product` WHERE reference='".pSQL($productRef)."'");
        if (!$idProduct) {
            $results['warnings'][] = "Product not found for alert, skipped: $productRef";
            return;
        }

        // find variable and option ids if possible
        $idVar = $db->getValue("SELECT id_variable FROM ".pSQL(_DB_PREFIX_)."productpriceconfig_variable WHERE code='".pSQL($varCode)."'");
        if (!$idVar) {
            $results['warnings'][] = "Variable not found for alert, skipped: $varCode";
            return;
        }
        $idOpt = $db->getValue("SELECT id_option FROM ".pSQL(_DB_PREFIX_)."productpriceconfig_variable_option WHERE id_variable='".pSQL($idVar)."' AND value='".pSQL($optValue)."'");
        if (!$idOpt) {
            $results['warnings'][] = "Option not found for alert, skipped: $varCode => $optValue";
            return;
        }

        // Insert alert (do not update existing alerts automatically)
        $db->execute("INSERT INTO $tblAlert (id_product, id_variable, id_option, message) VALUES ('".pSQL($idProduct)."', '".pSQL($idVar)."', '".pSQL($idOpt)."', '".pSQL($message)."')");
        $results['imported'][] = "Alert created for product $productRef";
    }

    private function importProductConfig($productRef, $cfg, $elements, $selections, &$results)
    {
        $db = Db::getInstance();
        $tbl = pSQL(_DB_PREFIX_).'productpriceconfig_product_config';

        // find product id
        $idProduct = $db->getValue("SELECT id_product FROM `"._DB_PREFIX_."product` WHERE reference='".pSQL($productRef)."'");
        if (!$idProduct) {
            $results['warnings'][] = "Product not found, product config skipped: $productRef";
            return;
        }

        // Compose config payload only with requested elements
        $payload = [];
        foreach ($elements as $el) {
            if (isset($cfg[$el])) {
                $payload[$el] = $cfg[$el];
            }
        }

        if (empty($payload)) {
            $results['warnings'][] = "No selected product elements for $productRef";
            return;
        }

        // Find existing config by product
        $existing = $db->getValue("SELECT id_config FROM $tbl WHERE id_product='".pSQL($idProduct)."'");
        $overwrite = !empty($selections['overwrite']);
        if ($existing) {
            if ($overwrite) {
                $db->execute("UPDATE $tbl SET config='".pSQL(json_encode($payload))."' WHERE id_config='".pSQL($existing)."'");
                $results['imported'][] = "Product config updated: $productRef";
            } else {
                $results['skipped'][] = "Product config exists and skipped: $productRef";
            }
        } else {
            $db->execute("INSERT INTO $tbl (id_product, config) VALUES ('".pSQL($idProduct)."', '".pSQL(json_encode($payload))."')");
            $results['imported'][] = "Product config created: $productRef";
        }
    }
}